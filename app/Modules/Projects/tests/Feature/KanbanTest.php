<?php

use App\Modules\Projects\Enums\TaskStatus;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\Task;

it('renders the board page', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();

    $this->get(route('projects.board', $project))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Projects::Board'));
});

it('returns tasks grouped by status', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    Task::factory()->create(['project_id' => $project->id, 'status' => TaskStatus::Todo, 'parent_id' => null]);
    Task::factory()->create(['project_id' => $project->id, 'status' => TaskStatus::InProgress, 'parent_id' => null]);

    $this->get(route('projects.board', $project))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('columns')
        );
});

it('moves a task to a new status', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id, 'status' => TaskStatus::Todo, 'parent_id' => null]);

    $this->patch(route('projects.tasks.move', [$project, $task]), [
        'status' => 'in_progress',
        'position' => 0,
    ])->assertOk();

    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);
});

it('only includes top-level tasks on the board', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    $parent = Task::factory()->create(['project_id' => $project->id, 'parent_id' => null, 'status' => TaskStatus::Todo->value]);
    Task::factory()->create(['project_id' => $project->id, 'parent_id' => $parent->id, 'status' => TaskStatus::Todo->value]);

    $this->get(route('projects.board', $project))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('columns.todo.0'));
});
