<?php

namespace App\Modules\Core\Http\Controllers;

use App\Modules\Clients\Models\Client;
use App\Modules\Core\Enums\WorkspaceRole;
use App\Modules\Core\Models\ActivityLog;
use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;
use App\Modules\Core\Models\WorkspaceInvitation;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\TaskStatus;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\Task;
use App\Modules\Projects\Models\TimeEntry;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $workspace = app(Workspace::class);
        $isAdminOrOwner = $this->isAdminOrOwner($workspace);

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
                'avatar' => $member->profile_photo_path,
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
                    'avatar' => $log->actor->profile_photo_path,
                ] : null,
                'created_at' => $log->created_at->toISOString(),
            ]);

        $projectBase = $isAdminOrOwner
            ? Project::query()
            : Project::whereHas('members', fn ($q) => $q->where('user_id', auth()->id()));

        $taskBase = $isAdminOrOwner
            ? Task::query()
            : Task::where('assigned_to', auth()->id());

        $timeBase = $isAdminOrOwner
            ? TimeEntry::query()
            : TimeEntry::where('user_id', auth()->id());

        $activeProjectsCount = (clone $projectBase)
            ->where('status', ProjectStatus::Active)
            ->count();

        $openTasksCount = (clone $taskBase)
            ->where('status', '!=', TaskStatus::Done)
            ->count();

        $clientsCount = Client::count();

        $hoursThisWeek = round(
            (clone $timeBase)
                ->whereBetween('started_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('duration_minutes') / 60,
            1
        );

        $activeProjects = (clone $projectBase)
            ->where('status', ProjectStatus::Active)
            ->with('client:id,name')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Project $project) => [
                'id' => $project->id,
                'name' => $project->name,
                'colour' => $project->colour,
                'status' => $project->status->value,
                'client_name' => $project->client?->name,
            ]);

        $openTasks = (clone $taskBase)
            ->where('status', '!=', TaskStatus::Done)
            ->with('project:id,name')
            ->orderByRaw('due_at IS NULL, due_at ASC')
            ->limit(5)
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'priority' => $task->priority->value,
                'due_at' => $task->due_at?->toDateString(),
                'project_name' => $task->project?->name,
            ]);

        $overdueTasks = (clone $taskBase)
            ->where('status', '!=', TaskStatus::Done)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now()->startOfDay())
            ->with('project:id,name')
            ->orderBy('due_at')
            ->limit(5)
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'priority' => $task->priority->value,
                'due_at' => $task->due_at?->toDateString(),
                'days_overdue' => (int) now()->startOfDay()->diffInDays($task->due_at),
                'project_name' => $task->project?->name,
            ]);

        $recentTimeEntries = (clone $timeBase)
            ->with(['user:id,name,profile_photo_path', 'project:id,name'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (TimeEntry $entry) => [
                'id' => $entry->id,
                'duration_minutes' => $entry->duration_minutes,
                'project_name' => $entry->project?->name,
                'user' => $entry->user ? [
                    'name' => $entry->user->name,
                    'avatar' => $entry->user->profile_photo_path,
                ] : null,
                'created_at' => $entry->created_at->toISOString(),
            ]);

        return Inertia::render('Core::Dashboard', [
            'memberCount' => $memberCount,
            'pendingInvitationsCount' => $pendingInvitationsCount,
            'recentMembers' => $recentMembers,
            'recentActivity' => $recentActivity,
            'activeProjectsCount' => $activeProjectsCount,
            'openTasksCount' => $openTasksCount,
            'clientsCount' => $clientsCount,
            'hoursThisWeek' => $hoursThisWeek,
            'activeProjects' => $activeProjects,
            'openTasks' => $openTasks,
            'overdueTasks' => $overdueTasks,
            'recentTimeEntries' => $recentTimeEntries,
        ]);
    }

    protected function isAdminOrOwner(Workspace $workspace): bool
    {
        $role = $workspace->members()
            ->where('user_id', auth()->id())
            ->first()
            ?->pivot
            ?->role;

        return in_array($role, [WorkspaceRole::Owner->value, WorkspaceRole::Admin->value]);
    }
}
