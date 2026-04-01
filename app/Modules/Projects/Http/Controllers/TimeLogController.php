<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\TimeEntry;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TimeLogController extends Controller
{
    public function __invoke(Project $project): Response
    {
        Gate::authorize('view-project');

        $entries = $project->timeEntries()
            ->with('user:id,name,profile_photo_path', 'task:id,title')
            ->latest('started_at')
            ->get()
            ->map(fn (TimeEntry $e) => [
                'id' => $e->id,
                'description' => $e->description,
                'started_at' => $e->started_at->toDateTimeString(),
                'ended_at' => $e->ended_at?->toDateTimeString(),
                'duration_minutes' => $e->duration_minutes,
                'billable' => $e->billable,
                'invoiced_at' => $e->invoiced_at?->toDateTimeString(),
                'task' => $e->task ? ['id' => $e->task->id, 'title' => $e->task->title] : null,
                'user' => ['id' => $e->user?->id, 'name' => $e->user?->name, 'avatar' => $e->user?->profile_photo_path],
            ]);

        /** @var int $totalMinutes */
        $totalMinutes = $entries->sum('duration_minutes');
        /** @var int $billableMinutes */
        $billableMinutes = $entries->where('billable', true)->sum('duration_minutes');

        return Inertia::render('projects/TimeLog', [
            'project' => ['id' => $project->id, 'name' => $project->name],
            'entries' => $entries,
            'totals' => [
                'total_minutes' => $totalMinutes,
                'billable_minutes' => $billableMinutes,
                'non_billable_minutes' => $totalMinutes - $billableMinutes,
            ],
            'canEdit' => Gate::check('update-project'),
        ]);
    }
}
