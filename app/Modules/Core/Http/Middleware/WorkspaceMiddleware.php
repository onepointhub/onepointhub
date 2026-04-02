<?php

namespace App\Modules\Core\Http\Middleware;

use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkspaceMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User $user */
        $user = $request->user();

        // Resolve the workspace the authenticated user belongs to.
        // For v0.1 a user belongs to one active workspace; workspace-switching
        // will store the active workspace ID in the session.
        $workspaceId = session('active_workspace_id')
            ?? $user->workspaces()->first()?->id;

        if ($workspaceId === null) {
            // User is authenticated but has no workspace yet - send them
            // through onboarding instead of throwing.
            return redirect()->route('onboarding.workspace.create');
        }

        // Verify the user actually belongs to this workspace (prevents session
        // tampering where a user changes active_workspace_id to another workspace)
        $workspace = $user->workspaces()
            ->where('workspaces.id', $workspaceId)
            ->first();

        if ($workspace === null) {
            // Session contains a workspace ID that doesn't belong to this user.
            // Clear the stale session key and redirect to pick a valid one.
            session()->forget('active_workspace_id');

            return redirect()->route('onboarding.workspace.create');
        }

        // Bind the resolved Workspace into the container as a singleton for
        // the duration of this request. WorkspaceScope reads from here.
        app()->instance(Workspace::class, $workspace);

        // Also make it available as a request attribute for convenience.
        $request->attributes->set('workspace', $workspace);

        // Establish the Spatie Permission team context immediately so that any
        // role/permission check later in the request (Gate, middleware, controller)
        // uses the correct workspace scope. Without this, roles loaded before
        // Gate::before runs would be fetched with team_id = null and cached as empty.
        setPermissionsTeamId($workspace->id);

        return $next($request);
    }
}
