<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Models\User;
use App\Modules\Projects\Http\Requests\StoreTaskRequest;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function store(StoreTaskRequest $request, Project $project): RedirectResponse
    {
        $data = $request->validated();
        /** @var array<string, mixed> $labelIds */
        $labelIds = $data['label_ids'] ?? [];
        unset($data['label_ids']);

        /** @var User $user */
        $user = $request->user();
        $data['created_by'] = $user->id;

        $task = $project->tasks()->create($data);

        if ($labelIds) {
            $task->labels()->sync($labelIds);
        }

        return redirect()->back();
    }

    public function update(StoreTaskRequest $request, Project $project, Task $task): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);

        $data = $request->validated();
        /** @var array<string, mixed> $labelIds */
        $labelIds = $data['label_ids'] ?? [];
        unset($data['label_ids']);

        $task->update($data);

        if ($labelIds) {
            $task->labels()->sync($labelIds);
        }

        return redirect()->back();
    }

    public function destroy(Project $project, Task $task): RedirectResponse
    {
        Gate::authorize('update-project');

        abort_unless($task->project_id === $project->id, 404);

        $task->delete();

        return redirect()->back();
    }
}
