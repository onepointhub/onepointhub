<?php

use App\Modules\Clients\Enums\ClientStatus;
use App\Modules\Clients\Models\Client;

it('archives a client', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()->create();

    $response = $this->patch(route('clients.archive', $client));

    $response->assertRedirect(route('clients.index'));

    expect($client->fresh()->status)->toBe(ClientStatus::Archived);
});

it('restores an archived client', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()->archived()->create();

    $this->patch(route('clients.restore', $client))
        ->assertRedirect();

    expect($client->fresh()->status)->toBe(ClientStatus::Active);
});

it('soft-deletes a client', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()->create();

    $this->delete(route('clients.destroy', $client))
        ->assertRedirect(route('clients.index'));

    expect(Client::withTrashed()->find($client->id)->deleted_at)->not->toBeNull();
});

it('keeps soft-deleted clients retrievable for 30 days', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()->create();
    $client->delete();

    expect(Client::withTrashed()->find($client->id))->not->toBeNull();
});

it('permanently deletes a client after restoring from trash', function () {
    actingAsWorkspaceMember('admin');

    $client = Client::factory()->create();
    $client->delete();

    $this->delete(route('clients.destroy', $client))
        ->assertRedirect(route('clients.index'));

    expect(Client::withTrashed()->find($client->id))->toBeNull();
});

it('denies archive to member without delete-client permission', function () {
    actingAsWorkspaceMember('member');

    $client = Client::factory()->create();

    $this->patch(route('clients.archive', $client))->assertForbidden();
});

it('bulk archives selected clients', function () {
    actingAsWorkspaceMember('admin');

    $clients = Client::factory()->count(3)->create();
    $ids = $clients->pluck('id')->all();

    $this->post(route('clients.bulk-archive'), ['ids' => $ids])
        ->assertRedirect(route('clients.index'));

    expect(Client::where('status', ClientStatus::Archived->value)->count())->toBe(3);
});

it('bulk archive ignores ids outside the current workspace', function () {
    actingAsWorkspaceMember('admin');

    [$user, $otherWorkspace] = workspaceWithUser('admin');
    $foreignClient = Client::factory()->create([
        'workspace_id' => $otherWorkspace->id,
    ]);

    // Re-bind original workspace
    actingAsWorkspaceMember('admin');

    $this->post(route('clients.bulk-archive'), ['ids' => [$foreignClient->id]])
        ->assertRedirect(route('clients.index'));

    // Foreign client should remain active
    expect($foreignClient->fresh()->status)->toBe(ClientStatus::Active);
});
