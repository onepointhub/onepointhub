<?php

use App\Modules\Core\Enums\NotificationType;
use App\Modules\Core\Models\NotificationPreference;
use App\Modules\Core\Models\User;
use Inertia\Testing\AssertableInertia;

it('defaults to email enabled when no preference exists', function () {
    $user = User::factory()->create();

    expect(NotificationPreference::emailEnabled($user, NotificationType::MemberJoined))->toBeTrue();
});

it('returns false when user has disabled email for a type', function () {
    $user = User::factory()->create();

    NotificationPreference::create([
        'user_id' => $user->id,
        'type' => NotificationType::MemberJoined->value,
        'email_enabled' => false,
    ]);

    expect(NotificationPreference::emailEnabled($user, NotificationType::MemberJoined))->toBeFalse();
});

it('returns true when user has explicitly enabled email for a type', function () {
    $user = User::factory()->create();

    NotificationPreference::create([
        'user_id' => $user->id,
        'type' => NotificationType::MemberJoined->value,
        'email_enabled' => true,
    ]);

    expect(NotificationPreference::emailEnabled($user, NotificationType::MemberJoined))->toBeTrue();
});

it('user can disable email for a notification type', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->patch(route('notifications.preferences.update', 'member_joined'), [
        'email_enabled' => false,
    ])->assertRedirect();

    expect(NotificationPreference::where('user_id', $user->id)
        ->where('type', 'member_joined')
        ->first()?->email_enabled)->toBeFalse();
});

it('user can re-enable email for a notification type', function () {
    $user = User::factory()->create();
    NotificationPreference::create([
        'user_id' => $user->id,
        'type' => 'member_joined',
        'email_enabled' => false,
    ]);
    $this->actingAs($user);

    $this->patch(route('notifications.preferences.update', 'member_joined'), [
        'email_enabled' => true,
    ])->assertRedirect();

    expect(NotificationPreference::where('user_id', $user->id)
        ->where('type', 'member_joined')
        ->first()?->email_enabled)->toBeTrue();
});

it('rejects an unknown notification type', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->patch(route('notifications.preferences.update', 'nonexistent_type'), [
        'email_enabled' => false,
    ])->assertNotFound();
});

it('renders the notification preferences settings page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('notifications.preferences.edit'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Core::settings/Notifications')
            ->has('types')
            ->has('preferences'));
});
