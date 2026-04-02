<?php

use App\Modules\Core\Models\Workspace;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Milestone;
use App\Modules\Projects\Models\Project;

it('stores a milestone', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create();

    $this->post(route('projects.milestones.store', $project), [
        'name' => 'Sprint 1',
        'due_at' => now()->addDays(14)->toDateString(),
    ])->assertRedirect();

    expect($project->milestones()->where('name', 'Sprint 1')->exists())->toBeTrue();
});

it('validates name is required', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create();

    $this->post(route('projects.milestones.store', $project), [])
        ->assertSessionHasErrors(['name']);
});

it('updates a milestone', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create();
    $milestone = Milestone::factory()->create(['project_id' => $project->id, 'name' => 'Old Name']);

    $this->patch(route('projects.milestones.update', [$project, $milestone]), [
        'name' => 'New Name',
    ])->assertRedirect();

    expect($milestone->fresh()->name)->toBe('New Name');
});

it('deletes a milestone', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create();
    $milestone = Milestone::factory()->create(['project_id' => $project->id]);

    $this->delete(route('projects.milestones.destroy', [$project, $milestone]))->assertRedirect();

    expect(Milestone::find($milestone->id))->toBeNull();
});

it('marks a milestone as complete', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create();
    $milestone = Milestone::factory()->create(['project_id' => $project->id, 'completed_at' => null]);

    $this->patch(route('projects.milestones.complete', [$project, $milestone]))->assertRedirect();

    expect($milestone->fresh()->completed_at)->not()->toBeNull();
});

it('prevents accessing milestones of another workspace project', function () {
    actingAsWorkspaceMember('admin');

    [$user, $otherWorkspace] = workspaceWithUser('admin');

    $foreign = new Project;
    $foreign->workspace_id = $otherWorkspace->id;
    $foreign->name = 'Foreign';
    $foreign->status = ProjectStatus::Active;
    $foreign->type = ProjectType::Fixed;
    $foreign->saveQuietly();

    actingAsWorkspaceMember('admin');

    $this->post(route('projects.milestones.store', $foreign), ['name' => 'Hack'])->assertNotFound();
});

it('cannot update a milestone from a different workspace even if project_id matches', function () {
    actingAsWorkspaceMember('admin');

    $ownProject = Project::factory()->create();

    // Build a milestone in another workspace but pointing at our project (simulates crafted attack)
    $otherWorkspace = Workspace::factory()->create();
    $orphan = new Milestone;
    $orphan->workspace_id = $otherWorkspace->id;
    $orphan->project_id = $ownProject->id;
    $orphan->name = 'Orphan';
    $orphan->saveQuietly();

    $this->patch(route('projects.milestones.update', [$ownProject, $orphan]), [
        'name' => 'Hacked',
    ])->assertNotFound();
});
