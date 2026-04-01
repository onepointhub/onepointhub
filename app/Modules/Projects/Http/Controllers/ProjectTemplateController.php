<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Models\Workspace;
use App\Modules\Projects\Http\Requests\StoreProjectRequest;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectTemplate;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ProjectTemplateController extends Controller
{
    public function index(): Response
    {
        $templates = ProjectTemplate::orderBy('is_builtin', 'desc')
            ->orderBy('name')
            ->get()
            ->map(fn (ProjectTemplate $t) => [
                'id' => $t->id,
                'name' => $t->name,
                'description' => $t->description,
                'is_builtin' => $t->is_builtin,
                'task_count' => count($t->structure['tasks'] ?? []),
                'milestone_count' => count($t->structure['milestones'] ?? []),
            ]);

        return Inertia::render('Projects::Templates', [
            'templates' => $templates,
            'canCreate' => Gate::check('create-project'),
        ]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        Gate::authorize('update-project');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $milestones = $project->milestones()->get()->map(fn ($m) => [
            'name' => $m->name,
            'due_offset_days' => null,
        ])->toArray();

        $milestoneMap = $project->milestones()->pluck('id')->toArray();

        $tasks = $project->tasks()
            ->whereNull('parent_id')
            ->orderBy('position')
            ->get()
            ->map(fn ($t) => [
                'title' => $t->title,
                'status' => $t->status->value,
                'priority' => $t->priority->value,
                'milestone_index' => $t->milestone_id
                    ? array_search($t->milestone_id, $milestoneMap, true)
                    : null,
            ])->toArray();

        ProjectTemplate::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'structure' => ['milestones' => $milestones, 'tasks' => $tasks],
            'is_builtin' => false,
        ]);

        return redirect()->route('projects.templates.index');
    }

    /**
     * @throws Throwable
     */
    public function fromTemplate(StoreProjectRequest $request): RedirectResponse
    {
        $workspace = app(Workspace::class);

        $request->validate([
            'template_id' => [
                'required',
                Rule::exists('project_templates', 'id')
                    ->where(function (Builder $query) use ($workspace) {
                        $query->where('workspace_id', $workspace->id)
                            ->orWhere('is_builtin', true);
                    }),
            ],
        ]);

        $template = ProjectTemplate::findOrFail($request->template_id);
        $data = $request->validated();
        unset($data['members']);

        $project = DB::transaction(function () use ($data, $template, $request) {
            $project = Project::create($data);

            $milestoneIds = [];
            foreach ($template->structure['milestones'] ?? [] as $i => $m) {
                $milestone = $project->milestones()->create(['name' => $m['name']]);
                $milestoneIds[$i] = $milestone->id;
            }

            foreach ($template->structure['tasks'] ?? [] as $t) {
                $project->tasks()->create([
                    'title' => $t['title'],
                    'status' => $t['status'],
                    'priority' => $t['priority'],
                    'milestone_id' => isset($t['milestone_index']) && isset($milestoneIds[$t['milestone_index']])
                        ? $milestoneIds[$t['milestone_index']]
                        : null,
                    'created_by' => $request->user()?->id,
                ]);
            }

            return $project;
        });

        return redirect()->route('projects.show', $project);
    }

    public function destroy(ProjectTemplate $template): RedirectResponse
    {
        Gate::authorize('delete-project');
        abort_if($template->is_builtin, 403);

        $template->delete();

        return redirect()->route('projects.templates.index');
    }
}
