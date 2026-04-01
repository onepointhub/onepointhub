<?php

use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\TimeEntry;

it('stores a manual time entry', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();

    $this->post(route('projects.time.store', $project), [
        'description' => 'Design work',
        'started_at' => '2025-01-15 09:00:00',
        'ended_at' => '2025-01-15 11:00:00',
        'billable' => true,
    ])->assertRedirect();

    expect($project->timeEntries()->count())->toBe(1)
        ->and($project->timeEntries()->first()->duration_minutes)->toBe(120);
});

it('validates started_at is required', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();

    $this->post(route('projects.time.store', $project), [
        'ended_at' => now()->toDateTimeString(),
    ])->assertSessionHasErrors(['started_at']);
});

it('starts a timer', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();

    $this->post(route('projects.timer.start', $project))->assertOk();

    expect($project->timeEntries()->whereNull('ended_at')->count())->toBe(1);
});

it('stops a running timer', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();
    $entry = TimeEntry::factory()->create([
        'project_id' => $project->id,
        'user_id' => auth()->id(),
        'started_at' => now()->subMinutes(30),
        'ended_at' => null,
    ]);

    $this->post(route('projects.timer.stop', $project))->assertOk();

    expect($entry->fresh()->ended_at)->not()->toBeNull()
        ->and($entry->fresh()->duration_minutes)->toBeGreaterThanOrEqual(30);
});

it('renders the time log page', function () {
    actingAsWorkspaceMember('member');

    $project = Project::factory()->create();

    $this->get(route('projects.timelog', $project))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('projects/TimeLog'));
});

it('cannot delete invoiced time entries', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create();
    $entry = TimeEntry::factory()->create([
        'project_id' => $project->id,
        'user_id' => auth()->id(),
        'invoiced_at' => now(),
    ]);

    $this->delete(route('projects.time.destroy', [$project, $entry]))->assertForbidden();
});
