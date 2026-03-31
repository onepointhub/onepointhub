<?php

use App\Modules\Core\Models\User;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;

it('renders the create project page', function () {
    actingAsWorkspaceMember('member');

    $this->get(route('projects.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Projects::Create'));
});

it('stores a new project', function () {
    actingAsWorkspaceMember('admin');

    $this->post(route('projects.store'), [
        'name' => 'New Project',
        'status' => 'active',
        'type' => 'fixed',
        'members' => [],
    ])->assertRedirect();

    expect(Project::where('name', 'New Project')->exists())->toBeTrue();
});

it('validates name is required', function () {
    actingAsWorkspaceMember('admin');

    $this->post(route('projects.store'), [
        'status' => 'active',
        'type' => 'fixed',
    ])->assertSessionHasErrors(['name']);
});

it('validates budget is numeric when provided', function () {
    actingAsWorkspaceMember('admin');

    $this->post(route('projects.store'), [
        'name' => 'Budget Test',
        'status' => 'active',
        'type' => 'fixed',
        'budget' => 'not-a-number',
        'members' => [],
    ])->assertSessionHasErrors(['budget']);
});

it('renders the edit project page', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();

    $this->get(route('projects.edit', $project))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Projects::Edit')
            ->where('project.id', $project->id)
        );
});

it('updates an existing project', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create(['name' => 'Old Name']);

    $this->patch(route('projects.update', $project), [
        'name' => 'New Name',
        'status' => 'active',
        'type' => 'fixed',
        'members' => [],
    ])->assertRedirect();

    expect($project->fresh()->name)->toBe('New Name');
});

it('syncs team members on store', function () {
    actingAsWorkspaceMember('admin');

    $user = User::factory()->create();

    $this->post(route('projects.store'), [
        'name' => 'Team Project',
        'status' => 'active',
        'type' => 'fixed',
        'members' => [
            ['user_id' => $user->id, 'role' => 'member', 'hourly_rate' => null],
        ],
    ])->assertRedirect();

    $project = Project::where('name', 'Team Project')->first();

    expect($project->members()->count())->toBe(1);
});

it('returns 404 for a project in another workspace', function () {
    actingAsWorkspaceMember('admin');

    [$user, $otherWorkspace] = workspaceWithUser('admin');

    $foreign = new Project;
    $foreign->workspace_id = $otherWorkspace->id;
    $foreign->name = 'Foreign';
    $foreign->status = ProjectStatus::Active;
    $foreign->type = ProjectType::Fixed;
    $foreign->saveQuietly();

    actingAsWorkspaceMember('admin');

    $this->get(route('projects.edit', $foreign))->assertNotFound();
});
