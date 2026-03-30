<?php

namespace App\Modules\Core\Models\Scopes;

use App\Modules\Core\Exceptions\WorkspaceNotResolvedException;
use App\Modules\Core\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class WorkspaceScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $workspace = app()->bound(Workspace::class)
            ? app(Workspace::class)
            : null;

        // If we're inside a console command or a job that has explicitly
        // set a workspace on the container, use it. If we're in an HTTP
        // request context without a workspace, that's a bug - throw immediately.
        if ($workspace === null) {
            if (app()->runningInConsole()) {
                // Console commands must opt in to scoping by binding a workspace
                // themselves. If no binding exists, return unscoped (safe default
                // for maintenance commands, migrations, etc.)
                return;
            }

            throw new WorkspaceNotResolvedException;
        }

        $builder->where($model->getTable().'.workspace_id', $workspace->id);
    }
}
