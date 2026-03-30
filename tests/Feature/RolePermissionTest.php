<?php

use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;

it('owner has all permissions', function () {
    $workspace = Workspace::factory()->create();
    $owner = User::factory()->create();
    $owner->workspaces()->attach($workspace->id, ['role' => 'owner']);

    setPermissionsTeamId($workspace->id);
    $owner->assignRole('owner');

    expect($owner->can('manage-members'))->toBeTrue()
        ->and($owner->can('delete-project'))->toBeTrue();
});

it('client has no internal permissions', function () {
    $workspace = Workspace::factory()->create();
    $client = User::factory()->create();
    $client->workspaces()->attach($workspace->id, ['role' => 'client']);

    setPermissionsTeamId($workspace->id);
    $client->assignRole('client');

    expect($client->can('manage-members'))->toBeFalse()
        ->and($client->can('create-project'))->toBeFalse();
});

it('client cannot access internal routes', function () {
    $workspace = Workspace::factory()->create();
    $client = User::factory()->create();
    $client->workspaces()->attach($workspace->id, ['role' => 'client']);
    app()->instance(Workspace::class, $workspace);
    setPermissionsTeamId($workspace->id);
    $client->assignRole('client');

    $this->actingAs($client)->get(route('dashboard'))
        ->assertForbidden();
});

it('access-internal gate returns false when no workspace is bound', function () {
    $user = User::factory()->create();
    app()->forgetInstance(Workspace::class);

    expect($user->can('access-internal'))->toBeFalse();
});
