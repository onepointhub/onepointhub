<?php

use App\Models\Client;
use App\Models\ClientContact;
use App\Models\PortalToken;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\PortalMagicLinkNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

it('sends a portal magic link to a primary contact', function () {
    actingAsWorkspaceMember('admin');

    $workspace = app(Workspace::class);
    $client = Client::factory()->create();
    ClientContact::factory()->primary()->create([
        'client_id' => $client->id,
        'email' => 'contact@example.com',
    ]);

    Notification::fake();

    $this->post(route('clients.portal.send-link', $client))
        ->assertRedirect();

    Notification::assertSentTo(
        new AnonymousNotifiable,
        PortalMagicLinkNotification::class,
    );

    $this->assertDatabaseHas('portal_tokens', ['client_id' => $client->id]);
});

it('consuming a valid token establishes a portal session', function () {
    $workspace = Workspace::factory()->create();
    $client = Client::factory()->make();
    $client->workspace_id = $workspace->id;
    $client->save();

    $contact = ClientContact::factory()->create(['client_id' => $client->id]);

    $token = PortalToken::create([
        'client_id' => $client->id,
        'contact_id' => $contact->id,
        'token' => $plain = Str::random(64),
        'expires_at' => now()->addHours(24),
        'consumed_at' => null,
    ]);

    $this->get(route('portal.auth.consume', [
        'workspace_slug' => $workspace->slug,
        'client_slug' => $client->slug,
        'token' => $plain,
    ]))->assertRedirect(route('portal.dashboard', [
        'workspace_slug' => $workspace->slug,
        'client_slug' => $client->slug,
    ]));

    expect(session('portal_contact_id'))->toBe($contact->id)
        ->and($token->fresh()->consumed_at)->not->toBeNull();
});

it('rejects an expired token', function () {
    $workspace = Workspace::factory()->create();
    $client = Client::factory()->make();
    $client->workspace_id = $workspace->id;
    $client->save();

    $contact = ClientContact::factory()->create(['client_id' => $client->id]);

    PortalToken::create([
        'client_id' => $client->id,
        'contact_id' => $contact->id,
        'token' => $plain = Str::random(64),
        'expires_at' => now()->subMinute(), // expired
        'consumed_at' => null,
    ]);

    $this->get(route('portal.auth.consume', [
        'workspace_slug' => $workspace->slug,
        'client_slug' => $client->slug,
        'token' => $plain,
    ]))->assertRedirect()->assertSessionHas('error');
});

it('rejects an already-consumed token', function () {
    $workspace = Workspace::factory()->create();
    $client = Client::factory()->make();
    $client->workspace_id = $workspace->id;
    $client->save();

    $contact = ClientContact::factory()->create(['client_id' => $client->id]);

    PortalToken::create([
        'client_id' => $client->id,
        'contact_id' => $contact->id,
        'token' => $plain = Str::random(64),
        'expires_at' => now()->addHours(24),
        'consumed_at' => now(), // already used
    ]);

    $this->get(route('portal.auth.consume', [
        'workspace_slug' => $workspace->slug,
        'client_slug' => $client->slug,
        'token' => $plain,
    ]))->assertRedirect()->assertSessionHas('error');
});

it('renders the portal dashboard for an authenticated portal user', function () {
    $workspace = Workspace::factory()->create();
    $client = Client::factory()->make();
    $client->workspace_id = $workspace->id;
    $client->save();

    $contact = ClientContact::factory()->create(['client_id' => $client->id]);

    session(['portal_contact_id' => $contact->id, 'portal_client_id' => $client->id]);

    $this->get(route('portal.dashboard', [
        'workspace_slug' => $workspace->slug,
        'client_slug' => $client->slug,
    ]))->assertOk()
        ->assertInertia(fn ($page) => $page->component('portal/Dashboard'));
});

it('redirects to portal login when no portal session exists', function () {
    $workspace = Workspace::factory()->create();
    $client = Client::factory()->make();
    $client->workspace_id = $workspace->id;
    $client->save();

    $this->get(route('portal.dashboard', [
        'workspace_slug' => $workspace->slug,
        'client_slug' => $client->slug,
    ]))->assertRedirect();
});

it('portal user cannot access internal dashboard', function () {
    $workspace = Workspace::factory()->create();
    $client = Client::factory()->make();
    $client->workspace_id = $workspace->id;
    $client->save();

    $contact = ClientContact::factory()->create(['client_id' => $client->id]);
    $portalUser = User::factory()->create();
    $portalUser->workspaces()->attach($workspace->id, ['role' => 'client']);
    setPermissionsTeamId($workspace->id);
    $portalUser->assignRole('client');

    $this->actingAs($portalUser)
        ->withSession(['active_workspace_id' => $workspace->id]);

    app()->instance(Workspace::class, $workspace);

    $this->get(route('dashboard'))->assertForbidden();
});
