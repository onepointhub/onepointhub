<?php

use App\Modules\Core\Enums\WorkspaceRole;
use App\Modules\Core\Models\User;
use App\Modules\Core\Models\WorkspaceInvitation;
use App\Modules\Core\Notifications\MemberJoinedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

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

it('marks a single notification as read', function () {
    [$user] = workspaceWithUser('member');

    $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => MemberJoinedNotification::class,
        'data' => ['type' => 'member_joined', 'message' => 'Someone joined'],
        'read_at' => null,
    ]);

    $notification = $user->unreadNotifications()->first();

    $this->actingAs($user)
        ->patch(route('notifications.read', $notification->id))
        ->assertRedirect();

    expect($user->unreadNotifications()->count())->toBe(0);
});

it('marks all notifications as read', function () {
    [$user] = workspaceWithUser('member');

    foreach (range(1, 3) as $i) {
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => MemberJoinedNotification::class,
            'data' => ['type' => 'member_joined', 'message' => "Notification $i"],
            'read_at' => null,
        ]);
    }

    $this->actingAs($user)
        ->post(route('notifications.read-all'))
        ->assertRedirect();

    expect($user->unreadNotifications()->count())->toBe(0);
});
