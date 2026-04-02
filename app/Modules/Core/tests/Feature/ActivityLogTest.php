<?php

use App\Modules\Core\Models\ActivityLog;
use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;
use App\Modules\Core\Models\WorkspaceInvitation;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;

it('can be created and has no updated_at column', function () {
    [$user, $workspace] = workspaceWithUser('owner');

    $log = ActivityLog::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'event' => 'created',
        'subject_type' => Workspace::class,
        'subject_id' => (string) $workspace->id,
        'properties' => null,
    ]);

    expect($log->id)->toBeInt()
        ->and($log->event)->toBe('created')
        ->and($log->updated_at)->toBeNull();
});

it('records a created event when a LogsActivity model is created', function () {
    [$user, $workspace] = workspaceWithUser('owner');
    app()->instance(Workspace::class, $workspace);

    $this->actingAs($user);

    WorkspaceInvitation::create([
        'workspace_id' => $workspace->id,
        'email' => 'invite@example.com',
        'role' => 'member',
        'token' => Str::random(64),
        'expires_at' => now()->addHours(48),
    ]);

    $log = ActivityLog::where('workspace_id', $workspace->id)
        ->where('event', 'created')
        ->where('subject_type', WorkspaceInvitation::class)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($user->id)
        ->and($log->properties)->toBeNull();
});

it('records an updated event with old and new values', function () {
    [$user, $workspace] = workspaceWithUser('owner');
    app()->instance(Workspace::class, $workspace);

    $this->actingAs($user);

    $invitation = WorkspaceInvitation::create([
        'workspace_id' => $workspace->id,
        'email' => 'invite2@example.com',
        'role' => 'member',
        'token' => Str::random(64),
        'expires_at' => now()->addHours(48),
    ]);

    $invitation->update(['accepted_at' => now()]);

    $log = ActivityLog::where('workspace_id', $workspace->id)
        ->where('event', 'updated')
        ->where('subject_type', WorkspaceInvitation::class)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->properties)->toHaveKey('old')
        ->and($log->properties)->toHaveKey('new');
});

it('does not log when workspace is not bound to the container', function () {
    // Simulate a console/seeder context where no workspace is in the container.
    // workspaceWithUser binds the workspace, so we need a fresh container binding check.
    $workspace = Workspace::factory()->create();
    User::factory()->create();

    // Ensure the workspace is NOT bound
    app()->forgetInstance(Workspace::class);

    WorkspaceInvitation::create([
        'workspace_id' => $workspace->id,
        'email' => 'console@example.com',
        'role' => 'member',
        'token' => Str::random(64),
        'expires_at' => now()->addHours(48),
    ]);

    expect(ActivityLog::where('workspace_id', $workspace->id)->count())->toBe(0);
});

it('renders the activity log page for a workspace owner', function () {
    [$user, $workspace] = workspaceWithUser('owner');

    ActivityLog::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'event' => 'created',
        'subject_type' => WorkspaceInvitation::class,
        'subject_id' => '1',
    ]);

    $this->actingAs($user)
        ->get(route('workspace.activity-log.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Core::workspace/settings/ActivityLog')
            ->has('logs', 1)
            ->has('logs.0', fn ($log) => $log
                ->where('event', 'created')
                ->where('subject_type', 'WorkspaceInvitation')
                ->etc()
            )
        );
});

it('renders the activity log page for a workspace admin', function () {
    [$admin, $workspace] = workspaceWithUser('admin');

    $this->actingAs($admin)
        ->get(route('workspace.activity-log.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Core::workspace/settings/ActivityLog')
        );
});

it('denies activity log access to workspace members', function () {
    [$member, $workspace] = workspaceWithUser('member');

    $this->actingAs($member)
        ->get(route('workspace.activity-log.index'))
        ->assertForbidden();
});

it('does not include hidden attributes in update activity log properties', function () {
    [$user, $workspace] = workspaceWithUser('owner');
    app()->instance(Workspace::class, $workspace);
    $this->actingAs($user);

    $invitation = WorkspaceInvitation::create([
        'workspace_id' => $workspace->id,
        'email' => 'hidden@example.com',
        'role' => 'member',
        'token' => Str::random(64),
        'expires_at' => now()->addHours(48),
    ]);

    $invitation->update(['accepted_at' => now()]);

    $log = ActivityLog::where('workspace_id', $workspace->id)
        ->where('event', 'updated')
        ->where('subject_type', WorkspaceInvitation::class)
        ->first();

    expect($log->properties['old'])->not->toHaveKey('token');
});

it('allows admins to access the activity log', function () {
    actingAsWorkspaceMember('admin');

    $this->get(route('workspace.activity-log.index'))->assertOk();
});

it('denies members from accessing the activity log', function () {
    [$user, $workspace] = actingAsWorkspaceMember('member');

    $this->get(route('workspace.activity-log.index'))->assertForbidden();
});

it('excludes activityLogExclude columns from activity log properties', function () {
    [$user, $workspace] = workspaceWithUser('owner');
    app()->instance(Workspace::class, $workspace);
    $this->actingAs($user);

    $invitation = WorkspaceInvitation::create([
        'workspace_id' => $workspace->id,
        'email' => 'exclude@example.com',
        'role' => 'member',
        'token' => Str::random(64),
        'expires_at' => now()->addHours(48),
    ]);

    $invitation->update(['expires_at' => now()->addHours(96)]);

    $log = ActivityLog::where('workspace_id', $workspace->id)
        ->where('event', 'updated')
        ->where('subject_type', WorkspaceInvitation::class)
        ->latest()
        ->first();

    expect($log->properties['old'])->not->toHaveKey('expires_at');
});
