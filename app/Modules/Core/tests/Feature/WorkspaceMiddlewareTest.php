<?php

use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;

it('redirects unauthenticated users', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('redirects authenticated user with no workspace to onboarding', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertRedirect(route('onboarding.workspace.create'));
});

it('binds workspace to container for authenticated user with workspace', function () {
    $workspace = Workspace::factory()->create();
    $user = User::factory()->create();
    $user->workspaces()->attach($workspace->id, ['role' => 'owner']);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk();

    expect(app(Workspace::class)->id)->toBe($workspace->id);
});

it('rejects session with workspace belonging to another user', function () {
    $workspace = Workspace::factory()->create();
    $user = User::factory()->create(); // not attached to workspace

    $this->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->get(route('dashboard'))
        ->assertRedirect(route('onboarding.workspace.create'));
});
