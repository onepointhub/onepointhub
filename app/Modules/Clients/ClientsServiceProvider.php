<?php

namespace App\Modules\Clients;

use App\Support\ModuleServiceProvider;

class ClientsServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'clients';
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
            'view-client',
            'create-client',
            'update-client',
            'delete-client',
            'manage-portal',
        ];
    }

    public function navigation(): array
    {
        return [
            [
                'label' => 'Clients',
                'route' => 'clients.index',
                'icon' => 'users',
            ],
        ];
    }
}
