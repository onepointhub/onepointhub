<?php

use App\Modules\Clients\Enums\ClientStatus;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\ClientContact;

it('requires authentication', function () {
    $this->get(route('clients.index'))->assertRedirect(route('login'));
});

it('renders the client list page', function () {
    actingAsWorkspaceMember('member');

    $this->get(route('clients.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('clients/Index'));
});

it('paginates clients at 25 per page', function () {
    actingAsWorkspaceMember('admin');

    Client::factory()->count(30)->create();

    $this->get(route('clients.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('clients.data', 25)
            ->where('clients.total', 30)
        );
});

it('filters clients by status', function () {
    actingAsWorkspaceMember('admin');

    Client::factory()->count(3)->create(['status' => ClientStatus::Active->value]);
    Client::factory()->count(2)->archived()->create();

    $this->get(route('clients.index', ['status' => 'archived']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('clients.data', 2));
});

it('filters clients by type', function () {
    actingAsWorkspaceMember('admin');

    Client::factory()->count(4)->company()->create();
    Client::factory()->count(1)->individual()->create();

    $this->get(route('clients.index', ['type' => 'individual']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('clients.data', 1));
});

it('searches clients by name', function () {
    actingAsWorkspaceMember('admin');

    Client::factory()->create(['name' => 'Acme Corp']);
    Client::factory()->create(['name' => 'Beta Ltd']);

    $this->get(route('clients.index', ['search' => 'Acme']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('clients.data', 1));
});

it('searches clients by contact email', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()->create();
    ClientContact::factory()->create(['client_id' => $client->id, 'email' => 'jane@example.com']);
    Client::factory()->create(); // no matching contact

    $this->get(route('clients.index', ['search' => 'jane@example.com']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('clients.data', 1));
});

it('excludes archived clients from default listing', function () {
    actingAsWorkspaceMember('admin');

    Client::factory()->count(3)->create();
    Client::factory()->count(2)->archived()->create();

    $this->get(route('clients.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('clients.data', 3));
});

it('exports clients as csv', function () {
    actingAsWorkspaceMember('admin');

    Client::factory()->count(3)->create();

    $response = $this->get(route('clients.export'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});
