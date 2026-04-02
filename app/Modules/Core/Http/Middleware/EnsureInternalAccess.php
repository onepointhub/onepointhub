<?php

namespace App\Modules\Core\Http\Middleware;

use App\Modules\Core\Enums\WorkspaceRole;
use App\Modules\Core\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInternalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // WorkspaceMiddleware runs first and binds the workspace with pivot data loaded.
        // Reading pivot->role here avoids a second database query on every request.
        $pivotRole = app(Workspace::class)->pivot?->role;

        if ($pivotRole === WorkspaceRole::Client->value) {
            abort(403, 'Client portal access only.');
        }

        return $next($request);
    }
}
