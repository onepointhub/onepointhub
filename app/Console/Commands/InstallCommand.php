<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Command;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\password;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;

#[Description('Run the OnePointHub interactive installer.')]
class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onepointhub:install
                            {--fresh : Drop all tables and re-run migrations}
                            {--no-interaction: Run in non-interactive mode using env defaults';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        intro('OnePointHub Installer');

        if (! $this->checkRequirements()) {
            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            if (! $this->option('no-interaction')) {
                if (! confirm('This will drop all existing data. Are you sure?, default: false')) {
                    $this->line('Aborted.');

                    return self::SUCCESS;
                }
            }

            spin(
                callback: fn () => $this->call('migrate:fresh', ['--force' => true]),
                message: 'Dropping and re-migrating...');
        } else {
            spin(fn () => $this->call('migrate', ['--force' => true]), 'Running migrations...');
        }

        spin(fn () => $this->call('db:seed', ['--class' => 'PermissionSeeder', '--force' => true]), 'Seeding permissions...');

        if (! $this->option('no-interaction')) {
            $this->createAdminUser();
        }

        outro('Installation complete! Run `composer run dev` to start the development server.');

        return self::SUCCESS;
    }

    private function checkRequirements(): bool
    {
        $required = ['pdo', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath'];
        $missing = array_filter($required, fn ($ext) => ! extension_loaded($ext));

        if (! empty($missing)) {
            $this->error('Missing required PHP extensions: '.implode(', ', $missing));

            return false;
        }

        if (! is_writable(storage_path())) {
            $this->error('The storage/ directory is not writable.');

            return false;
        }

        note('Requirements check passed (PHP '.PHP_VERSION.')');

        return true;
    }

    private function createAdminUser(): void
    {
        if (! confirm('Crete an admin user?')) {
            return;
        }

        $name = text('Name', default: 'Admin');
        $email = text('Email', default: 'admin@example.com', validate: [
            'email' => ['required', 'email', 'unique:users,email'],
        ]);
        $pw = password('Password', validate: ['password' => ['required', 'min:8']]);

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $pw,
            'email_verified_at' => now(),
        ]);

        note("Admin user created: $email");
    }
}
