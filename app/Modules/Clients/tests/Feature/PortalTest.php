<?php

use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\ClientContact;
use App\Modules\Clients\Models\PortalToken;
use App\Modules\Clients\Notifications\PortalMagicLinkNotification;
use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

it('sends a portal magic link to a primary contact', function () {
    actingAsWorkspaceMember('admin');

    $workspace = app(Workspace::class);
    $client = Client::factory()->create();
    $clientContact = ClientContact::factory()->primary()->create([
        'client_id' => $client->id,
        'email' => 'contact@example.com',
    ]);

    Notification::fake();

    $response = $this->post(route('clients.portal.send-link', $client));

    $response->assertRedirect();

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
    $plain = Str::random(64);
    $token = PortalToken::create([
        'client_id' => $client->id,
        'contact_id' => $contact->id,
        'token' => hash('sha256', $plain),
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
    $plain = Str::random(64);
    PortalToken::create([
        'client_id' => $client->id,
        'contact_id' => $contact->id,
        'token' => hash('sha256', $plain),
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
    $plain = Str::random(64);
    PortalToken::create([
        'client_id' => $client->id,
        'contact_id' => $contact->id,
        'token' => hash('sha256', $plain),
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
        ->assertInertia(fn ($page) => $page->component('Clients::portal/Dashboard'));
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

it('stores the portal token as a sha256 hash', function () {
    actingAsWorkspaceMember('admin');

    $workspace = app(Workspace::class);
    $client = Client::factory()->create();
    ClientContact::factory()->create([
        'client_id' => $client->id,
        'is_primary' => true,
        'email' => 'contact@example.com',
    ]);

    Notification::fake();

    $this->post(route('clients.portal.send-link', $client))->assertRedirect();

    $token = PortalToken::where('client_id', $client->id)->firstOrFail();

    // Token in DB must be a 64-char hex SHA-256 hash, not raw random bytes
    expect(strlen($token->token))->toBe(64)
        ->and(ctype_xdigit($token->token))->toBeTrue();
});

it('returns 403 when URL client slug does not match session client', function () {
    // Set up two clients in the same workspace
    $workspace = Workspace::factory()->create();
    $clientA = Client::factory()->create(['workspace_id' => $workspace->id]);
    $clientB = Client::factory()->create(['workspace_id' => $workspace->id]);
    $contact = ClientContact::factory()->create(['client_id' => $clientA->id]);

    // Simulate authenticated portal session for clientA
    session([
        'portal_client_id' => $clientA->id,
        'portal_contact_id' => $contact->id,
    ]);

    // Navigate to clientB's URL — should be rejected
    $this->get(route('portal.dashboard', [
        'workspace_slug' => $workspace->slug,
        'client_slug' => $clientB->slug,
    ]))->assertForbidden();
});
