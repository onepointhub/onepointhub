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
     * Role - permission assignments this module contributes to the seeder.
     *
     * Keys are role names ('admin', 'member', etc.).
     * The 'owner' role receives all permissions automatically - no module needs to declare it.
     * Use an explicit 'owner' key only for permissions that no other role should receive.
     *
     * @return array<string, list<string>>
     */
    public function permissions(): array
    {
        return [];
    }

    /**
     * Navigation items this module contributes to the sidebar.
     *
     * @return array<array{title: string, href: string, icon?: string}>
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
