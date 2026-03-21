<?php

namespace App\Models\Concerns;

use App\Models\Scopes\WorkspaceScope;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToWorkspace
{
    public static function bootBelongsToWorkspace(): void
    {
        // Register the global scope on every query
        static::addGlobalScope(new WorkspaceScope);

        // Automatically stamp workspace_id on new models
        static::creating(function (self $model) {
            if (empty($model->workspace_id) && app()->bound(Workspace::class)) {
                $model->workspace_id = app(Workspace::class)->id;
            }
        });
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Escape the global scope for queries that need to span workspaces.
     * Use sparingly and only in admin/console contexts.
     */
    public static function withoutWorkspaceScope(): Builder
    {
        return static::withoutGlobalScope(WorkspaceScope::class);
    }

    /**
     * Scope to an explicit workspace bypassing the auto-resolved one.
     * Useful in queue jobs where you pass workspace_id as job data.
     */
    public function scopeForWorkspace(Builder $query, Workspace|string $workspace): Builder
    {
        $id = $workspace instanceof Workspace ? $workspace->id : $workspace;

        return $query->withoutGlobalScope(WorkspaceScope::class)
            ->where($this->getTable().'.workspace_id', $id);
    }
}
