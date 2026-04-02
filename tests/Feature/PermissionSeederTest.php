<?php

use App\Support\ModuleRegistry;
use Database\Seeders\PermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

// The seeder already runs in beforeEach for every feature test.
// These tests check the state it leaves behind.

it('creates every permission declared across all module service providers', function () {
    $expected = collect(app(ModuleRegistry::class)->all())
        ->flatMap(fn ($provider) => collect($provider->permissions())->flatten())
        ->unique()
        ->sort()
        ->values()
        ->all();

    $actual = Permission::pluck('name')->sort()->values()->all();

    expect($actual)->toEqual($expected);
});

it('grants owner all seeded permissions', function () {
    $allPermissions = Permission::pluck('name')->sort()->values()->all();
    $ownerPermissions = Role::findByName('owner')->permissions->pluck('name')->sort()->values()->all();

    expect($ownerPermissions)->toEqual($allPermissions);
});

it('creates the client role with no permissions', function () {
    expect(Role::findByName('client')->permissions)->toBeEmpty();
});

it('is idempotent — re-running does not change permission or role-assignment counts', function () {
    $permissionCount = Permission::count();
    $adminPermissionCount = Role::findByName('admin')->permissions()->count();

    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    $this->seed(PermissionSeeder::class);

    expect(Permission::count())->toBe($permissionCount)
        ->and(Role::findByName('admin')->permissions()->count())->toBe($adminPermissionCount);
});

it('grants admin exactly the permissions declared for it across all module service providers', function () {
    $expected = collect(app(ModuleRegistry::class)->all())
        ->flatMap(fn ($provider) => $provider->permissions()['admin'] ?? [])
        ->unique()
        ->sort()
        ->values()
        ->all();

    $actual = Role::findByName('admin')->permissions->pluck('name')->sort()->values()->all();

    expect($actual)->toEqual($expected);
});

it('grants member exactly the permissions declared for it across all module service providers', function () {
    $expected = collect(app(ModuleRegistry::class)->all())
        ->flatMap(fn ($provider) => $provider->permissions()['member'] ?? [])
        ->unique()
        ->sort()
        ->values()
        ->all();

    $actual = Role::findByName('member')->permissions->pluck('name')->sort()->values()->all();

    expect($actual)->toEqual($expected);
});
