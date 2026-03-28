<?php

namespace App\Models;

use App\Enums\ClientStatus;
use App\Enums\ClientType;
use App\Models\Concerns\BelongsToWorkspace;
use App\Models\Concerns\LogsActivity;
use App\Models\Scopes\WorkspaceScope;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'type', 'status', 'currency', 'website', 'vat_number', 'notes', 'settings'])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use BelongsToWorkspace, HasFactory, LogsActivity, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ClientType::class,
            'status' => ClientStatus::class,
            'settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        parent::boot();

        static::creating(function (Client $client) {
            if (empty($client->slug)) {
                $client->slug = self::generateSlug($client->name);
            }
        });
    }

    private static function generateSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $count = 0;

        $workspaceId = app(Workspace::class)->id;

        while (
            static::withoutGlobalScope(WorkspaceScope::class)
                ->where('workspace_id', $workspaceId)
                ->where('slug', $slug)
                ->exists()
        ) {
            $count++;
            $slug = "$base-$count";
        }

        return $slug;
    }

    /**
     * @return HasMany<ClientContact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
    }

    /**
     * @return HasMany<ClientAddress, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(ClientAddress::class);
    }

    public function primaryContact(): ?ClientContact
    {
        return $this->contacts()->where('is_primary', true)->first();
    }
}
