<?php

use App\Modules\Projects\Models\Milestone;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectTemplate;
use App\Modules\Projects\Models\Task;

it('saves a project as a template', function () {
    actingAsWorkspaceMember('admin');

    $project = Project::factory()->create(['name' => 'My Project']);
    Milestone::factory()->count(2)->create(['project_id' => $project->id]);
    Task::factory()->count(3)->create(['project_id' => $project->id, 'parent_id' => null]);

    $this->post(route('projects.templates.store', $project), [
        'name' => 'My Template',
    ])->assertRedirect();

    expect(ProjectTemplate::where('name', 'My Template')->exists())->toBeTrue();
    $template = ProjectTemplate::where('name', 'My Template')->first();
    expect(count($template->structure['tasks']))->toBe(3)
        ->and(count($template->structure['milestones']))->toBe(2);
});

it('creates a project from a template', function () {
    actingAsWorkspaceMember('admin');

    $template = ProjectTemplate::factory()->create();

    $this->post(route('projects.from-template'), [
        'template_id' => $template->id,
        'name' => 'New from Template',
        'status' => 'active',
        'type' => 'fixed',
    ])->assertRedirect();

    $project = Project::where('name', 'New from Template')->first();
    expect($project)->not()->toBeNull()
        ->and($project->tasks()->count())->toBe(count($template->structure['tasks']));
});

it('cannot delete a built-in template', function () {
    actingAsWorkspaceMember('admin');

    $template = ProjectTemplate::factory()->builtin()->create();

    $this->delete(route('projects.templates.destroy', $template))->assertForbidden();
});

it('can delete a custom template', function () {
    actingAsWorkspaceMember('admin');

    $template = ProjectTemplate::factory()->create();

    $this->delete(route('projects.templates.destroy', $template))->assertRedirect();

    expect(ProjectTemplate::find($template->id))->toBeNull();
});

// it('lists available templates', function () {
//    actingAsWorkspaceMember('member');
//
//    ProjectTemplate::factory()->count(2)->create();
//
//    $this->get(route('projects.templates.index'))
//        ->assertOk()
//        ->assertInertia(fn ($page) => $page->component('projects/Templates')
//            ->has('templates', 2)
//        );
// });
