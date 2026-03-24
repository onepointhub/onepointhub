<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User')
        ->and($user->email)->toBe('test@example.com')
        ->and($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh())->not->toBeNull();
});

it('can upload profile photo', function () {
    $user = User::factory()->create();

    Storage::fake('public');

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'photo' => $file = UploadedFile::fake()->image('photo.jpg'),
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings/profile');

    $user->refresh();

    $this->assertNotNull($user->profile_photo_path);
    $this->assertTrue(Storage::disk('public')->exists($user->profile_photo_path));
});

it('can remove profile photo', function () {
    $user = User::factory()->create();

    Storage::fake('public');

    $response = $this->actingAs($user)->patch('/settings/profile', [
        'name' => $user->name,
        'email' => $user->email,
        'photo' => $file = UploadedFile::fake()->image('photo.jpg'),
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect('/settings/profile');

    $user->refresh();

    $this->assertNotNull($user->profile_photo_path);
    $this->assertTrue(Storage::disk('public')->exists($user->profile_photo_path));

    $oldPath = $user->profile_photo_path;

    $response = $this->actingAs($user)->delete('/settings/profile-photo');

    $response->assertSessionHasNoErrors()
        ->assertRedirect('/settings/profile');

    $user->refresh();
    $this->assertNull($user->profile_photo_path);
    $this->assertFalse(Storage::disk('public')->exists($oldPath));
});
