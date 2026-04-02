<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use UnhandledMatchError;

class TaskBulkController extends Controller
{
    public function __invoke(Request $request, Project $project): RedirectResponse
    {
        Gate::authorize('update-project');

        $validated = $request->validate([
            'task_ids' => ['required', 'array'],
            'task_ids.*' => ['integer', 'exists:tasks,id'],
            'action' => ['required', Rule::in(['status', 'assign', 'delete'])],
            'value' => ['nullable', 'string'],
        ]);

        $tasks = $project->tasks()->whereIn('id', $validated['task_ids']);

        match ($validated['action']) {
            'status' => $tasks->update(['status' => $validated['value']]),
            'assign' => $tasks->update(['assigned_to' => $validated['value']]),
            'delete' => $tasks->delete(),
            default => throw new UnhandledMatchError('Unsupported action: '.$validated['action']),
        };

        return redirect()->back();
    }
}
