<?php

use App\Modules\Core\CoreServiceProvider;
use App\Support\ModuleRegistry;

it('resolves ModuleRegistry from the container', function () {
    expect(app(ModuleRegistry::class))->toBeInstanceOf(ModuleRegistry::class);
});

it('discovers the Core module automatically', function () {
    $registry = app(ModuleRegistry::class);

    expect($registry->has('core'))->toBeTrue();
});

it('lists modules via artisan command', function () {
    $this->artisan('onepointhub:modules')
        ->assertSuccessful()
        ->expectsOutputToContain('core');
});

it('guards against registering a duplicate module name', function () {
    $registry = app(ModuleRegistry::class);

    expect(fn () => $registry->register(new CoreServiceProvider(app())))
        ->toThrow(RuntimeException::class);
});
