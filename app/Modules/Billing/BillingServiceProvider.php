<?php

namespace App\Modules\Billing;

use App\Support\ModuleServiceProvider;

class BillingServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Billing';
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
                'create-invoice',
                'update-invoice',
                'delete-invoice',
                'send-invoice',
                'record-payment',
                'create-expense',
                'update-expense',
                'delete-expense',
                'view-billing-reports',
            ],
            'member' => [
                'create-invoice',
                'update-invoice',
                'create-expense',
                'update-expense',
            ],
        ];
    }

    public function navigation(): array
    {
        return [
            [
                'title' => 'Billing',
                'href' => route('billing.index'),
                'icon' => 'receipt',
            ],
        ];
    }
}
