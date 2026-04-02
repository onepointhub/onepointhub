<?php

namespace App\Modules\Core\Console\Commands;

use App\Support\ModuleRegistry;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('onepointhub:modules')]
#[Description('List all registered OnePointHub modules.')]
class ListModulesCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry): int
    {
        $modules = $registry->all();

        if (empty($modules)) {
            $this->line('No modules registered.');

            return self::SUCCESS;
        }

        $rows = array_map(
            fn ($name, object $provider) => [$name, $provider::class],
            array_keys($modules),
            $modules,
        );

        $this->table(['Module', 'Provider'], $rows);

        return self::SUCCESS;
    }
}
