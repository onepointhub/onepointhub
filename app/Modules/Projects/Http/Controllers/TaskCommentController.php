<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\Task;
use App\Modules\Projects\Models\TaskComment;
use App\Modules\Projects\Notifications\MentionedInCommentNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskCommentController extends Controller
{
    public function store(Request $request, Project $project, Task $task): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $comment = $task->comments()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

        // Dispatch mention notification
        preg_match_all('/@(\w+)/', $validated['body'], $matches);
        if (! empty($matches[1])) {
            app(Workspace::class)->members()
                ->whereIn('users.name', $matches[1])
                ->where('users.id', '!=', $user->id)
                ->each(fn (User $mentioned) => $mentioned->notify(new MentionedInCommentNotification($comment, $task)));
        }

        return redirect()->back();
    }

    public function update(Request $request, Project $project, Task $task, TaskComment $comment): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);
        abort_unless($comment->task_id === $task->id, 404);

        /** @var User $user */
        $user = $request->user();

        if ($comment->user_id !== $user->id) {
            Gate::authorize('update-project'); // admins can edit any
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $comment->update($validated);

        return redirect()->back();
    }

    public function destroy(Request $request, Project $project, Task $task, TaskComment $comment): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);
        abort_unless($comment->task_id === $task->id, 404);

        /** @var User $user */
        $user = $request->user();

        if ($comment->user_id !== $user->id) {
            Gate::authorize('update-project'); // admins can delete any
        }

        $comment->delete();

        return redirect()->back();
    }
}
