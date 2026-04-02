<?php

use App\Modules\Core\Models\User;

it('returns member results matching the query', function () {
    [$owner, $workspace] = workspaceWithUser('owner');
    $member = User::factory()->create(['name' => 'Alice Wonderland']);
    $member->workspaces()->attach($workspace->id, ['role' => 'member']);

    $this->actingAs($owner)
        ->getJson(route('search', ['q' => 'Alice']))
        ->assertOk()
        ->assertJsonPath('results.0.label', 'Alice Wonderland')
        ->assertJsonPath('results.0.type', 'member');
});

it('returns an empty array for a query with no matches', function () {
    [$owner] = workspaceWithUser('owner');

    $this->actingAs($owner)
        ->getJson(route('search', ['q' => 'zzznomatch']))
        ->assertOk()
        ->assertJsonPath('results', []);
});

it('requires at least 2 characters', function () {
    [$owner] = workspaceWithUser('owner');

    $this->actingAs($owner)
        ->getJson(route('search', ['q' => 'a']))
        ->assertUnprocessable();
});

it('requires authentication', function () {
    $this->getJson(route('search', ['q' => 'test']))
        ->assertUnauthorized();
});
