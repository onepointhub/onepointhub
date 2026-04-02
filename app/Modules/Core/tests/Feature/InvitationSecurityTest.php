<?php

use App\Modules\Core\Models\WorkspaceInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

it('stores the invitation token as a sha256 hash', function () {
    actingAsWorkspaceMember('admin');

    $this->post(route('workspace.invitations.store'), [
        'email' => 'newmember@example.com',
        'role' => 'member',
    ])->assertRedirect();

    $invitation = WorkspaceInvitation::where('email', 'newmember@example.com')->firstOrFail();

    // Token stored in DB must be a 64-char hex string (SHA-256), not a plain random string
    expect(strlen($invitation->token))->toBe(64)
        ->and(ctype_xdigit($invitation->token))->toBeTrue();
});

it('accepts an invitation using the plain token from the URL', function () {
    [$user, $workspace] = actingAsWorkspaceMember('admin');

    // Create an invitation with a known plain token
    $plain = Str::random(64);
    $invitation = WorkspaceInvitation::factory()->create([
        'workspace_id' => $workspace->id,
        'token' => hash('sha256', $plain),
        'email' => Auth::user()->email,
        'expires_at' => now()->addHours(24),
    ]);

    $this->get(route('invitations.accept', $plain))->assertRedirect(route('dashboard'));

    $invitation->refresh();
    expect($invitation->accepted_at)->not()->toBeNull();
});
