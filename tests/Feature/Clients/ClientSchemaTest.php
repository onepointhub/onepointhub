<?php

use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\ClientAddress;
use App\Modules\Clients\Models\ClientContact;
use App\Modules\Core\Models\Workspace;
use App\Support\ModuleRegistry;

it('scopes clients to workspace', function () {
    [$user, $workspaceA] = workspaceWithUser();
    $clientA = Client::factory()->create();

    [$user, $workspaceB] = workspaceWithUser();
    Client::factory()->create();

    app()->instance(Workspace::class, $workspaceA);
    session(['active_workspace_id' => $workspaceA->id]);

    expect(Client::count())->toBe(1)
        ->and(Client::first()->id)->toBe($clientA->id);
});

it('auto-stamps workspace_id on creation', function () {
    actingAsWorkspaceMember('admin');
    $workspace = app(Workspace::class);

    $client = Client::factory()->create();

    expect($client->workspace_id)->toBe($workspace->id);
});

it('generates a unique slug from the client name', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::create(['name' => 'Acme Corp']);

    expect($client->slug)->toBe('acme-corp');
});

it('appends a suffix when a slug already exists in the workspace', function () {
    actingAsWorkspaceMember('admin');

    Client::create(['name' => 'Acme Corp']);
    $second = Client::create(['name' => 'Acme Corp']);

    expect($second->slug)->toBe('acme-corp-1');
});

it('creates client contacts belonging to the client', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()
        ->has(ClientContact::factory()->count(3), 'contacts')
        ->create();

    expect($client->contacts()->count())->toBe(3);
});

it('sets exactly one primary contact per client', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()
        ->has(ClientContact::factory()->primary(), 'contacts')
        ->has(ClientContact::factory()->count(2), 'contacts')
        ->create();

    expect($client->contacts()->where('is_primary', true)->count())->toBe(1);
});

it('creates client addresses belonging to the client', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()
        ->has(ClientAddress::factory(), 'addresses')
        ->create();

    expect($client->addresses()->count())->toBe(1);
});

it('registers the clients module on boot', function () {
    expect(app(ModuleRegistry::class)->has('clients'))->toBeTrue();
});
