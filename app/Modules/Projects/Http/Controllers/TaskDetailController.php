<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Models\ActivityLog;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\Task;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TaskDetailController extends Controller
{
    public function __invoke(Project $project, Task $task): Response
    {
        abort_unless($task->project_id === $project->id, 404);

        $task->load([
            'assignee:id,name,profile_photo_path',
            'labels:id,name,colour',
            'milestone:id,name',
            'creator:id,name',
        ]);

        $subTasks = $task->subTasks()
            ->with('assignee:id,name,profile_photo_path')
            ->orderBy('position')
            ->get()
            ->map(fn (Task $sub) => [
                'id' => $sub->id,
                'title' => $sub->title,
                'status' => $sub->status->value,
                'completed_at' => $sub->completed_at?->toDateTimeString(),
                'assignee' => $sub->assignee ? [
                    'id' => $sub->assignee->id,
                    'name' => $sub->assignee->name,
                ] : null,
            ]);

        return Inertia::render('Projects::TaskDetail', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
            ],
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'due_at' => $task->due_at?->toDateTimeString(),
                'completed_at' => $task->completed_at?->toDateTimeString(),
                'estimated_hours' => $task->estimated_hours,
                'milestone' => $task->milestone ? [
                    'id' => $task->milestone->id,
                    'name' => $task->milestone->name,
                ] : null,
                'assignee' => $task->assignee ? [
                    'id' => $task->assignee->id,
                    'name' => $task->assignee->name,
                    'avatar' => $task->assignee->profile_photo_path,
                ] : null,
                'labels' => $task->labels->map(fn ($label) => [
                    'id' => $label->id,
                    'name' => $label->name,
                    'colour' => $label->colour,
                ]),
                'created_by' => $task->creator ? [
                    'name' => $task->creator->name,
                ] : null,
                'created_at' => $task->created_at->toDateTimeString(),
            ],
            'subTasks' => $subTasks,
            'canEdit' => Gate::check('update-project'),
            // Deferred: only loaded when panel is open
            'comments' => Inertia::defer(fn () => $task->comments()
                ->with('user:id,name,profile_photo_path')
                ->latest()
                ->get()
                ->map(fn ($comment) => [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'user' => [
                        'id' => $comment->user?->id,
                        'name' => $comment->user?->name,
                        'avatar' => $comment->user?->profile_photo_path,
                    ],
                    'created_at' => $comment->created_at->toDateTimeString(),
                ])
            ),
            'activity' => Inertia::defer(fn () => ActivityLog::query()
                ->where('subject_type', Task::class)
                ->where('subject_id', $task->id)
                ->with('actor:id,name,profile_photo_path')
                ->latest()
                ->limit(30)
                ->get()
                ->map(fn (ActivityLog $log) => [
                    'id' => $log->id,
                    'event' => $log->event,
                    'actor' => $log->actor ? [
                        'name' => $log->actor->name,
                    ] : null,
                    'created_at' => $log->created_at->toDateTimeString(),
                ])
            ),
        ]);
    }
}
