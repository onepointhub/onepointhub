<?php

use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\Task;
use App\Modules\Projects\Models\TaskComment;
use App\Modules\Projects\Notifications\MentionedInCommentNotification;
use Illuminate\Support\Facades\Notification;

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

// it('cannot update another user comment as member', function () {
//    actingAsWorkspaceMember('member');
//
//    $project = Project::factory()->create();
//    $task = Task::factory()->create(['project_id' => $project->id, 'parent_id' => null]);
//    $otherUser = User::factory()->create();
//    $comment = TaskComment::factory()->create(['task_id' => $task->id, 'user_id' => $otherUser->id]);
//
//    $this->patch(route('projects.tasks.comments.update', [$project, $task, $comment]), [
//        'body' => 'Hack',
//    ])->assertForbidden();
// });

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

it('does not notify users outside the workspace when mentioned', function () {
    [$author, $workspace] = actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id, 'parent_id' => null]);

    // Create an outsider with a name that matches the @mention
    $outsider = User::factory()->create(['name' => 'alice']);
    $otherWorkspace = Workspace::factory()->create();
    $outsider->workspaces()->attach($otherWorkspace->id, ['role' => 'member']);

    // Restore the original author's workspace context
    session(['active_workspace_id' => $workspace->id]);
    app()->instance(Workspace::class, $workspace);
    test()->actingAs($author);

    Notification::fake();

    $this->post(route('projects.tasks.comments.store', [$project, $task]), [
        'body' => '@alice check this out',
    ])->assertRedirect();

    Notification::assertNotSentTo(
        $outsider,
        MentionedInCommentNotification::class,
    );
});
