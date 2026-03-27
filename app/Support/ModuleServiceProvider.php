<?php

namespace App\Support;

use Illuminate\Support\ServiceProvider;

abstract class ModuleServiceProvider extends ServiceProvider
{
    /**
     * The unique name of this module (lowercase, kebab-case).
     */
    abstract public function moduleName(): string;

    /**
     * Permission names this module seeds. Called by PermissionSeeder.
     *
     * @return array<string>
     */
    public function permissions(): array
    {
        return [];
    }

    /**
     * Navigation items this module contributes to the sidebar.
     *
     * @return array<array{label: string, route: string, icon?: string}>
     */
    public function navigation(): array
    {
        return [];
    }
}
