<?php

namespace App\Support;

use Illuminate\Support\ServiceProvider;
use ReflectionClass;

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

    /**
     * Autoload this module's migrations and routes if the directories exist.
     */
    public function boot(): void
    {
        $migrations = $this->moduleDirectory().'/database/migrations';
        $routes = $this->moduleDirectory().'/routes';

        if (is_dir($migrations)) {
            $this->loadMigrationsFrom($migrations);
        }

        if (is_dir($routes)) {
            $this->loadRoutesFrom($routes.'/web.php');
        }
    }

    /**
     * Returns the absolute path to this module's root directory
     * (the folder containing the ServiceProvider file).
     */
    protected function moduleDirectory(): string
    {
        /** @var string $path */
        $path = (new ReflectionClass($this))->getFileName();

        return dirname($path);
    }
}
