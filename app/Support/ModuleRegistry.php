<?php

namespace App\Support;

use DirectoryIterator;
use Illuminate\Contracts\Foundation\Application;
use RuntimeException;

class ModuleRegistry
{
    /** @var array<string, ModuleServiceProvider> */
    private array $modules = [];

    public function __construct(private readonly Application $app) {}

    /**
     * Register a module. Throws if the name is already taken.
     */
    public function register(ModuleServiceProvider $provider): void
    {
        $name = $provider->moduleName();

        if (isset($this->modules[$name])) {
            throw new RuntimeException("Module [$name] is already registered.");
        }

        $this->modules[$name] = $provider;
    }

    /**
     * Auto-discover modules from the given directory.
     * Expects: app/Modules/{Name}/{Name}ServiceProvider.php
     */
    public function discover(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        foreach (new DirectoryIterator($path) as $item) {
            if ($item->isDot() || ! $item->isDir()) {
                continue;
            }

            $name = $item->getFilename();
            $class = "App\\Modules\\$name\\{$name}ServiceProvider";

            if (class_exists($class)) {
                /** @var ModuleServiceProvider $provider */
                $provider = new $class($this->app);

                $this->register($provider);
                $this->app->register($provider);
            }
        }
    }

    /**
     * @return array<string, ModuleServiceProvider>
     */
    public function all(): array
    {
        return $this->modules;
    }

    public function has(string $name): bool
    {
        return isset($this->modules[$name]);
    }
}
