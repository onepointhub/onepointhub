<?php

use App\Modules\Core\Models\User;
use App\Modules\Projects\Enums\TaskPriority;
use App\Modules\Projects\Enums\TaskStatus;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\Task;

it('renders the tasks page', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();

    $this->get(route('projects.tasks', $project))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Projects::Tasks'));
});

it('returns top-level tasks only', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    $parent = Task::factory()->create(['project_id' => $project->id, 'parent_id' => null]);
    Task::factory()->create(['project_id' => $project->id, 'parent_id' => $parent->id]);

    $this->get(route('projects.tasks', $project))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('tasks', 1));
});

it('filters tasks by priority', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    Task::factory()->create(['project_id' => $project->id, 'priority' => TaskPriority::High, 'parent_id' => null]);
    Task::factory()->create(['project_id' => $project->id, 'priority' => TaskPriority::Low, 'parent_id' => null]);

    $this->get(route('projects.tasks', ['project' => $project->id, 'priority' => 'high']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('tasks', 1));
});

it('bulk changes status of tasks', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create();
    $tasks = Task::factory()->count(3)->create(['project_id' => $project->id, 'status' => TaskStatus::Todo, 'parent_id' => null]);

    $this->post(route('projects.tasks.bulk', $project), [
        'task_ids' => $tasks->pluck('id')->toArray(),
        'action' => 'status',
        'value' => 'done',
    ])->assertRedirect();

    expect(Task::where('project_id', $project->id)->where('status', TaskStatus::Done)->count())->toBe(3);
});

it('bulk deletes tasks', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create();
    $tasks = Task::factory()->count(2)->create(['project_id' => $project->id, 'parent_id' => null]);

    $this->post(route('projects.tasks.bulk', $project), [
        'task_ids' => $tasks->pluck('id')->toArray(),
        'action' => 'delete',
    ])->assertRedirect();

    expect($project->tasks()->count())->toBe(0);
});

it('bulk assigns tasks to a user', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create();
    $tasks = Task::factory()->count(2)->create(['project_id' => $project->id, 'parent_id' => null]);
    $user = User::factory()->create();

    $this->post(route('projects.tasks.bulk', $project), [
        'task_ids' => $tasks->pluck('id')->toArray(),
        'action' => 'assign',
        'value' => (string) $user->id,
    ])->assertRedirect();

    expect(Task::where('project_id', $project->id)->where('assigned_to', $user->id)->count())->toBe(2);
});

it('filters tasks by assignee', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    $user = User::factory()->create();
    Task::factory()->create(['project_id' => $project->id, 'parent_id' => null, 'assigned_to' => $user->id]);
    Task::factory()->create(['project_id' => $project->id, 'parent_id' => null, 'assigned_to' => null]);

    $this->get(route('projects.tasks', ['project' => $project->id, 'assignee_id' => $user->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('tasks', 1));
});
