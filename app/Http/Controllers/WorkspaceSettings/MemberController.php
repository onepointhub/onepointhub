<?php

namespace App\Http\Controllers\WorkspaceSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkspaceSettings\UpdateMemberRoleRequest;
use App\Modules\Core\Enums\WorkspaceRole;
use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    public function index(): Response
    {
        $workspace = app(Workspace::class);

        $members = $workspace->members()
            ->get()
            ->map(fn (User $member) => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'avatar' => $member->avatar,
                'role' => $member->pivot->role,
            ]);

        return Inertia::render('workspace/settings/Members', [
            'members' => $members,
            'roles' => array_column(WorkspaceRole::cases(), 'value'),
            'canManageMembers' => Gate::check('manage-members'),
        ]);
    }

    public function update(UpdateMemberRoleRequest $request, User $user): RedirectResponse
    {
        $workspace = app(Workspace::class);

        abort_unless($workspace->members()->where('user_id', $user->id)->exists(), 404);

        $user->workspaces()->updateExistingPivot($workspace->id, [
            'role' => $request->validated('role'),
        ]);

        $user->syncRoles([$request->validated('role')]);

        return redirect()->route('workspace.members.index');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('manage-members');

        $workspace = app(Workspace::class);
        $pivotRole = $user->workspaces()->where('workspaces.id', $workspace->id)->first()?->pivot?->role;

        abort_if($pivotRole === null, 404);

        if ($pivotRole === WorkspaceRole::Owner->value) {
            $ownerCount = $workspace->members()->wherePivot('role', WorkspaceRole::Owner->value)->count();

            if ($ownerCount <= 1) {
                return back()->withErrors(['removal' => 'Cannot remove the sole owner. Transfer ownership first.']);
            }
        }

        // TODO(v0.4): trigger task/invoice reassignment prompt before detaching
        $user->workspaces()->detach($workspace->id);

        return redirect()->route('workspace.members.index');
    }
}
