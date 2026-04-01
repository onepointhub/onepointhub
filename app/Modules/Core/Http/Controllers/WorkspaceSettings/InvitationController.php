<?php

namespace App\Modules\Core\Http\Controllers\WorkspaceSettings;

use App\Modules\Core\Enums\WorkspaceRole;
use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Http\Requests\WorkspaceSettings\InviteMemberRequest;
use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;
use App\Modules\Core\Models\WorkspaceInvitation;
use App\Modules\Core\Notifications\MemberJoinedNotification;
use App\Modules\Core\Notifications\WorkspaceInvitationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class InvitationController extends Controller
{
    public function store(InviteMemberRequest $request): RedirectResponse
    {
        $workspace = app(Workspace::class);

        /** @var int $expiry */
        $expiry = config('workspace.invitation_expiry_hours');

        $plain = Str::random(64);

        $invitation = WorkspaceInvitation::create([
            'workspace_id' => $workspace->id,
            'email' => $request->validated('email'),
            'role' => $request->validated('role'),
            'token' => hash('sha256', $plain),
            'expires_at' => now()->addHours($expiry),
        ]);

        Notification::route('mail', $invitation->email)
            ->notify(new WorkspaceInvitationNotification($invitation, $plain));

        return redirect()->route('workspace.members.index')
            ->with('status', 'Invitation sent.');
    }

    /**
     * @throws Throwable
     */
    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = WorkspaceInvitation::where('token', hash('sha256', $token))->firstOrFail();

        if ($invitation->accepted_at !== null) {
            return redirect()->route('dashboard');
        }

        if ($invitation->isExpired()) {
            abort(403, 'This invitation has expired.');
        }

        /** @var User $user */
        $user = $request->user();

        if ($user->email !== $invitation->email) {
            abort(403, 'This invitation is not for your account.');
        }

        /** @var Workspace $workspace */
        $workspace = $invitation->workspace;

        if (! $user->workspaces()->where('workspaces.id', $invitation->workspace_id)->exists()) {
            DB::transaction(function () use ($invitation, $user): void {
                setPermissionsTeamId($invitation->workspace_id);
                $user->workspaces()->attach($invitation->workspace_id, ['role' => $invitation->role]);
                $user->assignRole($invitation->role);
                $invitation->update(['accepted_at' => now()]);
            });

            // Notify all owners and admins (excluding the new member)
            $notifiables = $workspace->members()
                ->wherePivotIn('role', [WorkspaceRole::Owner->value, WorkspaceRole::Admin->value])
                ->where('user_id', '!=', $user->id)
                ->get();

            Notification::send($notifiables, new MemberJoinedNotification($user, $workspace));
        } else {
            $invitation->update(['accepted_at' => now()]);
        }

        return redirect()->route('dashboard');
    }
}
