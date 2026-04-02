<?php

use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\ClientContact;
use App\Modules\Core\Models\Scopes\WorkspaceScope;

it('stores a new contact on a client', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()->create();

    $this->post(route('clients.contacts.store', $client), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '+44 7700 900000',
        'role' => 'CEO',
        'is_primary' => false,
    ])->assertRedirect();

    expect($client->contacts()->count())->toBe(1)
        ->and($client->contacts()->first()->name)->toBe('Jane Doe');
});

it('validates name is required on contact store', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()->create();

    $this->post(route('clients.contacts.store', $client), [])
        ->assertSessionHasErrors(['name']);
});

it('validates email format on contact store', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()->create();

    $this->post(route('clients.contacts.store', $client), [
        'name' => 'Jane',
        'email' => 'not-an-email',
    ])->assertSessionHasErrors(['email']);
});

it('sets a contact as primary and clears others', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()->create();
    $contactA = ClientContact::factory()->create(['client_id' => $client->id, 'is_primary' => true]);
    $contactB = ClientContact::factory()->create(['client_id' => $client->id]);

    $this->patch(route('clients.contacts.update', [$client, $contactB]), [
        'name' => $contactB->name,
        'is_primary' => true,
    ])->assertRedirect();

    expect($contactA->fresh()->is_primary)->toBeFalse()
        ->and($contactB->fresh()->is_primary)->toBeTrue();
});

it('updates contact details', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()->create();
    $contact = ClientContact::factory()->create(['client_id' => $client->id, 'name' => 'Old Name']);

    $this->patch(route('clients.contacts.update', [$client, $contact]), [
        'name' => 'New Name',
    ])->assertRedirect();

    expect($contact->fresh()->name)->toBe('New Name');
});

it('deletes a contact', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()->create();
    $contact = ClientContact::factory()->create(['client_id' => $client->id]);

    $this->delete(route('clients.contacts.destroy', [$client, $contact]))
        ->assertRedirect();

    expect($client->contacts()->count())->toBe(0);
});

it('promotes next contact to primary when primary is deleted', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()->create();
    $primary = ClientContact::factory()->primary()->create(['client_id' => $client->id]);
    $other = ClientContact::factory()->create(['client_id' => $client->id]);

    $this->delete(route('clients.contacts.destroy', [$client, $primary]));

    expect($other->fresh()->is_primary)->toBeTrue();
});

it('prevents accessing contacts of a client in another workspace', function () {
    actingAsWorkspaceMember('admin');

    [$user, $otherWorkspace] = workspaceWithUser('admin');
    $foreignClient = Client::withoutGlobalScope(WorkspaceScope::class)
        ->where('workspace_id', $otherWorkspace->id)
        ->first();

    if (! $foreignClient) {
        $foreignClient = Client::factory()->make();
        $foreignClient->workspace_id = $otherWorkspace->id;
        $foreignClient->save();
    }

    // Re-bind original workspace
    actingAsWorkspaceMember('admin');

    $this->post(route('clients.contacts.store', $foreignClient), [
        'name' => 'Hacker',
    ])->assertNotFound();
});
