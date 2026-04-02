<?php

namespace App\Modules\Clients;

use App\Support\ModuleServiceProvider;

class ClientsServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Clients';
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
                'view-client',
                'create-client',
                'update-client',
                'delete-client',
                'manage-portal',
            ],
            'member' => [
                'view-client',
                'create-client',
                'update-client',
            ],
        ];
    }

    public function navigation(): array
    {
        return [
            [
                'title' => 'Clients',
                'href' => route('clients.index'),
                'icon' => 'users',
            ],
        ];
    }
}
