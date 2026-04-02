<?php

namespace App\Modules\Clients\Services;

use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\PortalToken;
use App\Modules\Clients\Notifications\PortalMagicLinkNotification;
use App\Modules\Core\Models\Workspace;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Str;

class PortalLinkService
{
    /**
     * Generate a portal magic link and send it to the client's primary contact.
     * Returns the email address the link was sent to.
     */
    public function send(Client $client): string
    {
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

        /** @var string $email */
        $email = $contact->email;

        return $email;
    }
}
