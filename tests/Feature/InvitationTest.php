<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Notifications\WorkspaceInvitationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

it('sends an invitation email to a new member', function () {
    Notification::fake();

    $workspace = Workspace::factory()->create();
    $owner = User::factory()->create();
    $owner->workspaces()->attach($workspace->id, ['role' => 'owner']);
    app()->instance(Workspace::class, $workspace);
    setPermissionsTeamId($workspace->id);
    $owner->assignRole('owner');

    $this->actingAs($owner)->post(route('workspace.invitations.store'), [
        'email' => 'newmember@example.com',
        'role' => 'member',
    ])->assertRedirect();

    $invitation = WorkspaceInvitation::where('email', 'newmember@example.com')->first();
    expect($invitation)->not->toBeNull();

    Notification::assertSentOnDemand(WorkspaceInvitationNotification::class);
});

it('accepts a valid invitation and attaches the user to the workspace', function () {
    $workspace = Workspace::factory()->create();
    $invitation = WorkspaceInvitation::create([
        'workspace_id' => $workspace->id,
        'email' => 'invitee@example.com',
        'role' => 'member',
        'token' => Str::random(64),
        'expires_at' => now()->addHours(48),
    ]);

    $user = User::factory()->create(['email' => 'invitee@example.com']);

    $this->actingAs($user)->get(route('invitations.accept', ['token' => $invitation->token]))
        ->assertRedirect(route('dashboard'));

    expect($user->workspaces()->where('workspaces.id', $workspace->id)->exists())->toBeTrue()
        ->and($invitation->fresh()->accepted_at)->not->toBeNull();
});

it('rejects an expired invitation', function () {
    $workspace = Workspace::factory()->create();
    $invitation = WorkspaceInvitation::create([
        'workspace_id' => $workspace->id,
        'email' => 'invitee@example.com',
        'role' => 'member',
        'token' => Str::random(64),
        'expires_at' => now()->subHour(),
    ]);

    $user = User::factory()->create(['email' => 'invitee@example.com']);

    $this->actingAs($user)->get(route('invitations.accept', ['token' => $invitation->token]))
        ->assertForbidden();
});

it('sends invitation email during onboarding', function () {
    Notification::fake();

    [$owner] = workspaceWithUser('owner');

    $this->actingAs($owner)->post(route('onboarding.invite.store'), [
        'emails' => 'colleague@example.com',
    ])->assertRedirect(route('onboarding.currency'));

    $invitation = WorkspaceInvitation::where('email', 'colleague@example.com')->first();
    expect($invitation)->not->toBeNull();
    Notification::assertSentOnDemand(WorkspaceInvitationNotification::class);
});
