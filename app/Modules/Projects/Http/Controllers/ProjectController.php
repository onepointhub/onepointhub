<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Modules\Clients\Models\Client;
use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Models\User;
use App\Modules\Projects\Enums\TaskStatus;
use App\Modules\Projects\Http\Requests\StoreProjectRequest;
use App\Modules\Projects\Http\Requests\UpdateProjectRequest;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectMember;
use App\Modules\Projects\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Throwable;

class ProjectController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = Project::query()
            ->with(['client:id,name', 'members.user:id,name,profile_photo_path'])
            ->withCount(['tasks', 'tasks as completed_tasks_count' => fn ($q) => $q->whereNotNull('completed_at')])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->string('search')}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->client_id, fn ($q) => $q->where('client_id', $request->client_id))
            ->when($request->member_id, fn ($q) => $q->whereHas('members', fn ($q) => $q->where('user_id', $request->member_id)));

        $sort = $request->string('sort', 'created_at')->toString();
        $direction = $request->string('direction', 'desc')->toString();

        if (in_array($sort, ['name', 'ends_at', 'created_at'])) {
            $query->orderBy($sort, $direction);
        }

        $projects = $query->paginate(24)->withQueryString();

        return Inertia::render('Projects::Index', [
            'projects' => $projects->through(fn (Project $project) => [
                'id' => $project->id,
                'name' => $project->name,
                'status' => $project->status,
                'type' => $project->type,
                'colour' => $project->colour,
                'ends_at' => $project->ends_at,
                'client' => $project->client ? [
                    'id' => $project->client->id,
                    'name' => $project->client->name,
                ] : null,
                'tasks_count' => $project->tasks_count,
                'completed_tasks_count' => $project->completed_tasks_count,
                'members' => $project->members->map(fn ($m) => [
                    'id' => $m->user?->id,
                    'name' => $m->user?->name,
                    'avatar' => $m->user?->profile_photo_path,
                ]),
            ]),
            'filters' => $request->only(['search', 'status', 'client_id', 'member_id', 'sort', 'direction']),
            'canCreate' => Gate::check('create-project'),
        ]);
    }

    public function create(): InertiaResponse
    {
        Gate::authorize('create-project');

        return Inertia::render('Projects::Create', [
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name', 'profile_photo_path']),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $data = $request->validated();
        /** @var array<string, mixed> $members */
        $members = $data['members'] ?? [];
        unset($data['members']);

        $project = DB::transaction(function () use ($data, $members) {
            $project = Project::create($data);

            /** @var array<string, mixed> $member */
            foreach ($members as $member) {
                $project->members()->create($member);
            }

            return $project;
        });

        return redirect()->route('projects.show', $project);
    }

    public function edit(Project $project): InertiaResponse
    {
        Gate::authorize('update-project');

        return Inertia::render('Projects::Edit', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'client_id' => $project->client_id,
                'status' => $project->status->value,
                'type' => $project->type->value,
                'budget' => $project->budget,
                'budget_type' => $project->budget_type?->value,
                'colour' => $project->colour,
                'starts_at' => $project->starts_at?->toDateTimeString(),
                'ends_at' => $project->ends_at?->toDateTimeString(),
                'members' => $project->members()->with('user:id,name,profile_photo_path')
                    ->get()
                    ->map(fn (ProjectMember $m) => [
                        'user_id' => $m->user_id,
                        'role' => $m->role,
                        'hourly_rate' => $m->hourly_rate,
                        'user' => ['id' => $m->user_id, 'name' => $m->user?->name, 'avatar' => $m->user?->profile_photo_path],
                    ]),
            ],
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name', 'profile_photo_path']),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $data = $request->validated();
        /** @var array<string, mixed> $members */
        $members = $data['members'] ?? [];
        unset($data['members']);

        DB::transaction(function () use ($project, $data, $members) {
            $project->update($data);

            $project->members()->delete();

            /** @var array<string, mixed> $member */
            foreach ($members as $member) {
                $project->members()->create($member);
            }
        });

        return redirect()->route('projects.show', $project);
    }

    public function show(Project $project): InertiaResponse
    {
        //        Gate::authorize('view-project');

        return Inertia::render('Projects::Show', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
            ],
        ]);
    }

    public function destroy(Project $project): RedirectResponse
    {
        Gate::authorize('delete-project');

        $project->delete();

        return redirect()->route('projects.index');
    }

    public function board(Project $project): InertiaResponse
    {
        //        Gate::authorize('view-project');

        $tasks = $project->tasks()
            ->whereNull('parent_id')
            ->with(['assignee:id,name,profile_photo_path', 'labels:id,name,colour'])
            ->withCount('subTasks')
            ->orderBy('position')
            ->get();

        $columns = collect(TaskStatus::cases())->mapWithKeys(fn (TaskStatus $status) => [
            $status->value => $tasks
                ->where('status', $status)
                ->map(fn (Task $task) => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status->value,
                    'priority' => $task->priority->value,
                    'position' => $task->position,
                    'due_at' => $task->due_at?->toDateTimeString(),
                    'sub_tasks_count' => $task->sub_tasks_count,
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
                ])->values(),
        ]);

        return Inertia::render('Projects::Board', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
            ],
            'columns' => $columns,
            'canEdit' => Gate::check('update-project'),
        ]);
    }
}
