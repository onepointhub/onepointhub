<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

// ---------------------------------------------------------------------------
// Notice page
// ---------------------------------------------------------------------------

it('renders the email verification notice page', function () {
    $this->actingAs(User::factory()->unverified()->create())
        ->get(route('verification.notice'))
        ->assertOk();
});

it('redirects guests to the login page', function () {
    $this->get(route('verification.notice'))
        ->assertRedirect(route('login'));
});

// ---------------------------------------------------------------------------
// Resend verification email
// ---------------------------------------------------------------------------

it('resends the verification notification', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect();

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('does not resend the notification when already verified', function () {
    Notification::fake();

    $user = User::factory()->create(); // email_verified_at is set by default

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect();

    Notification::assertNotSentTo($user, VerifyEmail::class);
});

// ---------------------------------------------------------------------------
// Verify via signed URL
// ---------------------------------------------------------------------------

it('verifies the email with a valid signed URL', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->actingAs($user)
        ->get($verificationUrl)
        ->assertRedirect();

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('does not verify with a tampered URL', function () {
    $user = User::factory()->unverified()->create();

    $invalidUrl = route('verification.verify', [
        'id' => $user->id,
        'hash' => 'invalid-hash',
    ]);

    $this->actingAs($user)
        ->get($invalidUrl)
        ->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('does not verify with another user\'s hash', function () {
    $user = User::factory()->unverified()->create();
    $other = User::factory()->create();

    $spoofedUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($other->email)],
    );

    $this->actingAs($user)
        ->get($spoofedUrl)
        ->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});
