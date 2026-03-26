<?php

use App\Enums\NotificationType;
use App\Models\NotificationPreference;
use App\Models\User;

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
