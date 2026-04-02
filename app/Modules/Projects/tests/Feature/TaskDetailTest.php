<?php

use App\Modules\Core\Models\User;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\Task;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the task detail page', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id, 'parent_id' => null]);

    $this->get(route('projects.tasks.detail', [$project, $task]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Projects::TaskDetail'));
});

it('returns task data', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id, 'title' => 'My Task', 'parent_id' => null]);

    $this->get(route('projects.tasks.detail', [$project, $task]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('task.title', 'My Task'));
});

it('returns sub-tasks', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id, 'parent_id' => null]);
    Task::factory()->count(2)->create(['project_id' => $project->id, 'parent_id' => $task->id]);

    $this->get(route('projects.tasks.detail', [$project, $task]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('subTasks', 2));
});

it('returns deferred activity', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id, 'parent_id' => null]);

    $this->get(route('projects.tasks.detail', [$project, $task]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('activity')
            )
        );
});

it('returns deferred comments', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id, 'parent_id' => null]);

    $this->get(route('projects.tasks.detail', [$project, $task]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('comments')
            )
        );
});

it('returns 404 for task in another project', function () {
    actingAsWorkspaceMember('admin');

    $project1 = Project::factory()->create();
    $project2 = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project2->id, 'parent_id' => null]);

    $this->get(route('projects.tasks.detail', [$project1, $task]))->assertNotFound();
});

it('includes the assignee profile_photo_path in task detail', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    $user = User::factory()->create(['profile_photo_path' => 'photos/test.jpg']);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'parent_id' => null,
        'assigned_to' => $user->id,
    ]);

    $this->get(route('projects.tasks.detail', [$project, $task]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('task.assignee.avatar', 'photos/test.jpg')
        );
});
