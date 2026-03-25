<?php

use App\Models\User;
use App\Models\Workspace;
use Database\Seeders\PermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects authenticated users with no workspace to step 1', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('onboarding.workspace.create'));
});

it('renders step 1 — name your workspace', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('onboarding.workspace.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('onboarding/CreateWorkspace'));
});

it('creates workspace and assigns owner role to user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.workspace.store'), [
            'name' => 'Acme Corp',
        ])->assertRedirect(route('onboarding.invite'));

    $workspace = Workspace::query()->where('name', 'Acme Corp')->first();

    expect($workspace)->not->toBeNull()
        ->and($user->workspaces()->where('workspaces.id', $workspace->id)->exists())->toBeTrue();
});

it('renders step 2 — invite team (skippable)', function () {
    $this->seed(PermissionSeeder::class);

    [$user] = workspaceWithUser('owner');

    $this->actingAs($user)
        ->get(route('onboarding.invite'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('onboarding/Invite'));
});

it('can skip step 2 invite', function () {
    $this->seed(PermissionSeeder::class);

    [$user] = workspaceWithUser('owner');

    $this->actingAs($user)
        ->post(route('onboarding.invite.store'), ['skip' => true])
        ->assertRedirect(route('onboarding.currency'));
});

it('renders step 3 — choose currency', function () {
    $this->seed(PermissionSeeder::class);

    [$user] = workspaceWithUser('owner');

    $this->actingAs($user)
        ->get(route('onboarding.currency'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('onboarding/Currency'));
});

it('saves currency and redirects to dashboard', function () {
    $this->seed(PermissionSeeder::class);

    [$user, $workspace] = workspaceWithUser('owner');

    $this->actingAs($user)
        ->post(route('onboarding.currency.store'), [
            'currency' => 'EUR',
        ])->assertRedirect(route('dashboard'));

    expect($workspace->fresh()->currency)->toBe('EUR');
});
