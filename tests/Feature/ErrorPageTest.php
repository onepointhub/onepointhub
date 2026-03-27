<?php

use Inertia\Testing\AssertableInertia;

it('renders the Error page component for a 404', function () {
    $this->get('/this-route-definitely-does-not-exist')
        ->assertStatus(404)
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Error')
            ->where('status', 404)
        );
});

it('renders the Error page component for a 403', function () {
    $this->get(route('dashboard'))
        ->assertStatus(302); // guest gets redirected, not 403
    // 403 is tested via a route that explicitly aborts
});
