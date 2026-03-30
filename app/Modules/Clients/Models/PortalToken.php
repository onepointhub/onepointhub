<?php

namespace App\Modules\Clients\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable $expires_at
 */
#[Fillable(['client_id', 'contact_id', 'token', 'expires_at', 'consumed_at'])]
class PortalToken extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function isValid(): bool
    {
        return $this->consumed_at === null && $this->expires_at->isFuture();
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return BelongsTo<ClientContact, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(ClientContact::class);
    }
}
