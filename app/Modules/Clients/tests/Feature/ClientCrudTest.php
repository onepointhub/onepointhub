<?php

use App\Modules\Clients\Enums\ClientStatus;
use App\Modules\Clients\Enums\ClientType;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\CustomFieldDefinition;
use App\Modules\Clients\Models\CustomFieldValue;

it('renders the create form', function () {
    actingAsWorkspaceMember('admin');

    $this->get(route('clients.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Clients::clients/Create'));
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
            ->component('Clients::clients/Edit')
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

it('saves multiple custom field values on store', function () {
    [$user, $workspace] = actingAsWorkspaceMember('admin');

    $def1 = CustomFieldDefinition::factory()->create(['workspace_id' => $workspace->id]);
    $def2 = CustomFieldDefinition::factory()->create(['workspace_id' => $workspace->id]);

    $this->post(route('clients.store'), [
        'name' => 'Acme',
        'type' => ClientType::Company->value,
        'status' => ClientStatus::Active->value,
        'custom_fields' => [
            $def1->id => 'value one',
            $def2->id => 'value two',
        ],
    ])->assertRedirect();

    $client = Client::where('name', 'Acme')->first();

    expect(CustomFieldValue::where('model_id', $client->id)->count())->toBe(2);
});

it('updates existing custom field values on update without creating duplicates', function () {
    [$user, $workspace] = actingAsWorkspaceMember('admin');

    $client = Client::factory()->create();
    $def = CustomFieldDefinition::factory()->create(['workspace_id' => $workspace->id]);

    // Store initial value
    $this->patch(route('clients.update', $client), [
        'name' => $client->name,
        'type' => $client->type->value,
        'status' => $client->status->value,
        'custom_fields' => [$def->id => 'initial'],
    ])->assertRedirect();

    // Update the value
    $this->patch(route('clients.update', $client), [
        'name' => $client->name,
        'type' => $client->type->value,
        'status' => $client->status->value,
        'custom_fields' => [$def->id => 'updated'],
    ])->assertRedirect();

    expect(CustomFieldValue::where('model_id', $client->id)->count())->toBe(1)
        ->and(CustomFieldValue::where('model_id', $client->id)->value('value'))->toBe('updated');
});
