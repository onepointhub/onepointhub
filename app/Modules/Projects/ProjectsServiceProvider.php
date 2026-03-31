<?php

namespace App\Modules\Projects;

use App\Support\ModuleServiceProvider;

class ProjectsServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Projects';
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
            'create-project',
            'update-project',
            'delete-project',
        ];
    }

    public function navigation(): array
    {
        return [
            [
                'label' => 'Projects',
                'route' => '/projects',
                'icon' => 'folder',
            ],
        ];
    }
}
