<?php

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\WorkspaceInvitation;
use App\Notifications\MemberJoinedNotification;
use Illuminate\Support\Facades\Notification;

it('notifies workspace owners when an invitation is accepted', function () {
    Notification::fake();

    [$owner, $workspace] = workspaceWithUser('owner');

    $invitee = User::factory()->create();
    WorkspaceInvitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => $invitee->email,
        'role' => WorkspaceRole::Member->value,
        'token' => 'test-token-123',
        'expires_at' => now()->addHour(),
    ]);

    $this->actingAs($invitee)
        ->get(route('invitations.accept', ['token' => 'test-token-123']))
        ->assertRedirect(route('dashboard'));

    Notification::assertSentTo($owner, MemberJoinedNotification::class);
});

it('does not notify the new member themselves', function () {
    Notification::fake();

    [$owner, $workspace] = workspaceWithUser('owner');

    $invitee = User::factory()->create();
    WorkspaceInvitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => $invitee->email,
        'role' => WorkspaceRole::Member->value,
        'token' => 'test-token-456',
        'expires_at' => now()->addHour(),
    ]);

    $this->actingAs($invitee)
        ->get(route('invitations.accept', ['token' => 'test-token-456']))
        ->assertRedirect();

    Notification::assertNotSentTo($invitee, MemberJoinedNotification::class);
});
