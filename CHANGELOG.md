# Changelog

All notable changes to `OnePointHub` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/).

Semantic versioning applies from **v1.0.0** onwards. Pre-1.0 releases may contain
breaking changes between any two versions — see upgrade notes per version.

---

## [Unreleased]

### Added
- Module service provider contract (`ModuleServiceProvider`) and registry (`ModuleRegistry`) with auto-discovery from `app/Modules/`
- `CoreServiceProvider` stub proving the module contract
- `onepointhub:modules` Artisan command listing all registered modules
- `tests/Integration/` test suite for tests that hit real external services (no database refresh)
- Coverage report uploaded as a CI artifact on PHP 8.4 builds
- `DevSeeder` creating a demo workspace with three pre-built users (owner/admin/member at `*@demo.test`), idempotent — safe to run multiple times
- `CONTRIBUTING.md` with local setup, Docker setup, testing, code style, and module development guidelines
- `onepointhub:install` CLI installer using Laravel Prompts — checks PHP extension requirements, runs migrations, seeds permissions, creates first admin user; supports `--fresh` and `--no-interaction` flags
- Docker Compose development environment with PHP-FPM, nginx, MySQL 8, Redis, and Mailpit (mail UI at `:8025`)
- `Makefile` with `up`, `down`, `shell`, `install`, `test`, `migrate`, `seed`, `fresh`, and `logs` targets
- `.env.docker` with Docker-specific environment defaults
- `docs/development.md` covering both local PHP and Docker Compose setup options
- CaptainHook pre-commit hooks running Pint (auto-format) and PHPStan (level 9 analysis) before every commit
- Custom Inertia error pages (`Error.vue`) for 401, 403, 404, 419, 429, 500, and 503 responses with branded layout and home link
- Optional Sentry error tracking integration via `SENTRY_DSN` environment variable

---

## [0.1.0] — 2026-03-26

### Added

#### Authentication & User Accounts
- Email/password registration and login via Laravel Fortify
- Email verification, password reset, and password confirmation flows
- Two-factor authentication (TOTP) with QR code setup and recovery codes
- User profile management: name, email, timezone, and profile photo upload/removal
- Account deletion

#### Workspace Foundation
- Workspace creation with auto-generated unique slugs
- Multi-tenant data isolation: all workspace-scoped queries automatically filter by active workspace via `WorkspaceScope`
- Three-step onboarding flow: name workspace → invite a colleague → choose currency
- Session-based active workspace tracking
- Workspace switcher in the sidebar header for users who belong to multiple workspaces

#### Roles & Permissions
- Four-tier role system: Owner, Admin, Member, Client (Spatie Laravel Permission with team scoping)
- Owner gate: workspace owners automatically pass all ability checks
- `access-internal` gate: separates internal roles (Owner/Admin/Member) from the Client portal
- `manage-members` and `manage-workspace` permissions seeded per role

#### Member Management
- Members list with inline role editing
- Remove member from workspace (with sole-owner protection)
- Member roles update via `syncRoles` keeping Spatie and pivot in sync

#### Invitations
- Token-based email invitations with 48-hour expiry
- Invitation acceptance validates token, email match, and expiry
- Workspace owners and admins notified when a new member accepts
- Invitation sending during both onboarding and workspace settings

#### Notification System
- Database-backed in-app notifications with unread count badge
- Bell dropdown showing the five most recent notifications with relative timestamps
- Mark individual or all notifications as read
- Per-user email preference opt-out per notification type (`MemberJoined`)
- Notification preferences settings page

#### Activity / Audit Log
- Immutable `activity_logs` table recording created/updated/deleted events on workspace models
- `LogsActivity` trait applied to `WorkspaceInvitation`; skips logging in console/seeder/queue context
- Activity log page in workspace settings (visible to Owner and Admin only)
- Properties diff stored for update events; sensitive (hidden) model attributes excluded

#### Application Shell
- Authenticated app layout with top navigation bar (notifications bell, user menu)
- Sidebar navigation with workspace-aware breadcrumbs
- Dark/light mode toggle
- Workspace settings layout with nested navigation (Members, Activity Log)
- Dashboard home page with member count, pending invitation count, recent members list, and recent activity feed

### Fixed
- Null dereference in `NotificationPreference::emailEnabled()` when no preference row exists — changed `->first()->email_enabled` to `->first()?->email_enabled`
- Missing `Storage` facade import in `ProfileUpdateTest` causing photo upload tests to fail
- `Gate::define` callbacks using `$user->can()` instead of `$user->hasPermissionTo()` — removed fragile dependency on Spatie's internal `can()` override
- `access-internal` gate throwing `BindingResolutionException` when evaluated outside a workspace-scoped request — added `app()->bound()` guard
- `LogsActivity` logging raw model attributes including sensitive fields — properties are now filtered through `getHidden()`
- `WorkspaceInvitation::token` added to model's hidden attributes to prevent leakage into activity logs
- Invitation `accept()` not wrapped in a database transaction — `attach()` and `assignRole()` now execute atomically
- Duplicate pending invitations not blocked — `InviteMemberRequest` now rejects a second active invitation for the same email in the same workspace
- Existing workspace members could be re-invited — `InviteMemberRequest` now checks membership before creating an invitation
- Re-visiting an already-accepted invitation link would silently update `accepted_at` again — early return added
- `storeInvite` in `OnboardingController` used `filter_var` instead of a `FormRequest`, providing no user feedback on invalid email — replaced with `StoreOnboardingInviteRequest`
- Redundant `setPermissionsTeamId()` call in `MemberController::update()` removed — context already established by `WorkspaceMiddleware`
- Hardcoded 48-hour invitation expiry in two controllers centralised to `config/workspace.invitation_expiry_hours`
