<?php

use App\Modules\Projects\Enums\TaskStatus;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\Task;
use App\Modules\Projects\Models\TaskLabel;

it('stores a new task', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create();

    $this->post(route('projects.tasks.store', $project), [
        'title' => 'My First Task',
        'status' => 'todo',
        'priority' => 'medium',
    ])->assertRedirect();

    expect($project->tasks()->where('title', 'My First Task')->exists())->toBeTrue();
});

it('validates title is required', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create();

    $this->post(route('projects.tasks.store', $project), [
        'status' => 'todo',
    ])->assertSessionHasErrors(['title']);
});

it('updates a task', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id, 'title' => 'Old Title']);

    $this->patch(route('projects.tasks.update', [$project, $task]), [
        'title' => 'New Title',
        'status' => 'in_progress',
    ])->assertRedirect();

    expect($task->fresh()->title)->toBe('New Title')
        ->and($task->fresh()->status)->toBe(TaskStatus::InProgress);
});

it('deletes a task', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id]);

    $this->delete(route('projects.tasks.destroy', [$project, $task]))->assertRedirect();

    expect(Task::find($task->id))->toBeNull();
});

it('creates a sub-task', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create();
    $parent = Task::factory()->create(['project_id' => $project->id]);

    $this->post(route('projects.tasks.store', $project), [
        'title' => 'Sub Task',
        'status' => 'todo',
        'parent_id' => $parent->id,
    ])->assertRedirect();

    expect($parent->subTasks()->count())->toBe(1);
});

it('creates task labels with factory', function () {
    actingAsWorkspaceMember('admin');

    $label = TaskLabel::factory()->create();

    expect($label->name)->not()->toBeEmpty();
});
