<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Models\User;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\Task;
use App\Modules\Projects\Models\TaskComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskCommentReactionController extends Controller
{
    public function __invoke(Request $request, Project $project, Task $task, TaskComment $comment): JsonResponse
    {
        abort_unless($task->project_id === $project->id, 404);
        abort_unless($comment->task_id === $task->id, 404);

        $validated = $request->validate([
            'emoji' => ['required', 'string', 'max:8'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $existing = $comment->reactions()
            ->where('user_id', $user->id)
            ->where('emoji', $validated['emoji'])
            ->first();

        if ($existing) {
            $existing->delete();
            $added = false;
        } else {
            $comment->reactions()->create([
                'user_id' => $user->id,
                'emoji' => $validated['emoji'],
            ]);
            $added = true;
        }

        return response()->json(['added' => $added]);
    }
}
