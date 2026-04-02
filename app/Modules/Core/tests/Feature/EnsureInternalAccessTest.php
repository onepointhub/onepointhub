<?php

use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;

it('blocks users with client workspace role from accessing internal routes', function () {
    $workspace = Workspace::factory()->create();
    $user = User::factory()->create();
    $user->workspaces()->attach($workspace->id, ['role' => 'client']);
    app()->instance(Workspace::class, $workspace);
    session(['active_workspace_id' => $workspace->id]);

    $this->actingAs($user)
        ->get(route('clients.index'))
        ->assertForbidden();
});

it('allows users with member workspace role to access internal routes', function () {
    actingAsWorkspaceMember('member');

    $this->get(route('clients.index'))->assertOk();
});
