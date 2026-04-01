<?php

use App\Modules\Projects\Models\Project;
use Illuminate\Support\Facades\Auth;

it('allows workspace members with view-project to see a project', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();

    $this->get(route('projects.show', $project))->assertOk();
});

it('denies workspace members without view-project from seeing the project show page', function () {
    [$user, $workspace] = actingAsWorkspaceMember('member');

    setPermissionsTeamId($workspace->id);
    Auth::user()?->roles()->detach();
    Auth::user()?->forgetCachedPermissions();

    $project = Project::factory()->create();

    $this->get(route('projects.show', $project))->assertForbidden();
});

it('denies workspace members without view-project from seeing the project board', function () {
    [$user, $workspace] = actingAsWorkspaceMember('member');

    setPermissionsTeamId($workspace->id);
    Auth::user()?->roles()->detach();
    Auth::user()?->forgetCachedPermissions();

    $project = Project::factory()->create();

    $this->get(route('projects.board', $project))->assertForbidden();
});

it('denies workspace members without view-project from seeing the task list', function () {
    [$user, $workspace] = actingAsWorkspaceMember('member');

    setPermissionsTeamId($workspace->id);
    Auth::user()?->roles()->detach();
    Auth::user()?->forgetCachedPermissions();

    $project = Project::factory()->create();

    $this->get(route('projects.tasks', $project))->assertForbidden();
});

it('denies workspace members without view-project from seeing the timelog', function () {
    [$user, $workspace] = actingAsWorkspaceMember('member');

    setPermissionsTeamId($workspace->id);
    Auth::user()?->roles()->detach();
    Auth::user()?->forgetCachedPermissions();

    $project = Project::factory()->create();

    $this->get(route('projects.timelog', $project))->assertForbidden();
});
