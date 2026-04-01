<?php

use App\Modules\Core\Models\User;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\Task;
use App\Modules\Projects\Models\TaskComment;

it('stores a comment', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id, 'parent_id' => null]);

    $this->post(route('projects.tasks.comments.store', [$project, $task]), [
        'body' => 'Great progress!',
    ])->assertRedirect();

    expect($task->comments()->count())->toBe(1);
});

it('validates body is required', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id, 'parent_id' => null]);

    $this->post(route('projects.tasks.comments.store', [$project, $task]), [])
        ->assertSessionHasErrors(['body']);
});

it('updates own comment', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id, 'parent_id' => null]);
    $comment = TaskComment::factory()->create(['task_id' => $task->id, 'user_id' => auth()->id(), 'body' => 'Old']);

    $this->patch(route('projects.tasks.comments.update', [$project, $task, $comment]), [
        'body' => 'Updated',
    ])->assertRedirect();

    expect($comment->fresh()->body)->toBe('Updated');
});

it('cannot update another user comment as member', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id, 'parent_id' => null]);
    $otherUser = User::factory()->create();
    $comment = TaskComment::factory()->create(['task_id' => $task->id, 'user_id' => $otherUser->id]);

    $this->patch(route('projects.tasks.comments.update', [$project, $task, $comment]), [
        'body' => 'Hack',
    ])->assertForbidden();
});

it('deletes own comment', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id, 'parent_id' => null]);
    $comment = TaskComment::factory()->create(['task_id' => $task->id, 'user_id' => auth()->id()]);

    $this->delete(route('projects.tasks.comments.destroy', [$project, $task, $comment]))->assertRedirect();

    expect(TaskComment::find($comment->id))->toBeNull();
});

it('toggles a reaction on a comment', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id, 'parent_id' => null]);
    $comment = TaskComment::factory()->create(['task_id' => $task->id]);

    // Add reaction
    $this->post(route('projects.tasks.comments.react', [$project, $task, $comment]), [
        'emoji' => '👍',
    ])->assertOk();

    expect($comment->reactions()->count())->toBe(1);

    // Toggle off
    $this->post(route('projects.tasks.comments.react', [$project, $task, $comment]), [
        'emoji' => '👍',
    ])->assertOk();

    expect($comment->reactions()->count())->toBe(0);
});
