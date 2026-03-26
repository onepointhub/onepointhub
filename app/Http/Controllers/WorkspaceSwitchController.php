<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkspaceSwitchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $workspaceId = $request->integer('workspace_id');

        /** @var User $user */
        $user = $request->user();

        $workspace = $user->workspaces()
            ->where('workspaces.id', $workspaceId)
            ->first();

        if ($workspace === null) {
            abort(403);
        }

        session(['active_workspace_id' => $workspaceId]);

        return redirect()->route('dashboard');
    }
}
