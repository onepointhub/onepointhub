<?php

use App\Modules\Projects\Models\Milestone;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\Task;

it('renders the Gantt page', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create([
        'starts_at' => now()->startOfMonth(),
        'ends_at' => now()->addMonths(3)->endOfMonth(),
    ]);

    $this->get(route('projects.gantt', $project))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Projects::Gantt'));
});

it('returns milestones with due dates', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    Milestone::factory()->count(2)->create([
        'project_id' => $project->id,
        'due_at' => now()->addWeeks(2),
    ]);

    $this->get(route('projects.gantt', $project))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('milestones', 2));
});

it('returns tasks with date ranges', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    Task::factory()->count(3)->create([
        'project_id' => $project->id,
        'parent_id' => null,
        'due_at' => now()->addWeeks(1),
    ]);

    $this->get(route('projects.gantt', $project))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('tasks', 3));
});

it('returns project date range for timeline bounds', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create([
        'starts_at' => '2025-01-01',
        'ends_at' => '2025-06-30',
    ]);

    $this->get(route('projects.gantt', $project))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('project.starts_at', '2025-01-01')
            ->where('project.ends_at', '2025-06-30')
        );
});
