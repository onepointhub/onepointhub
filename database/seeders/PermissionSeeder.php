<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    private array $permissions = [
        'manage-members',
        'manage-roles',
        'manage-workspace',
        'create-project',
        'update-project',
        'delete-project',
        'create-invoice',
        'update-invoice',
        'delete-invoice',
        'send-invoice',
        'view-client',
        'create-client',
        'update-client',
        'delete-client',
        'manage-portal',
        'view-activity-log',
    ];

    public function run(): void
    {
        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $owner = Role::firstOrCreate(['name' => 'owner']);
        $owner->givePermissionTo(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'manage-members',
            'manage-roles',
            'create-project',
            'update-project',
            'delete-project',
            'create-invoice',
            'update-invoice',
            'send-invoice',
            'view-client',
            'create-client',
            'update-client',
            'delete-client',
            'manage-portal',
            'view-activity-log',
        ]);

        $member = Role::firstOrCreate(['name' => 'member']);
        $member->givePermissionTo([
            'create-project',
            'update-project',
            'view-client',
            'create-client',
            'update-client',
        ]);

        Role::firstOrCreate(['name' => 'client']);
    }
}
