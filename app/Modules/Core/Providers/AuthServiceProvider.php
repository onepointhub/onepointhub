<?php

namespace App\Modules\Core\Providers;

use App\Modules\Core\Enums\WorkspaceRole;
use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            // Owner always wins within their workspace
            $workspace = app()->bound(Workspace::class) ? app(Workspace::class) : null;

            if ($workspace) {
                setPermissionsTeamId($workspace->id);

                if ($user->hasRole('owner')) {
                    return true;
                }
            }
        });

        Gate::define('manage-members', fn (User $user) => $user->hasPermissionTo('manage-members'));

        Gate::define('manage-workspace', fn (User $user) => $user->hasPermissionTo('manage-workspace'));

        Gate::define('access-internal', function (User $user) {
            if (! app()->bound(Workspace::class)) {
                return false;
            }

            $workspace = app(Workspace::class);
            setPermissionsTeamId($workspace->id);

            return in_array(
                $user->workspaces()->where('workspaces.id', $workspace->id)->first()?->pivot->role,
                WorkspaceRole::internal()
            );
        });
    }
}
