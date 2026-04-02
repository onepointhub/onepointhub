<?php

use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;

it('lists workspace members', function () {
    $workspace = Workspace::factory()->create();
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $owner->workspaces()->attach($workspace->id, ['role' => 'owner']);
    $member->workspaces()->attach($workspace->id, ['role' => 'member']);
    app()->instance(Workspace::class, $workspace);

    $this->actingAs($owner)->get(route('workspace.members.index'))
        ->assertOk()
        ->assertSee($member->name);
});

it('updates a member role', function () {
    $workspace = Workspace::factory()->create();
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $owner->workspaces()->attach($workspace->id, ['role' => 'owner']);
    $member->workspaces()->attach($workspace->id, ['role' => 'member']);
    app()->instance(Workspace::class, $workspace);
    setPermissionsTeamId($workspace->id);
    $owner->assignRole('owner');

    $this->actingAs($owner)->patch(route('workspace.members.update', $member), [
        'role' => 'admin',
    ])->assertRedirect();

    expect($member->workspaces()->where('workspaces.id', $workspace->id)->first()->pivot->role)->toBe('admin');
});

it('removes a member from the workspace', function () {
    $workspace = Workspace::factory()->create();
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $owner->workspaces()->attach($workspace->id, ['role' => 'owner']);
    $member->workspaces()->attach($workspace->id, ['role' => 'member']);
    app()->instance(Workspace::class, $workspace);
    setPermissionsTeamId($workspace->id);
    $owner->assignRole('owner');

    $this->actingAs($owner)->delete(route('workspace.members.destroy', $member))
        ->assertRedirect();

    expect($member->workspaces()->where('workspaces.id', $workspace->id)->exists())->toBeFalse();
});

it('prevents removing the sole owner of a workspace', function () {
    $workspace = Workspace::factory()->create();
    $owner = User::factory()->create();
    $owner->workspaces()->attach($workspace->id, ['role' => 'owner']);
    app()->instance(Workspace::class, $workspace);
    setPermissionsTeamId($workspace->id);
    $owner->assignRole('owner');

    $this->actingAs($owner)->delete(route('workspace.members.destroy', $owner))
        ->assertSessionHasErrors();

    expect($owner->workspaces()->where('workspaces.id', $workspace->id)->exists())->toBeTrue();
});
