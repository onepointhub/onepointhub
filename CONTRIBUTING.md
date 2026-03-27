# Contributing to OnePointHub

## Prerequisites

- PHP 8.3+
- Composer 2
- Node.js 20+

## Local Setup

```bash
git clone https://github.com/onepointhub/onepointhub.git
cd onepointhub
composer run setup
```

Alternatively, use Docker — see [docs/development.md](docs/development.md).

## Development Server

```bash
composer run dev
```

Starts PHP, Vite, queue worker, and Pail log viewer concurrently.

## Demo Data

Seed a realistic development workspace with three pre-built users (owner, admin, member):

```bash
php artisan migrate:fresh --seed # runs PermissionSeeder only
php artisan db:seed --class=DevSeeder
```

Login at `http://localhost:8000/login` with `owner@demo.test` / `password`.

The DevSeeder is idempotent – safe to run multiple times.

## Running Tests

```bash
php artisan test
```

Run a specific test or filter:

```bash
php artisan test --filter="InvitationTest"
```

## Code Style

PHP is formatted with [Pint](https://laravel.com/docs/pint):

```bash
vendor/bin/pint
```

TypeScript/Vue is linted with ESLint:

```bash
npm run lint
```

## Static Analysis

```bash
composer run analyse
```

PHPStan runs at level 9. Fix all errors before opening a PR.

## Module Development

New feature modules live in `app/Modules/{Name}/`. Each module must provide a `{Name}ServiceProvider` that extends `App\Support\ModuleServiceProvider`. See `app/Modules/Core/CoreServiceProvider.php` for the canonical example.

List registered modules:

```bash
php artisan onepointhub:modules
```

## Branching

- `main` — stable, tagged releases
- `develop` — integration branch, all PRs merge here
- Feature branches: `feat/short-description`
- Bug branches: `fix/short-description`
