<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Projects\Http\Requests\StoreMilestoneRequest;
use App\Modules\Projects\Models\Milestone;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class MilestoneController extends Controller
{
    public function store(StoreMilestoneRequest $request, Project $project): RedirectResponse
    {
        $project->milestones()->create($request->validated());

        return redirect()->back();
    }

    public function update(StoreMilestoneRequest $request, Project $project, Milestone $milestone): RedirectResponse
    {
        abort_unless($milestone->project_id === $project->id, 404);

        $milestone->update($request->validated());

        return redirect()->back();
    }

    public function destroy(Project $project, Milestone $milestone): RedirectResponse
    {
        Gate::authorize('update-project');

        abort_unless($milestone->project_id === $project->id, 404);

        $milestone->delete();

        return redirect()->back();
    }

    public function complete(Project $project, Milestone $milestone): RedirectResponse
    {
        Gate::authorize('update-project');

        abort_unless($milestone->project_id === $project->id, 404);

        $milestone->update(['completed_at' => now()]);

        return redirect()->back();
    }
}
