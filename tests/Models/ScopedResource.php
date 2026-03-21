<?php

namespace Tests\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A test-only model for verifying BelongsToWorkspace / WorkspaceScope behavior
 * without coupling scope tests to any real production model.
 *
 * The corresponding table is created and destroyed inside WorkspaceScopeTest.
 */
class ScopedResource extends Model
{
    use BelongsToWorkspace;

    protected $table = 'scoped_resources';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['name', 'workspace_id'];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::ulid();
            }
        });
    }
}
