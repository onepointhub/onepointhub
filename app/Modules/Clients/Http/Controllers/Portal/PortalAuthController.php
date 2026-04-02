<?php

namespace App\Modules\Clients\Http\Controllers\Portal;

use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\PortalToken;
use App\Modules\Clients\Services\PortalLinkService;
use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Models\Scopes\WorkspaceScope;
use App\Modules\Core\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PortalAuthController extends Controller
{
    public function __construct(
        private readonly PortalLinkService $portalLinkService,
    ) {}

    /**
     * Generate and send a magic link to the client's primary contact.
     * Called by a workspace admin from the client detail page.
     */
    public function sendLink(Client $client): RedirectResponse
    {
        Gate::authorize('manage-portal');

        $email = $this->portalLinkService->send($client);

        return back()->with('success', "Portal link sent to $email.");
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
