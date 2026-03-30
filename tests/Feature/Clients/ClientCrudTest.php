<?php

use App\Modules\Clients\Enums\ClientStatus;
use App\Modules\Clients\Enums\ClientType;
use App\Modules\Clients\Models\Client;

it('renders the create form', function () {
    actingAsWorkspaceMember('admin');

    $this->get(route('clients.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('clients/Create'));
});

it('requires create-client permission', function () {
    actingAsWorkspaceMember('member'); // member has create-client

    $this->get(route('clients.create'))->assertOk();
});

it('stores a new client', function () {
    actingAsWorkspaceMember('admin');

    $this->post(route('clients.store'), [
        'name' => 'Globex Corp',
        'type' => ClientType::Company->value,
        'status' => ClientStatus::Active->value,
        'currency' => 'USD',
    ])->assertRedirect();

    $this->assertDatabaseHas('clients', ['name' => 'Globex Corp']);
});

it('auto-generates slug on store', function () {
    actingAsWorkspaceMember('admin');

    $this->post(route('clients.store'), [
        'name' => 'Globex Corp',
        'type' => ClientType::Company->value,
        'status' => ClientStatus::Active->value,
    ]);

    expect(Client::first()->slug)->toBe('globex-corp');
});

it('validates required fields on store', function () {
    actingAsWorkspaceMember('admin');

    $this->post(route('clients.store'), [])
        ->assertSessionHasErrors(['name', 'type', 'status']);
});

it('validates name is unique within workspace on store', function () {
    actingAsWorkspaceMember('admin');

    Client::factory()->create(['name' => 'Existing Corp']);

    $this->post(route('clients.store'), [
        'name' => 'Existing Corp',
        'type' => ClientType::Company->value,
        'status' => ClientStatus::Active->value,
    ])->assertSessionHasErrors(['name']);
});

it('renders the edit form with existing client data', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()->create();

    $this->get(route('clients.edit', $client))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('clients/Edit')
            ->where('client.id', $client->id)
        );
});

it('updates an existing client', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()->create(['name' => 'Old Name']);

    $this->patch(route('clients.update', $client), [
        'name' => 'New Name',
        'type' => $client->type->value,
        'status' => $client->status->value,
    ])->assertRedirect(route('clients.show', $client));

    expect($client->fresh()->name)->toBe('New Name');
});

it('validates name uniqueness excluding the current client on update', function () {
    actingAsWorkspaceMember('admin');

    $clientA = Client::factory()->create(['name' => 'Alpha']);
    $clientB = Client::factory()->create(['name' => 'Beta']);

    // Updating Beta to Alpha should fail
    $this->patch(route('clients.update', $clientB), [
        'name' => 'Alpha',
        'type' => $clientB->type->value,
        'status' => $clientB->status->value,
    ])->assertSessionHasErrors(['name']);

    // Updating Alpha to Alpha (same name) should pass
    $this->patch(route('clients.update', $clientA), [
        'name' => 'Alpha',
        'type' => $clientA->type->value,
        'status' => $clientA->status->value,
    ])->assertRedirect();
});

it('denies create to users without create-client permission', function () {
    actingAsWorkspaceMember('client'); // client role has no create-client

    $this->post(route('clients.store'), [
        'name' => 'Test',
        'type' => ClientType::Company->value,
        'status' => ClientStatus::Active->value,
    ])->assertForbidden();
});
