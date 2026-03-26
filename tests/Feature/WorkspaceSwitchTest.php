<?php

use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('switches the active workspace and redirects to dashboard', function () {
    [$user, $workspace1] = workspaceWithUser('owner');

    $workspace2 = Workspace::factory()->create();
    $user->workspaces()->attach($workspace2->id, ['role' => 'member']);

    $this->actingAs($user)
        ->post(route('workspace.switch'), ['workspace_id' => $workspace2->id])
        ->assertRedirect(route('dashboard'));

    expect(session('active_workspace_id'))->toBe($workspace2->id);
});

it('forbids switching to a workspace the user does not belong to', function () {
    [$user] = workspaceWithUser('member');
    $otherWorkspace = Workspace::factory()->create();

    $this->actingAs($user)
        ->post(route('workspace.switch'), ['workspace_id' => $otherWorkspace->id])
        ->assertForbidden();
});

it('requires authentication to switch workspace', function () {
    $workspace = Workspace::factory()->create();

    $this->post(route('workspace.switch'), ['workspace_id' => $workspace->id])
        ->assertRedirect(route('login'));
});
