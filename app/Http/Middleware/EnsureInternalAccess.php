<?php

namespace App\Http\Middleware;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInternalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User $user */
        $user = $request->user();

        $pivotRole = $user
            ->workspaces()
            ->where('workspaces.id', app(Workspace::class)->id)
            ->first()?->pivot->role;

        if ($pivotRole === WorkspaceRole::Client->value) {
            abort(403, 'Client portal access only.');
        }

        return $next($request);
    }
}
