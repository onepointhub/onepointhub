<?php

use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

it('shares the active workspace with Inertia pages', function () {
    [$user, $workspace] = workspaceWithUser('owner');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('workspace', fn (AssertableInertia $w) => $w
                ->where('id', $workspace->id)
                ->where('name', $workspace->name)
                ->where('slug', $workspace->slug)
            )
        );
});

it('shares all workspaces belonging to the user', function () {
    [$user, $workspace] = workspaceWithUser('owner');

    // Attach a second workspace to the same user
    $workspace2 = Workspace::factory()->create();
    $user->workspaces()->attach($workspace2->id, ['role' => 'member']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('workspaces', 2)
        );
});
