<?php

namespace Database\Seeders;

use App\Support\ModuleRegistry;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $rolePermissions = [];

        foreach (app(ModuleRegistry::class)->all() as $provider) {
            foreach ($provider->permissions() as $role => $permissions) {
                $rolePermissions[$role] = array_unique(array_merge(
                    $rolePermissions[$role] ?? [],
                    $permissions,
                ));
            }
        }

        // Create every permission that appears in any role's list
        $allPermissions = array_unique(array_merge(...array_values($rolePermissions)));

        foreach ($allPermissions as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        // Owner always receives every permission - declared once here, not per module.
        // The 'owner' key is skipped in the loop below, so this assignment is not overwritten.
        Role::firstOrCreate(['name' => 'owner'])->syncPermissions(Permission::all());

        // Each non-owner role receives exactly what its modules declared.
        foreach ($rolePermissions as $roleName => $permissions) {
            if ($roleName === 'owner') {
                continue; // already handled above - owner gets everything
            }
            Role::firstOrCreate(['name' => $roleName])->syncPermissions($permissions);
        }

        // Portal-only role - exists for middleware checks, no internal permissions
        Role::firstOrCreate(['name' => 'client']);
    }
}
