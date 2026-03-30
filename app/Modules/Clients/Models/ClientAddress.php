<?php

namespace App\Modules\Clients\Models;

use App\Modules\Clients\database\factories\ClientAddressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['client_id', 'type', 'line1', 'line2', 'city', 'state', 'postcode', 'country'])]
#[UseFactory(ClientAddressFactory::class)]
class ClientAddress extends Model
{
    /** @use HasFactory<ClientAddressFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
