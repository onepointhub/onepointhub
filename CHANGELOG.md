# Changelog

All notable changes to `OnePointHub` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/).

Semantic versioning applies from **v1.0.0** onwards. Pre-1.0 releases may contain
breaking changes between any two versions — see upgrade notes per version.

---

## [Unreleased]

### Added

- Projects module: database schema for projects, project_members, and milestones with models, enums, and factories (#30)
- Projects module: paginated project list with card/table toggle, status/client filters, and task progress bars (#31)
- Projects module: create and edit project forms with client select, budget, date range, colour picker, and team member management (#32)
- Projects module: task schema with sub-tasks, labels, priorities, and core CRUD endpoints (#33)
- Projects module: milestone create/edit/delete/complete with task progress tracking and overdue badge (#39)
- Projects module: task list with flat and milestone-grouped modes, inline status editing, and bulk actions (#35)
- Projects module: task detail page with inline editing, sub-tasks checklist, and deferred comments/activity (#36)
- Projects module: task comments with markdown body, @mention notifications, edit/delete own comments, and emoji reactions (#37)
- Projects module: time tracking with live timer (localStorage persistence), manual time entry, billable/non-billable split, and invoiced-entry locking (#38)
- Projects module: project templates with save-as-template, create-from-template, and 3 built-in templates (Web Design, Software Sprint, Monthly Retainer) (#41)
- Projects module: Gantt/timeline view with milestone diamonds, task bars, week/month/quarter zoom levels, and PNG export (#40)

### Changed

- fix: bulk-assign tasks now correctly writes to `assigned_to` column; assignee filter on task list now works
- fix: user avatars now correctly loaded on task detail and time log pages
- fix: project templates index page was unreachable due to route ordering conflict with `/{project}` wildcard
- security: re-enabled project authorization — `view-project` gate now enforced on show, board, task list, gantt, and time log endpoints
- security: added explicit `Gate::define` calls for all project and task gates
- security: added `view-project` and `delete-task` permissions to Projects module
- fix: admins can now access the Activity Log — `view-activity-log` gate is now defined and seeded
- security: workspace invitation tokens are now stored as SHA-256 hashes; plain token is only ever in the invitation email URL
- security: portal magic link tokens are now stored as SHA-256 hashes; plain token only ever appears in the email URL
- fix: invitation `accepted_at` timestamp is now updated inside the database transaction to prevent re-use on server crash
- security: added `verified` middleware to Clients and Projects route groups to enforce email verification
- security: `from-template` now validates that the template belongs to the current workspace (or is a built-in template)
- fix: portal dashboard now validates URL client slug matches the authenticated portal session to prevent navigation confusion
- security: import file path is now validated to prevent directory traversal outside the `imports/` directory
- perf: client CSV export now pre-loads all custom field values in a single query instead of one per client
- fix: client CSV export filename now correctly includes `.csv` extension
- fix: timer start now uses a database transaction with row locking to prevent duplicate running timers under concurrent requests
- refactor: extracted shared project validation rules into `ProjectRequestRules` trait to eliminate duplication between `StoreProjectRequest` and `UpdateProjectRequest`
- refactor: standardised Inertia component path in `TimeLogController` to use `Projects::TimeLog` module prefix
- fix: renamed `$warPrimary` variable to `$wasPrimary` in `ClientContactController`
- fix: standardised `ClientsServiceProvider::moduleName()` to return `'Clients'` (title-case)
- fix: home route now redirects to the dashboard instead of returning a plain text string

---

## [0.3.0] - 2026-03-30

### Added
- Clients module: `clients`, `client_contacts`, `client_addresses` tables with models, enums, factories, and `ClientsServiceProvider` (#21)
- Clients module: paginated client list with status/type filters, name and email search, and CSV export (#22)
- Clients module: create and edit forms with server-side validation, inline errors, and localStorage draft persistence (#23)
- Clients module: archive/restore, soft-delete with 30-day recovery window, and bulk archive from list view (#29)
- Clients module: add/edit/remove contacts, primary contact designation, and auto-promotion on primary deletion (#24)
- Clients module: workspace-scoped EAV custom fields (text, number, date, select, checkbox) displayed on client forms and included in CSV export (#26)
- Clients module: client portal scaffold at `/portal/{workspace-slug}/{client-slug}` with single-use magic link authentication and session-based portal access (#27)
- Clients module: CSV import with column mapping, 5-row preview, duplicate handling (skip/update/create), queued processing, and completion notification (#28)

---

## [0.2.0] - 2026-03-27

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
