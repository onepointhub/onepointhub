# OnePointHub

A multi-tenant workspace SaaS platform built with Laravel 13, Inertia.js v2, and Vue 3.

## Requirements

- PHP 8.3+
- Node.js 20+
- Composer 2
- SQLite (default) or MySQL/PostgreSQL

## Getting Started

```bash
# Clone and install
git clone https://github.com/onepointhub/onepointhub.git
cd onepointhub

# Install dependencies, copy .env, generate key, migrate, and build assets
composer run setup
```

Then start the development server (Laravel, queue worker, Pail log viewer, and Vite run concurrently):

```bash
composer run dev
```

The application will be available at `http://localhost:8000`.

## Tech Stack

| Layer       | Technology                              |
|-------------|-----------------------------------------|
| Backend     | Laravel 13, PHP 8.3                     |
| Frontend    | Vue 3, TypeScript, Inertia.js v2        |
| Styling     | Tailwind CSS v4, shadcn-vue (Reka UI)   |
| Auth        | Laravel Fortify (email/password, 2FA)   |
| Permissions | Spatie Laravel Permission (team-scoped) |
| Routes (TS) | Laravel Wayfinder                       |
| Testing     | Pest 4                                  |
| Queue       | Database driver                         |

## Features (v0.1)

- **Workspaces** — create and switch between workspaces; session-scoped multi-tenancy with automatic query isolation
- **Onboarding** — three-step flow: name a workspace, invite a colleague, choose currency
- **Roles** — Owner · Admin · Member · Client, with Spatie team scoping
- **Members** — invite by email (token-based, 48h expiry), manage roles inline, remove members
- **Notifications** — in-app bell with unread count, mark-read actions, per-type email opt-out
- **Activity log** — immutable audit trail of created/updated/deleted events on workspace models
- **Dashboard** — member count, pending invitations, recent members, recent activity feed
- **Auth** — registration, login, email verification, password reset, 2FA (TOTP), profile photo

## Running Tests

```bash
php artisan test
```

## Code Quality

```bash
# Format PHP
vendor/bin/pint

# Static analysis
composer run analyse

# TypeScript type check
npm run types:check

# Full CI check (lint + types + static analysis + tests)
composer run ci:check
```

## Configuration

Key environment variables (see `.env.example` for the full list):

| Variable                  | Default                 | Description                                        |
|---------------------------|-------------------------|----------------------------------------------------|
| `APP_URL`                 | `http://localhost:8000` | Application URL                                    |
| `DB_CONNECTION`           | `sqlite`                | Database driver                                    |
| `QUEUE_CONNECTION`        | `database`              | Queue driver                                       |
| `MAIL_MAILER`             | `log`                   | Mail driver (`log` in local, `smtp` in production) |
| `INVITATION_EXPIRY_HOURS` | `48`                    | Hours before a workspace invitation expires        |

## Project Structure

```
app/
  Enums/              WorkspaceRole, NotificationType
  Http/
    Controllers/      DashboardController, OnboardingController, workspace settings…
    Middleware/        WorkspaceMiddleware, EnsureInternalAccess
    Requests/         FormRequests per controller action
  Models/
    Concerns/         BelongsToWorkspace, HasProfilePhoto, LogsActivity
    Scopes/           WorkspaceScope (automatic multi-tenant filtering)
  Notifications/      MemberJoinedNotification, WorkspaceInvitationNotification
  Providers/          AuthServiceProvider (Gate definitions)
resources/js/
  components/         NotificationBell, WorkspaceSwitcher, shadcn-vue ui/
  composables/        useInitials, useTimeAgo, useTwoFactorAuth
  layouts/            AppLayout, workspace/settings/Layout
  pages/              Dashboard, onboarding/*, settings/*, workspace/settings/*
  routes/             Wayfinder-generated TypeScript route helpers
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md).
