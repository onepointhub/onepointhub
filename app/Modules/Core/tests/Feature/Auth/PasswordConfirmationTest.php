<?php

use App\Modules\Core\Models\User;

it('renders the confirm password page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('password.confirm'))
        ->assertOk();
});

it('redirects guests to the login page', function () {
    $this->get(route('password.confirm'))
        ->assertRedirect(route('login'));
});

it('confirms password with correct password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('password.confirm.store'), ['password' => 'password'])
        ->assertRedirect();

    $this->assertAuthenticatedAs($user);
});

it('rejects incorrect password on confirmation', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('password.confirm.store'), ['password' => 'wrong-password'])
        ->assertSessionHasErrors('password');
});

it('requires a password to be provided', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('password.confirm.store'), ['password' => ''])
        ->assertSessionHasErrors('password');
});
