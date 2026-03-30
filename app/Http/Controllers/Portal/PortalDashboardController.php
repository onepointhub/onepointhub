<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\Scopes\WorkspaceScope;
use Inertia\Inertia;
use Inertia\Response;

class PortalDashboardController extends Controller
{
    public function index(string $workspaceSlug, string $clientSlug): Response
    {
        /** @var int $clientId */
        $clientId = session('portal_client_id');

        /** @var int $contactId */
        $contactId = session('portal_contact_id');

        $client = Client::withoutGlobalScope(WorkspaceScope::class)
            ->findOrFail($clientId);

        $contact = ClientContact::findOrFail($contactId);

        return Inertia::render('portal/Dashboard', [
            'client' => [
                'name' => $client->name,
                'slug' => $client->slug,
            ],
            'contact' => [
                'name' => $contact->name,
                'email' => $contact->email,
            ],
            'workspaceSlug' => $workspaceSlug,
            'clientSlug' => $clientSlug,
        ]);
    }
}
