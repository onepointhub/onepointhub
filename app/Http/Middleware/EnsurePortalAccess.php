<?php

namespace App\Http\Middleware;

use App\Models\Client;
use App\Models\ClientContact;
use App\Models\Scopes\WorkspaceScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $contactId = session('portal_contact_id');
        $clientId = session('portal_client_id');

        if (! $contactId || ! $clientId) {
            return redirect()->route('portal.login', [
                'workspace_slug' => $request->route('workspace_slug'),
                'client_slug' => $request->route('client_slug'),
            ]);
        }

        $contact = ClientContact::whereKey($contactId)->first();
        $client = Client::withoutGlobalScope(WorkspaceScope::class)->find($clientId);

        if (! $contact || ! $client || $contact->client_id !== $clientId) {
            session()->forget(['portal_contact_id', 'portal_client_id']);

            return redirect()->route('portal.login', [
                'workspace_slug' => $request->route('workspace_slug'),
                'client_slug' => $request->route('client_slug'),
            ]);
        }

        return $next($request);
    }
}
