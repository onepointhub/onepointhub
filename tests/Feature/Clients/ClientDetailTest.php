<?php

use App\Models\Client;
use App\Models\ClientContact;
use App\Models\Scopes\WorkspaceScope;
use Inertia\Testing\AssertableInertia;

it('renders the client detail page', function () {
    actingAsWorkspaceMember('member');

    $client = Client::factory()->create();

    $this->get(route('clients.show', $client))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('clients/Show'));
});

it('returns the correct client data', function () {
    actingAsWorkspaceMember('member');

    $client = Client::factory()->create(['name' => 'Globex']);

    $this->get(route('clients.show', $client))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('client.name', 'Globex')
            ->where('client.id', $client->id)
        );
});

it('returns contacts on the detail page', function () {
    actingAsWorkspaceMember('member');

    $client = Client::factory()->create();
    ClientContact::factory()->count(2)->create(['client_id' => $client->id]);

    $this->get(route('clients.show', $client))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('contacts', 2));
});

it('returns deferred activity log', function () {
    actingAsWorkspaceMember('member');

    $client = Client::factory()->create();

    $this->get(route('clients.show', $client))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->missing('activity')
            ->loadDeferredProps(fn (AssertableInertia $reload) => $reload
                ->has('activity'))
        );
});

it('returns edit and delete capabilities based on permissions', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()->create();

    $this->get(route('clients.show', $client))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('canEdit', true)
            ->where('canDelete', true)
        );
});

it('returns correct capabilities for member role', function () {
    actingAsWorkspaceMember('member');

    $client = Client::factory()->create();

    $this->get(route('clients.show', $client))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('canEdit', true)  // member can update-client
            ->where('canDelete', false) // member cannot delete-client
        );
});

it('returns 404 for a client in another workspace', function () {
    actingAsWorkspaceMember('admin');

    [$user, $otherWorkspace] = workspaceWithUser('admin');
    $foreignClient = Client::withoutGlobalScope(WorkspaceScope::class)
        ->where('workspace_id', $otherWorkspace->id)->first()
        ?? tap(Client::factory()->make(), function ($c) use ($otherWorkspace) {
            $c->workspace_id = $otherWorkspace->id;
            $c->save();
        });

    actingAsWorkspaceMember('admin');

    $this->get(route('clients.show', $foreignClient))->assertNotFound();
});
