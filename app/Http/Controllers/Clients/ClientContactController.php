<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\StoreClientContactRequest;
use App\Models\Client;
use App\Models\ClientContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

class ClientContactController extends Controller
{
    /**
     * @throws Throwable
     */
    public function store(StoreClientContactRequest $request, Client $client): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($client, $data): void {
            if (! empty($data['is_primary'])) {
                $client->contacts()->update(['is_primary' => false]);
            }

            $client->contacts()->create($data);
        });

        return redirect()->route('clients.show', $client);
    }

    /**
     * @throws Throwable
     */
    public function update(StoreClientContactRequest $request, Client $client, ClientContact $contact): RedirectResponse
    {
        abort_unless($contact->client_id === $client->id, 404);

        $data = $request->validated();

        DB::transaction(function () use ($client, $contact, $data): void {
            if (! empty($data['is_primary'])) {
                $client->contacts()->where('id', '!=', $contact->id)->update(['is_primary' => false]);
            }

            $contact->update($data);
        });

        return redirect()->route('clients.show', $contact->client);
    }

    public function destroy(Client $client, ClientContact $contact): RedirectResponse
    {
        Gate::authorize('update-client');

        abort_unless($contact->client_id === $client->id, 404);

        $warPrimary = $contact->is_primary;

        $contact->delete();

        if ($warPrimary) {
            $next = $client->contacts()->orderBy('id')->first();
            $next?->update(['is_primary' => true]);
        }

        return redirect()->route('clients.show', $client);
    }
}
