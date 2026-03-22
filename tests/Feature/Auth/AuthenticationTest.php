<?php

use App\Models\User;
use App\Models\Workspace;

it('renders the login page', function () {
    $this->get(route('login'))->assertOk();
});

it('logs in with valid credentials', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    $user->workspaces()->attach($workspace->id, ['role' => 'owner']);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function () {
    User::factory()->create(['email' => 'user@example.com']);

    $this->post(route('login.store'), [
        'email' => 'user@example.com',
        'password' => 'wrong-password',
    ])->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

it('logs out and invalidates session', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('logout'))->assertRedirect('/');
    $this->assertGuest();
});

it('rate limits login to 10 attempts per minute', function () {
    User::factory()->create(['email' => 'user@example.com']);

    for ($i = 0; $i < 10; $i++) {
        $this->post(route('login.store'), [
            'email' => 'user@example.com',
            'password' => 'wrong',
        ]);
    }

    $this->post(route('login.store'), [
        'email' => 'user@example.com',
        'password' => 'wrong',
    ])->assertStatus(429);
});
