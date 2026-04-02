<?php

use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;

it('renders the project list page', function () {
    actingAsWorkspaceMember('member');

    $this->get(route('projects.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Projects::Index'));
});

it('returns paginated projects', function () {
    actingAsWorkspaceMember('member');

    Project::factory()->count(3)->create();

    $this->get(route('projects.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('projects.data', 3));
});

it('filters projects by status', function () {
    actingAsWorkspaceMember('member');

    Project::factory()->count(2)->create(['status' => ProjectStatus::Active]);
    Project::factory()->count(1)->create(['status' => ProjectStatus::Completed]);

    $this->get(route('projects.index', ['status' => 'active']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('projects.data', 2));
});

it('searches projects by name', function () {
    actingAsWorkspaceMember('member');

    Project::factory()->create(['name' => 'Alpha Project']);
    Project::factory()->create(['name' => 'Beta Project']);

    $this->get(route('projects.index', ['search' => 'Alpha']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('projects.data', 1));
});

it('returns only projects belonging to the current workspace', function () {
    actingAsWorkspaceMember('admin');

    Project::factory()->count(2)->create();

    [$user, $otherWorkspace] = workspaceWithUser('admin');

    $foreign = new Project;
    $foreign->workspace_id = $otherWorkspace->id;
    $foreign->name = 'Foreign Project';
    $foreign->status = ProjectStatus::Active;
    $foreign->type = ProjectType::Fixed;
    $foreign->saveQuietly();

    actingAsWorkspaceMember('admin');

    $this->get(route('projects.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('projects.data', 0));
});

it('requires authentication', function () {
    $this->get(route('projects.index'))->assertRedirect(route('login'));
});
