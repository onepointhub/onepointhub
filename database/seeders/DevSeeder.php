<?php

namespace Database\Seeders;

use App\Modules\Clients\Models\Client;
use App\Modules\Core\Enums\WorkspaceRole;
use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;
use App\Modules\Projects\Enums\ProjectMemberRole;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectMember;
use App\Modules\Projects\Models\Task;
use App\Modules\Projects\Models\TimeEntry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DevSeeder extends Seeder
{
    /**
     * Seed two realistic development workspaces with users, clients, projects, tasks, and time entries.
     * Safe to run multiple times - skips any workspace that already exists.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        $this->seedWorkspace(
            slug: 'agency-demo',
            name: 'Pixel & Code Agency',
            currency: 'USD',
            users: [
                ['name' => 'Alice Mercer', 'email' => 'alice@agency.test', 'role' => WorkspaceRole::Owner],
                ['name' => 'Bob Harrington', 'email' => 'bob@agency.test', 'role' => WorkspaceRole::Admin],
                ['name' => 'Carol Nguyen', 'email' => 'carol@agency.test', 'role' => WorkspaceRole::Member],
                ['name' => 'Dan Okafor', 'email' => 'dan@agency.test', 'role' => WorkspaceRole::Member],
            ],
            clients: [
                'Bright Horizon Retail',
                'Nexus Financial Group',
                'Verdant Health Labs',
                'Summit Logistics Co',
                'Coastal Media Group',
            ],
            projectNames: [
                'Brand Refresh & Style Guide',
                'E-commerce Platform Overhaul',
                'Mobile App MVP',
                'Analytics Dashboard',
                'SEO & Content Strategy',
                'Customer Portal',
                'Email Automation Setup',
                'API Integration Suite',
                'Compliance Audit Report',
                'Year-End Campaign',
            ],
        );

        $this->seedWorkspace(
            slug: 'consultancy-demo',
            name: 'Meridian Consulting',
            currency: 'EUR',
            users: [
                ['name' => 'Eve Larsson', 'email' => 'eve@meridian.test', 'role' => WorkspaceRole::Owner],
                ['name' => 'Frank Dumas', 'email' => 'frank@meridian.test', 'role' => WorkspaceRole::Admin],
                ['name' => 'Grace Petrov', 'email' => 'grace@meridian.test', 'role' => WorkspaceRole::Member],
            ],
            clients: [
                'Ironbridge Manufacturing',
                'Aurora Pharmaceuticals',
                'Titan Realty Group',
                'Bluewave Energy',
                'Cornerstone Ventures',
            ],
            projectNames: [
                'Digital Transformation Roadmap',
                'ERP Implementation Phase 1',
                'Change Management Programme',
                'IT Infrastructure Audit',
                'Process Optimisation Study',
                'Market Entry Analysis',
                'Supply Chain Review',
                'Financial Systems Migration',
                'Staff Training Initiative',
                'Quarterly Business Review',
            ],
        );

        $this->command->info('Dev workspaces seeded.');
        $this->command->info('  agency-demo  → alice@agency.test / password');
        $this->command->info('  consultancy  → eve@meridian.test / password');
    }

    /**
     * @param  array<int, array{name: string, email: string, role: WorkspaceRole}>  $users
     * @param  list<string>  $clients
     * @param  list<string>  $projectNames
     */
    private function seedWorkspace(
        string $slug,
        string $name,
        string $currency,
        array $users,
        array $clients,
        array $projectNames,
    ): void {
        if (Workspace::where('slug', $slug)->exists()) {
            $this->command->info("Workspace [$slug] already exists. Skipping.");

            return;
        }

        $workspace = Workspace::create([
            'name' => $name,
            'slug' => $slug,
            'currency' => $currency,
        ]);

        // Bind workspace context so BelongsToWorkspace auto-stamps workspace_id
        app()->instance(Workspace::class, $workspace);

        // Create workspace members
        $members = collect($users)->map(function (array $data) use ($workspace): User {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ],
            );

            $workspace->members()->attach($user->id, ['role' => $data['role']->value]);

            setPermissionsTeamId($workspace->id);
            $user->assignRole($data['role']->value);

            return $user;
        });

        // Create clients with a primary contact each
        $seededClients = collect($clients)->map(function (string $clientName) {
            $client = Client::create([
                'name' => $clientName,
                'currency' => 'USD',
            ]);

            $client->contacts()->create([
                'name' => fake()->name(),
                'email' => fake()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'role' => fake()->jobTitle(),
                'is_primary' => true,
            ]);

            return $client;
        });

        // Create projects, tasks, and time entries
        collect($projectNames)->each(function (string $projectName, int $index) use ($workspace, $members, $seededClients): void {
            $this->seedProject($workspace, $projectName, $members, $seededClients, $index);
        });
    }

    /**
     * @param  Collection<int, User>  $members
     * @param  Collection<int, Client>  $clients
     */
    private function seedProject(
        Workspace $workspace,
        string $projectName,
        Collection $members,
        Collection $clients,
        int $index,
    ): void {
        $project = Project::factory()->create([
            'name' => $projectName,
            'client_id' => $clients->get($index % $clients->count())->id,
            'workspace_id' => $workspace->id,
        ]);

        // Add all workspace members as project members
        $members->each(function (User $user, int $i) use ($project): void {
            ProjectMember::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'role' => $i === 0 ? ProjectMemberRole::Lead : ProjectMemberRole::Member,
                'hourly_rate' => fake()->randomFloat(2, 50, 200),
            ]);
        });

        // Create 2-3 tasks per project with time entries
        $taskCount = fake()->numberBetween(2, 3);

        for ($t = 0; $t < $taskCount; $t++) {
            $assignee = $members->random();
            $creator = $members->first();

            $task = Task::factory()->create([
                'project_id' => $project->id,
                'workspace_id' => $workspace->id,
                'assigned_to' => $assignee->id,
                'created_by' => $creator->id,
            ]);

            // 2-3 time entries per task
            $entryCount = fake()->numberBetween(2, 3);

            for ($e = 0; $e < $entryCount; $e++) {
                TimeEntry::factory()->create([
                    'project_id' => $project->id,
                    'task_id' => $task->id,
                    'user_id' => $members->random()->id,
                    'workspace_id' => $workspace->id,
                ]);
            }
        }

        // Add a few project-level time entries (not tied to a task)
        $projectEntryCount = fake()->numberBetween(2, 3);

        for ($e = 0; $e < $projectEntryCount; $e++) {
            TimeEntry::factory()->create([
                'project_id' => $project->id,
                'task_id' => null,
                'user_id' => $members->random()->id,
                'workspace_id' => $workspace->id,
            ]);
        }
    }
}
