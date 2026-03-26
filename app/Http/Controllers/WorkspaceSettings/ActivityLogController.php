<?php

namespace App\Http\Controllers\WorkspaceSettings;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Workspace;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('view-activity-log');

        $workspace = app(Workspace::class);

        $logs = ActivityLog::query()
            ->where('workspace_id', $workspace->id)
            ->with('actor')
            ->latest('created_at')
            ->limit(50)
            ->get()
            ->map(fn (ActivityLog $log) => [
                'id' => $log->id,
                'event' => $log->event,
                'subject_type' => class_basename($log->subject_type),
                'subject_id' => $log->subject_id,
                'actor' => $log->actor ? [
                    'id' => $log->actor->id,
                    'name' => $log->actor->name,
                    'avatar' => $log->actor->avatar,
                ] : null,
                'created_at' => $log->created_at->toISOString(),
            ]);

        return Inertia::render('workspace/settings/ActivityLog', [
            'logs' => $logs,
        ]);
    }
}
