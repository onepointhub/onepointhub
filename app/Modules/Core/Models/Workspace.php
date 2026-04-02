<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\database\factories\WorkspaceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * @property ?WorkspaceUser $pivot
 * @property array<string, mixed>|null $settings
 */
#[Fillable(['name', 'slug', 'plan', 'currency', 'settings'])]
#[UseFactory(WorkspaceFactory::class)]
class Workspace extends Model
{
    /** @use HasFactory<WorkspaceFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        parent::boot();

        static::creating(function (Workspace $workspace) {
            if (empty($workspace->slug)) {
                $workspace->slug = self::generateUniqueSlug($workspace->name);
            }
        });
    }

    /**
     * @return BelongsToMany<User, $this, WorkspaceUser>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_user', 'workspace_id', 'user_id')
            ->using(WorkspaceUser::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    private static function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "$base-$counter";
            $counter++;
        }

        return $slug;
    }
}
