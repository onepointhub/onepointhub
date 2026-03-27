<?php

namespace Database\Seeders;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class DevSeeder extends Seeder
{
    /**
     * Seed a realistic development workspace with three users.
     * Safe to run multiple times - skips if the demo workspace already exists.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        if (Workspace::where('slug', 'demo-workspace')->exists()) {
            $this->command->info('Demo workspace already exists. Skipping DevSeeder.');

            return;
        }

        $workspace = Workspace::create([
            'name' => 'Demo Workspace',
            'slug' => 'demo-workspace',
            'currency' => 'USD',
        ]);

        $users = [
            [
                'name' => 'Alice Owner',
                'email' => 'owner@demo.test',
                'role' => WorkspaceRole::Owner->value,
            ],
            [
                'name' => 'Bob Admin',
                'email' => 'admin@demo.test',
                'role' => WorkspaceRole::Admin->value,
            ],
            [
                'name' => 'Carol Member',
                'email' => 'member@demo.test',
                'role' => WorkspaceRole::Member->value,
            ],
        ];

        foreach ($users as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => 'password',
                'email_verified_at' => now(),
            ]);

            $user->workspaces()->attach($workspace->id, ['role' => $data['role']]);

            setPermissionsTeamId($workspace->id);
            $user->assignRole($data['role']);
        }

        $this->command->info('Demo workspace seeded. Login with owner@demo.test / password');
    }
}
