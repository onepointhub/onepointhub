<?php

namespace App\Modules\Core\Http\Controllers;

use App\Modules\Core\Models\ActivityLog;
use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;
use App\Modules\Core\Models\WorkspaceInvitation;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $workspace = app(Workspace::class);

        $memberCount = $workspace->members()->count();

        $pendingInvitationsCount = WorkspaceInvitation::where('workspace_id', $workspace->id)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->count();

        $recentMembers = $workspace->members()
            ->orderByPivot('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn (User $member) => [
                'id' => $member->id,
                'name' => $member->name,
                'avatar' => $member->avatar,
                'role' => $member->pivot->role,
            ]);

        $recentActivity = ActivityLog::query()
            ->where('workspace_id', $workspace->id)
            ->with('actor')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (ActivityLog $log) => [
                'id' => $log->id,
                'event' => $log->event,
                'subject_type' => class_basename($log->subject_type),
                'actor' => $log->actor ? [
                    'name' => $log->actor->name,
                    'avatar' => $log->actor->avatar,
                ] : null,
                'created_at' => $log->created_at->toISOString(),
            ]);

        return Inertia::render('Core::Dashboard', [
            'memberCount' => $memberCount,
            'pendingInvitationsCount' => $pendingInvitationsCount,
            'recentMembers' => $recentMembers,
            'recentActivity' => $recentActivity,
        ]);
    }
}
