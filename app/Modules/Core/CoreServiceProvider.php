<?php

namespace App\Modules\Core;

use App\Support\ModuleServiceProvider;

class CoreServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'core';
    }

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        parent::boot();
    }

    public function permissions(): array
    {
        return [
            'admin' => [
                'manage-members',
                'manage-roles',
                'manage-workspace',
                'view-activity-log',
            ],
        ];
    }
}
