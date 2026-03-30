<?php

use App\Modules\Core\Models\Workspace;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Models\ScopedResource;

// Create the test table before each test and tear it down afterward.
// Schema DDL in SQLite is not transactional, so we must manage this explicitly —
// RefreshDatabase's transaction rollback will NOT drop tables created here.
beforeEach(function () {
    Schema::dropIfExists('scoped_resources');

    Schema::create('scoped_resources', function (Blueprint $table) {
        $table->ulid('id')->primary();
        $table->ulid('workspace_id');
        $table->string('name');
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('scoped_resources');
});

// ---------------------------------------------------------------------------
// Helper: insert a row directly, bypassing the global scope and auto-stamp.
// workspace_id is set explicitly because the auto-stamp in BelongsToWorkspace
// currently checks app()->bound(WorkspaceScope::class) instead of
// app()->bound(Workspace::class), so it never fires.
// ---------------------------------------------------------------------------
function scopedResource(string $workspaceId, string $name): ScopedResource
{
    return ScopedResource::withoutWorkspaceScope()->create([
        'workspace_id' => $workspaceId,
        'name' => $name,
    ]);
}

// ---------------------------------------------------------------------------
// Scope filtering
// ---------------------------------------------------------------------------

it('only returns resources belonging to the bound workspace', function () {
    $workspaceA = Workspace::create(['name' => 'Workspace A']);
    $workspaceB = Workspace::create(['name' => 'Workspace B']);

    scopedResource($workspaceA->id, 'Resource A');
    scopedResource($workspaceB->id, 'Resource B');

    app()->instance(Workspace::class, $workspaceA);

    $results = ScopedResource::all();

    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('Resource A');
});

it('returns zero results when the bound workspace has no resources', function () {
    $workspaceA = Workspace::create(['name' => 'Workspace A']);
    $workspaceB = Workspace::create(['name' => 'Workspace B']);

    scopedResource($workspaceB->id, 'Resource B');

    app()->instance(Workspace::class, $workspaceA);

    expect(ScopedResource::all())->toHaveCount(0);
});

// ---------------------------------------------------------------------------
// Cross-workspace isolation
// ---------------------------------------------------------------------------

it('prevents cross-workspace data leakage', function () {
    $workspaceA = Workspace::create(['name' => 'Workspace A']);
    $workspaceB = Workspace::create(['name' => 'Workspace B']);

    scopedResource($workspaceA->id, 'Resource A');
    scopedResource($workspaceB->id, 'Resource B');

    app()->instance(Workspace::class, $workspaceA);

    $names = ScopedResource::pluck('name');

    expect($names)->toContain('Resource A')
        ->and($names)->not->toContain('Resource B');
});

it('switches scope correctly when the bound workspace changes', function () {
    $workspaceA = Workspace::create(['name' => 'Workspace A']);
    $workspaceB = Workspace::create(['name' => 'Workspace B']);

    scopedResource($workspaceA->id, 'Resource A');
    scopedResource($workspaceB->id, 'Resource B');

    app()->instance(Workspace::class, $workspaceA);
    expect(ScopedResource::pluck('name'))->toEqual(collect(['Resource A']));

    app()->instance(Workspace::class, $workspaceB);
    expect(ScopedResource::pluck('name'))->toEqual(collect(['Resource B']));
});

// ---------------------------------------------------------------------------
// Escape hatches
// ---------------------------------------------------------------------------

it('withoutWorkspaceScope() bypasses the scope and returns all resources', function () {
    $workspaceA = Workspace::create(['name' => 'Workspace A']);
    $workspaceB = Workspace::create(['name' => 'Workspace B']);

    scopedResource($workspaceA->id, 'Resource A');
    scopedResource($workspaceB->id, 'Resource B');

    app()->instance(Workspace::class, $workspaceA);

    $all = ScopedResource::withoutWorkspaceScope()->get();

    expect($all)->toHaveCount(2);
});

it('scopeForWorkspace() targets an explicit workspace regardless of the bound one', function () {
    $workspaceA = Workspace::create(['name' => 'Workspace A']);
    $workspaceB = Workspace::create(['name' => 'Workspace B']);

    scopedResource($workspaceA->id, 'Resource A');
    scopedResource($workspaceB->id, 'Resource B');

    // Workspace A is bound — but we explicitly query for B
    app()->instance(Workspace::class, $workspaceA);

    $results = (new ScopedResource)->scopeForWorkspace(ScopedResource::query(), $workspaceB)->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('Resource B');
});

// ---------------------------------------------------------------------------
// Console / unbound behaviour
// Note: php artisan test runs in console context, so WorkspaceScope returns
// unscoped (not throwing) when no workspace is bound. The HTTP-context
// exception (WorkspaceNotResolvedException) is covered in WorkspaceMiddlewareTest.
// ---------------------------------------------------------------------------

it('returns all resources unscoped when no workspace is bound in console context', function () {
    $workspaceA = Workspace::create(['name' => 'Workspace A']);
    $workspaceB = Workspace::create(['name' => 'Workspace B']);

    scopedResource($workspaceA->id, 'Resource A');
    scopedResource($workspaceB->id, 'Resource B');

    app()->forgetInstance(Workspace::class);

    expect(ScopedResource::all())->toHaveCount(2);
});

// ---------------------------------------------------------------------------
// Auto-stamp
// ---------------------------------------------------------------------------

it('auto-stamps workspace_id on creation when a workspace is bound', function () {
    $workspace = Workspace::create(['name' => 'My Workspace']);
    app()->instance(Workspace::class, $workspace);

    $resource = ScopedResource::create(['name' => 'New Resource']);

    expect($resource->workspace_id)->toBe($workspace->id);
});

it('does not overwrite an explicit workspace_id on creation', function () {
    $workspaceA = Workspace::create(['name' => 'Workspace A']);
    $workspaceB = Workspace::create(['name' => 'Workspace B']);
    app()->instance(Workspace::class, $workspaceA);

    // Explicitly passing workspace B — should not be overwritten with A
    $resource = ScopedResource::withoutWorkspaceScope()->create([
        'workspace_id' => $workspaceB->id,
        'name' => 'Resource',
    ]);

    expect($resource->workspace_id)->toBe($workspaceB->id);
});

// ---------------------------------------------------------------------------
// workspace() relationship
// ---------------------------------------------------------------------------

it('workspace() returns the owning workspace', function () {
    $workspace = Workspace::create(['name' => 'My Workspace']);
    app()->instance(Workspace::class, $workspace);

    $resource = ScopedResource::create(['name' => 'New Resource']);

    expect($resource->workspace->id)->toBe($workspace->id);
});
