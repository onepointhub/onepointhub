<?php

use App\Modules\Clients\Models\Client;
use App\Modules\Core\Models\User;
use App\Modules\Core\Models\WorkspaceInvitation;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectMember;
use App\Modules\Projects\Models\Task;
use App\Modules\Projects\Models\TimeEntry;
use Inertia\Testing\AssertableInertia;

it('renders the dashboard with workspace stats for an authenticated user', function () {
    [$user, $workspace] = workspaceWithUser('owner');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Core::Dashboard')
            ->where('memberCount', 1)
            ->where('pendingInvitationsCount', 0)
            ->has('recentMembers', 1)
            ->has('recentActivity')
            ->has('recentMembers.0', fn (AssertableInertia $m) => $m
                ->where('id', $user->id)
                ->where('name', $user->name)
                ->has('avatar')
                ->has('role')
            )
        );
});

it('counts only active (non-expired, unaccepted) pending invitations', function () {
    [$user, $workspace] = workspaceWithUser('owner');

    // Pending invitation (should count)
    WorkspaceInvitation::factory()->create([
        'workspace_id' => $workspace->id,
        'accepted_at' => null,
        'expires_at' => now()->addHours(24),
    ]);

    // Accepted invitation (should NOT count)
    WorkspaceInvitation::factory()->create([
        'workspace_id' => $workspace->id,
        'accepted_at' => now(),
        'expires_at' => now()->addHours(24),
    ]);

    // Expired invitation (should NOT count)
    WorkspaceInvitation::factory()->create([
        'workspace_id' => $workspace->id,
        'accepted_at' => null,
        'expires_at' => now()->subHours(1),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('pendingInvitationsCount', 1)
        );
});

it('redirects unauthenticated users to login', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

it('includes new widget props in the dashboard response', function () {
    [$user, $workspace] = workspaceWithUser('owner');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('activeProjectsCount')
            ->has('openTasksCount')
            ->has('clientsCount')
            ->has('hoursThisWeek')
            ->has('activeProjects')
            ->has('openTasks')
            ->has('overdueTasks')
            ->has('recentTimeEntries')
        );
});

it('owner sees all tasks workspace-wide', function () {
    [$user, $workspace] = workspaceWithUser('owner');
    $otherUser = User::factory()->create();
    $workspace->members()->attach($otherUser->id, ['role' => 'member']);

    $project = Project::factory()->active()->create();
    Task::factory()->todo()->create(['project_id' => $project->id, 'assigned_to' => $otherUser->id]);
    Task::factory()->todo()->create(['project_id' => $project->id, 'assigned_to' => $user->id]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('openTasksCount', 2)
        );
});

it('member only sees tasks assigned to them', function () {
    [$user, $workspace] = workspaceWithUser('member');
    $otherUser = User::factory()->create();
    $workspace->members()->attach($otherUser->id, ['role' => 'member']);

    $project = Project::factory()->active()->create();
    Task::factory()->todo()->create(['project_id' => $project->id, 'assigned_to' => $otherUser->id]);
    Task::factory()->todo()->create(['project_id' => $project->id, 'assigned_to' => $user->id]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('openTasksCount', 1)
        );
});

it('owner sees all active projects workspace-wide', function () {
    [$user, $workspace] = workspaceWithUser('owner');

    Project::factory()->active()->count(3)->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('activeProjectsCount', 3)
        );
});

it('member only sees active projects they are a member of', function () {
    [$user, $workspace] = workspaceWithUser('member');

    $myProject = Project::factory()->active()->create();
    ProjectMember::factory()->create(['project_id' => $myProject->id, 'user_id' => $user->id]);

    Project::factory()->active()->create(); // not a member

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('activeProjectsCount', 1)
        );
});

it('sums hours this week from started_at', function () {
    [$user, $workspace] = workspaceWithUser('owner');

    $project = Project::factory()->active()->create();

    TimeEntry::factory()->create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'duration_minutes' => 90,
        'started_at' => now()->startOfWeek()->addHours(2),
        'ended_at' => now()->startOfWeek()->addHours(3)->addMinutes(30),
    ]);

    TimeEntry::factory()->create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'duration_minutes' => 30,
        'started_at' => now()->subWeek(),
        'ended_at' => now()->subWeek()->addMinutes(30),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('hoursThisWeek', 1.5)
        );
});

it('member only sees their own hours this week', function () {
    [$user, $workspace] = workspaceWithUser('member');
    $otherUser = User::factory()->create();
    $workspace->members()->attach($otherUser->id, ['role' => 'member']);

    $project = Project::factory()->active()->create();

    TimeEntry::factory()->create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'duration_minutes' => 60,
        'started_at' => now()->startOfWeek()->addHours(1),
        'ended_at' => now()->startOfWeek()->addHours(2),
    ]);

    TimeEntry::factory()->create([
        'project_id' => $project->id,
        'user_id' => $otherUser->id,
        'duration_minutes' => 120,
        'started_at' => now()->startOfWeek()->addHours(3),
        'ended_at' => now()->startOfWeek()->addHours(5),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('hoursThisWeek', 1)
        );
});

it('identifies overdue tasks correctly', function () {
    [$user, $workspace] = workspaceWithUser('owner');

    $project = Project::factory()->active()->create();

    Task::factory()->todo()->create(['project_id' => $project->id, 'due_at' => now()->subDay()]);
    Task::factory()->done()->create(['project_id' => $project->id, 'due_at' => now()->subDay()]);
    Task::factory()->todo()->create(['project_id' => $project->id, 'due_at' => now()->addDay()]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('overdueTasks', 1)
        );
});

it('active projects list includes colour, status and client name', function () {
    [$user, $workspace] = workspaceWithUser('owner');

    $client = Client::factory()->create();
    Project::factory()->active()->create(['client_id' => $client->id, 'colour' => '#ff0000']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('activeProjects', 1, fn (AssertableInertia $p) => $p
                ->has('id')
                ->has('name')
                ->where('colour', '#ff0000')
                ->where('status', 'active')
                ->where('client_name', $client->name)
            )
        );
});

it('open tasks list includes priority, due date and project name', function () {
    [$user, $workspace] = workspaceWithUser('owner');

    $project = Project::factory()->active()->create();
    Task::factory()->todo()->create([
        'project_id' => $project->id,
        'due_at' => '2026-05-01',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('openTasks', 1, fn (AssertableInertia $t) => $t
                ->has('id')
                ->has('title')
                ->has('priority')
                ->where('due_at', '2026-05-01')
                ->where('project_name', $project->name)
            )
        );
});

it('recent time entries include user, project and duration', function () {
    [$user, $workspace] = workspaceWithUser('owner');

    $project = Project::factory()->active()->create();
    TimeEntry::factory()->create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'duration_minutes' => 75,
        'started_at' => now()->subHour(),
        'ended_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('recentTimeEntries', 1, fn (AssertableInertia $e) => $e
                ->has('id')
                ->where('duration_minutes', 75)
                ->where('project_name', $project->name)
                ->has('user')
                ->has('created_at')
            )
        );
});
