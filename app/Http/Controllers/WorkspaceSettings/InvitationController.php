<?php

namespace App\Http\Controllers\WorkspaceSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkspaceSettings\InviteMemberRequest;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Notifications\WorkspaceInvitationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function store(InviteMemberRequest $request): RedirectResponse
    {
        $workspace = app(Workspace::class);

        $invitation = WorkspaceInvitation::create([
            'workspace_id' => $workspace->id,
            'email' => $request->validated('email'),
            'role' => $request->validated('role'),
            'token' => Str::random(64),
            'expires_at' => now()->addHours(48),
        ]);

        Notification::route('mail', $invitation->email)
            ->notify(new WorkspaceInvitationNotification($invitation));

        return redirect()->route('workspace.members.index')
            ->with('status', 'Invitation sent.');
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = WorkspaceInvitation::where('token', $token)->firstOrFail();

        if ($invitation->isExpired()) {
            abort(403, 'This invitation has expired.');
        }

        /** @var User $user */
        $user = $request->user();

        if ($user->email !== $invitation->email) {
            abort(403, 'This invitation is not for your account.');
        }

        if (! $user->workspaces()->where('workspaces.id', $invitation->workspace_id)->exists()) {
            setPermissionsTeamId($invitation->workspace_id);
            $user->workspaces()->attach($invitation->workspace_id, ['role' => $invitation->role]);
            $user->assignRole($invitation->role);
        }

        $invitation->update(['accepted_at' => now()]);

        return redirect()->route('dashboard');
    }
}
