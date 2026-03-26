<?php

use App\Models\WorkspaceInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

it('renders the dashboard with workspace stats for an authenticated user', function () {
    [$user, $workspace] = workspaceWithUser('owner');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
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
