<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProjectController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = Project::query()
            ->with(['client:id,name', 'members.user:id,name,avatar'])
            //            ->withCount(['tasks', 'tasks as completed_tasks_count' => fn ($q) => $q->whereNotNull('completed_at')])
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
                //                'tasks_count' => $project->tasks_count,
                //                'completed_tasks_count' => $project->completed_tasks_count,
                'members' => $project->members->map(fn ($m) => [
                    'id' => $m->user?->id,
                    'name' => $m->user?->name,
                    'avatar' => $m->user?->avatar,
                ]),
            ]),
            'filters' => $request->only(['search', 'status', 'client_id', 'member_id', 'sort', 'direction']),
            'canCreate' => Gate::check('create-project'),
        ]);
    }
}
