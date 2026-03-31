<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Projects\Enums\TaskStatus;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TaskMoveController extends Controller
{
    public function __invoke(Request $request, Project $project, Task $task): JsonResponse
    {
        Gate::authorize('update-project');
        abort_unless($task->project_id === $project->id, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_column(TaskStatus::cases(), 'value'))],
            'position' => ['required', 'integer', 'min:0'],
        ]);

        $task->update([
            'status' => $validated['status'],
            'position' => $validated['position'],
        ]);

        return response()->json(['ok' => true]);
    }
}
