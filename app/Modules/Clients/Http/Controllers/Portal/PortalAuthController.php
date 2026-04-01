<?php

namespace App\Modules\Clients\Http\Controllers\Portal;

use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\PortalToken;
use App\Modules\Clients\Notifications\PortalMagicLinkNotification;
use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Models\Scopes\WorkspaceScope;
use App\Modules\Core\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PortalAuthController extends Controller
{
    /**
     * Generate and send a magic link to the client's primary contact.
     * Called by a workspace admin from the client detail page.
     */
    public function sendLink(Client $client): RedirectResponse
    {
        Gate::authorize('manage-portal');

        $contact = $client->contacts()->where('is_primary', true)->first()
            ?? $client->contacts()->first();

        abort_if($contact === null || $contact->email === null, 422, 'Client has no contact with an email address.');

        $workspace = app(Workspace::class);

        $plain = Str::random(64);

        PortalToken::create([
            'client_id' => $client->id,
            'contact_id' => $contact->id,
            'token' => hash('sha256', $plain),
            'expires_at' => now()->addHours(24),
        ]);

        $url = route('portal.auth.consume', [
            'workspace_slug' => $workspace->slug,
            'client_slug' => $client->slug,
            'token' => $plain,
        ]);

        (new AnonymousNotifiable)
            ->route('mail', $contact->email)
            ->notify(new PortalMagicLinkNotification($url, $client->name, $workspace->name));

        return back()->with('success', "Portal link sent to $contact->email.");
    }

    /**
     * Consume a magic link token and establish a portal session.
     */
    public function consume(string $workspaceSlug, string $clientSlug, string $token): RedirectResponse
    {
        $workspace = Workspace::where('slug', $workspaceSlug)->firstOrFail();

        $client = Client::withoutGlobalScope(WorkspaceScope::class)
            ->where('workspace_id', $workspace->id)
            ->where('slug', $clientSlug)
            ->firstOrFail();

        $record = PortalToken::where('token', hash('sha256', $token))
            ->where('client_id', $client->id)
            ->firstOrFail();

        if (! $record->isValid()) {
            return redirect()
                ->route('portal.login', ['workspace_slug' => $workspace->slug, 'client_slug' => $clientSlug])
                ->with('error', 'This link is invalid or has expired. Please request a new one.');
        }

        $record->update(['consumed_at' => now()]);

        session([
            'portal_contact_id' => $record->contact_id,
            'portal_client_id' => $client->id,
        ]);

        return redirect()->route('portal.dashboard', [
            'workspace_slug' => $workspace->slug,
            'client_slug' => $clientSlug,
        ]);
    }

    /**
     * Show the portal login page (used when the session has expired).
     */
    public function login(string $workspaceSlug, string $clientSlug): Response
    {
        return Inertia::render('portal/Login', [
            'workspaceSlug' => $workspaceSlug,
            'clientSlug' => $clientSlug,
        ]);
    }

    public function logout(string $workspaceSlug, string $clientSlug): RedirectResponse
    {
        session()->forget(['portal_contact_id', 'portal_client_id']);

        return redirect()->route('portal.login', compact('workspaceSlug', 'clientSlug'));
    }
}
