<?php

use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Milestone;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectMember;

it('creates a project with factory', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create(['name' => 'Test Project']);

    expect($project->name)->toBe('Test Project')
        ->and($project->workspace_id)->toBe(app(Workspace::class)->id);
});

it('creates a project without a client', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create(['client_id' => null]);

    expect($project->client_id)->toBeNull();
});

it('creates project members with factory', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create();
    $user = User::factory()->create();
    $member = ProjectMember::factory()->create([
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);

    expect($project->members()->count())->toBe(1);
});

it('creates milestones with factory', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create();
    Milestone::factory()->count(3)->create(['project_id' => $project->id]);

    expect($project->milestones()->count())->toBe(3);
});

it('scopes projects to the current workspace', function () {
    actingAsWorkspaceMember('admin');

    Project::factory()->count(2)->create();

    [$user, $otherWorkspace] = workspaceWithUser('admin');

    $foreign = new Project;
    $foreign->workspace_id = $otherWorkspace->id;
    $foreign->name = 'Foreign';
    $foreign->status = ProjectStatus::Active;
    $foreign->type = ProjectType::Fixed;
    $foreign->saveQuietly();

    expect(Project::count())->toBe(1);
});
