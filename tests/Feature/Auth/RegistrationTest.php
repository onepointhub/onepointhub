<?php

use App\Models\User;

it('renders the registration page', function () {
    $this->get(route('register'))->assertOk();
});

it('registers a new user and redirects to onboarding', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertAuthenticated();

    expect(User::withoutGlobalScopes()->where('email', 'jane@example.com')->exists())->toBeTrue();
});

it('rejects duplicate email on registration', function () {
    User::factory()->create(['email' => 'jane@example.com']);

    $response = $this->post(route('register.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ]);

    $response->assertSessionHasErrors('email');
});
