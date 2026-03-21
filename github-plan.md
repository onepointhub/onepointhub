# OnePointHub — GitHub Issues & Milestone Plan

## Label System

### Type
| Label            | Color     | Description                       |
|------------------|-----------|-----------------------------------|
| `type: feature`  | `#0075ca` | New functionality                 |
| `type: bug`      | `#d73a4a` | Something isn't working           |
| `type: chore`    | `#e4e669` | Maintenance, refactoring, tooling |
| `type: docs`     | `#0075ca` | Documentation                     |
| `type: security` | `#b60205` | Security concern                  |
| `type: dx`       | `#cfd3d7` | Developer experience              |

### Priority
| Label              | Color     | Description                   |
|--------------------|-----------|-------------------------------|
| `priority: high`   | `#b60205` | Must ship in this milestone   |
| `priority: medium` | `#e99695` | Should ship in this milestone |
| `priority: low`    | `#f9d0c4` | Nice to have                  |

### Module
| Label               | Color     | Description                       |
|---------------------|-----------|-----------------------------------|
| `module: core`      | `#1d76db` | Foundation, auth, shared services |
| `module: clients`   | `#0e8a16` | Clients & contacts module         |
| `module: projects`  | `#5319e7` | Projects, tasks, boards           |
| `module: billing`   | `#e4e669` | Invoices, time, expenses          |
| `module: comms`     | `#d93f0b` | Shared inbox, threads             |
| `module: docs`      | `#0075ca` | Wiki, proposals, file manager     |
| `module: api`       | `#cfd3d7` | REST API & webhooks               |
| `module: installer` | `#bfd4f2` | CLI installer & deployment        |

### Effort
| Label            | Color     | Description                 |
|------------------|-----------|-----------------------------|
| `effort: small`  | `#c2e0c6` | < 4 hours                   |
| `effort: medium` | `#fef2c0` | 4–12 hours                  |
| `effort: large`  | `#f9d0c4` | 1–3 days                    |
| `effort: xl`     | `#e99695` | 3+ days, consider splitting |

---

## Milestones

| #    | Name                 | Goal                                           |
|------|----------------------|------------------------------------------------|
| v0.1 | Foundation           | Skeleton, auth, workspaces, shared services    |
| v0.2 | Developer Experience | Module system, installer, testing harness      |
| v0.3 | Clients Module       | Full clients & contacts CRUD + portal scaffold |
| v0.4 | Projects Module      | Tasks, boards, milestones, time logging        |
| v0.5 | Billing Module       | Invoices, expenses, PDF export                 |
| v0.6 | Comms Module         | Shared inbox, threads, notifications           |
| v0.7 | Docs Module          | Wiki, proposals, file manager                  |
| v0.8 | API & Webhooks       | REST API, webhook system, API tokens           |
| v0.9 | Polish & Hardening   | Performance, security audit, accessibility     |
| v1.0 | Launch               | Stable release, docs site, Docker Hub image    |

---

## Issues

---

### Milestone v0.1 — Foundation

- [x] #1 — Initialise Laravel project with sane defaults

**Labels:** `type: chore` `module: core` `priority: high` `effort: small`

Set up a fresh Laravel 13 project with the following configuration locked in from day one:
- PHP 8.2+ requirement in `composer.json`
- Strict types are enabled globally via Rector or manual pass
- Pint configured with the project's code style rules
- `.editorconfig` and `.gitattributes` committed
- `APP_TIMEZONE=UTC` enforced in config
- Remove default Laravel boilerplate routes and views that won't be used

**Acceptance criteria:**
- `composer install && php artisan key:generate` succeeds on a clean clone
- `./vendor/bin/pint --test` passes with zero violations

---

- [x] #2 — Configure multi-database support (MySQL + SQLite)

**Labels:** `type: chore` `module: core` `priority: high` `effort: small`

The app must run on SQLite for local dev/testing with zero external dependencies, and on MySQL/MariaDB for production.

- Detect `DB_CONNECTION` and load the appropriate connection config
- Ensure all migrations use portable column types (no MySQL-specific types)
- Document the two modes in `README.md`
- Add a `DB_CONNECTION=sqlite` path to `phpunit.xml`

**Acceptance criteria:**
- Test suite passes against both SQLite (CI) and MySQL (local via Docker Compose)

---

- [x] #3 — Implement a workspace (tenant) model and scoping

**Labels:** `type: feature` `module: core` `priority: high` `effort: large`

Every resource in the system belongs to a `Workspace`. Implement workspace-level scoping without a third-party tenancy package.

- `workspaces` table: `id`, `name`, `slug`, `plan`, `settings` (JSON), `timestamps`
- `workspace_id` foreign key on all resource tables
- `BelongsToWorkspace` trait that applies a global scope
- `WorkspaceMiddleware` that resolves the current workspace from the authenticated user and binds it to the container
- Prevent cross-workspace data leakage in a dedicated test suite

**Acceptance criteria:**
- A query on any scoped model without an active workspace throws a `WorkspaceNotResolvedException`
- Unit tests cover scope injection and isolation

---

- [ ] #4 — Authentication: registration, login, logout

**Labels:** `type: feature` `module: core` `priority: high` `effort: medium`

Use Laravel Fortify as a scaffolding base, then strip it down to only what's needed.

- Email/password registration with email verification
- Login with remember-me
- Logout (session + CSRF invalidation)
- Password reset via email
- Rate-limit login attempts (10/minute per IP)
- Remove Breeze's Inertia/React scaffolding — keep only the auth logic and Blade views

**Acceptance criteria:**
- Full auth flow works end-to-end in the browser
- Feature tests cover happy path and rate-limiting

---

- [ ] #5 — User profile and account settings

**Labels:** `type: feature` `module: core` `priority: medium` `effort: small`

- Update name, email, avatar (stored via `spatie/laravel-medialibrary` or local disk)
- Change password flow with current-password confirmation
- Timezone preference (used for date display throughout the app)
- Delete an account with workspace ownership transfer guard

**Acceptance criteria:**
- Users can update all fields and see changes reflected immediately
- Deleting an account that owns a workspace requires reassigning ownership first

---

- [ ] #6 — Workspace creation and onboarding flow

**Labels:** `type: feature` `module: core` `priority: high` `effort: medium`

After registration, new users are guided through a minimal onboarding:

1. Name your workspace
2. Invite team members (optional, skippable)
3. Choose a primary currency for billing

- `workspaces` slug must be unique and URL-safe
- First user in a workspace is automatically the `owner` role
- Onboarding state stored on the user record; a skip link is always visible

**Acceptance criteria:**
- New user registration always lands on onboarding if the workspace is not yet configured
- Onboarding can be completed in under 60 seconds

---

- [ ] #7 — Role & permission system

**Labels:** `type: feature` `module: core` `priority: high` `effort: large`

Use `spatie/laravel-permission` scoped to workspaces.

Built-in roles:
- `owner` — full access, billing, delete workspace
- `admin` — manage members and all modules
- `member` — standard access to assigned projects
- `client` — read-only portal access (no internal visibility)

- Gate definitions for every major action (e.g. `create-invoice`, `manage-members`)
- Blade directive `@can` used consistently in views
- Role management UI in workspace settings

**Acceptance criteria:**
- A `client` role user cannot access any internal route
- Tests cover role assignment and permission denial

---

- [ ] #8 — Workspace member management

**Labels:** `type: feature` `module: core` `priority: high` `effort: medium`

- Invite by email (generates a signed invitation link, expires in 48 hours)
- Accept/decline invitation flow
- Change member role
- Remove member (with resource reassignment prompt)
- Workspace member list with role badges

**Acceptance criteria:**
- Invitation email is sent and the link works correctly
- Removing a member does not orphan their assigned tasks/issues

---

- [ ] #9 — Global notification system

**Labels:** `type: feature` `module: core` `priority: medium` `effort: large`

- `Notification` model: `id`, `workspace_id`, `user_id`, `type`, `data` (JSON), `read_at`, `timestamps`
- Laravel's notification system piped to database + email channels
- Bell icon in the nav with unread count badge
- Notification dropdown (last 10, mark all read)
- Notification preferences per user (which events trigger email)

**Acceptance criteria:**
- Notifications are created for key events (a task assigned, invoice sent, mention)
- User can disable email notifications per type

---

- [ ] #10 — Activity / audit log

**Labels:** `type: feature` `module: core` `priority: medium` `effort: medium`

- `activity_log` table tracking who did what to which resource
- `LogsActivity` trait applied to all major models
- Activity feed UI in sidebar/dashboard (last 20 events per workspace)
- Filterable by actor, resource type, and date range

**Acceptance criteria:**
- All CRUD operations on core resources generate an activity record
- Activity log is read-only and cannot be modified

---

- [ ] #11 — Application layout and navigation shell

**Labels:** `type: feature` `module: core` `priority: high` `effort: medium`

- Persistent sidebar with module links, workspace switcher, user avatar
- Top bar with global search trigger, notification bell, quick-create button
- Responsive: sidebar collapses to icon-only at < 1024 px, drawer on mobile
- Dark mode toggle persisted to `localStorage` and synced to user preferences
- Active route highlighting

**Acceptance criteria:**
- Layout renders correctly at 375 px, 768 px, and 1280 px
- Dark mode persists across page loads

---

- [ ] #12 — Dashboard / home page

**Labels:** `type: feature` `module: core` `priority: medium` `effort: medium`

Workspace home screen showing a personalized summary:

- My open tasks (count and list)
- Recent activity feed
- Upcoming invoice due dates
- Quick-create actions (new task, new invoice, new client)
- Empty state for brand-new workspaces guiding to first action

**Acceptance criteria:**
- Dashboard loads in under 300 ms on SQLite with seed data
- All widgets degrade gracefully when modules have no data

---

### Milestone v0.2 — Developer Experience

#### #13 — Module service provider contract
**Labels:** `type: dx` `module: core` `priority: high` `effort: large`

Define the interface every OnePointHub module must implement:

- `ModuleServiceProvider` abstract base class with `register()` and `boot()` hooks
- Modules declare: routes, migrations, menu items, permissions, event listeners
- `ModuleRegistry` singleton that discovers and loads modules
- Convention-over-config: modules live in `app/Modules/{Name}/`
- Guard against duplicate module registration

**Acceptance criteria:**
- A stub module can be registered with zero changes to core files
- `php artisan onepointHub:modules` lists all registered modules

---

#### #14 — Pest testing harness
**Labels:** `type: dx` `module: core` `priority: high` `effort: medium`

- Pest 3 configured as the sole test runner
- `TestCase` base class with workspace + user factory helpers
- `actingAsWorkspaceMember($role)` helper
- Factories for all core models
- GitHub Actions CI workflow running tests against SQLite
- Separate `Unit`, `Feature`, and `Integration` test suites

**Acceptance criteria:**
- `./vendor/bin/pest` passes on a fresh clone
- CI runs on every PR and reports coverage

---

#### #15 — Database seeders and dev fixtures
**Labels:** `type: dx` `module: core` `priority: medium` `effort: medium`

- `DevSeeder` that creates a realistic workspace with demo data across all modules
- Separate seeders per module, orchestrated by `DatabaseSeeder`
- Seeder creates: 1 workspace, 3 users (owner/admin/member), 5 clients, 10 projects, 20+ tasks
- `php artisan db:seed --class=DevSeeder` documented in `CONTRIBUTING.md`

**Acceptance criteria:**
- After seeding, every module has enough data to demo without manual input
- Seeder is idempotent (can be run multiple times safely)

---

#### #16 — CLI installer: `php artisan onepointHub:install`
**Labels:** `type: feature` `module: installer` `priority: high` `effort: large`

Interactive installer for new deployments:

1. Check system requirements (PHP version, extensions, writable paths)
2. Configure `.env` interactively (DB, mail, storage)
3. Run migrations
4. Run optional seeders
5. Create first admin user
6. Generate `APP_KEY`
7. Output a summary with next steps

- Must be re-runnable with a `--fresh` flag
- Works in non-interactive mode for CI: `--no-interaction --db-url=...`

**Acceptance criteria:**
- On a fresh VPS with PHP 8.2 and SQLite, the installer completes and the app is accessible
- Running installer twice without `--fresh` does not destroy data

---

#### #17 — Docker Compose development environment
**Labels:** `type: dx` `module: installer` `priority: high` `effort: medium`

- `docker-compose.yml` with: `app` (PHP-FPM), `nginx`, `mysql`, `redis`, `mailpit`
- `.env.docker` pre-filled with sensible defaults
- `make up`, `make test`, `make shell` convenience targets in `Makefile`
- Volume mounts for hot-reload during development
- Health checks on all services

**Acceptance criteria:**
- `docker compose up -d && make install` produces a running application with no manual steps
- Documented in `docs/development.md`

---

#### #18 — Code style, static analysis, and git hooks
**Labels:** `type: dx` `module: core` `priority: medium` `effort: small`

- Laravel Pint with project ruleset committed
- PHPStan at level 6 with a `phpstan.neon` baseline
- `husky` (or `captainhook`) running Pint + PHPStan on pre-commit
- GitHub Actions check on every PR (fail on Pint violations or PHPStan errors)

**Acceptance criteria:**
- `./vendor/bin/pint --test` and `./vendor/bin/phpstan analyse` both pass on CI
- Pre-commit hook catches a Pint violation before it reaches the remote

---

#### #19 — Error handling and logging configuration
**Labels:** `type: chore` `module: core` `priority: medium` `effort: small`

- Custom exception handler with user-friendly error pages (404, 403, 500)
- Log channels: `daily` for production, `single` for local
- Optional Sentry integration via `LOG_SENTRY_DSN` env var
- Validation errors surfaced as inline field errors in Livewire forms
- Never expose stack traces to non-authenticated users

**Acceptance criteria:**
- A 404 renders the branded error page, not a raw Laravel error
- Sentry receives exception events when `LOG_SENTRY_DSN` is set

---

#### #20 — Global search
**Labels:** `type: feature` `module: core` `priority: low` `effort: large`

- Keyboard shortcut (`Cmd/Ctrl + K`) opens command palette
- Searches across: clients, projects, tasks, invoices, docs
- Uses Laravel Scout with the `database` driver by default (zero-dependency)
- TNTSearch driver supported as an opt-in for better full-text search
- Results grouped by type with keyboard navigation

**Acceptance criteria:**
- Search returns results within 200ms on the dev seeder dataset
- Results respect workspace scoping (cannot find other workspaces' data)

---

### Milestone v0.3 — Clients Module

#### #21 — Clients database schema and model
**Labels:** `type: feature` `module: clients` `priority: high` `effort: medium`

Tables:
- `clients`: `id`, `workspace_id`, `name`, `type` (individual/company), `status` (active/archived), `currency`, `notes`, `settings` (JSON), `timestamps`
- `client_contacts`: `id`, `client_id`, `name`, `email`, `phone`, `role`, `is_primary`, `timestamps`
- `client_addresses`: `id`, `client_id`, `type`, `line1`, `line2`, `city`, `country`, `postcode`

**Acceptance criteria:**
- Migrations run cleanly on both SQLite and MySQL
- Model factories produce realistic data

---

#### #22 — Client list view
**Labels:** `type: feature` `module: clients` `priority: high` `effort: medium`

- Paginated table (25 per page) with sortable columns: name, status, created date
- Filter by status (active/archived) and type (individual/company)
- Search by name or contact email
- Bulk actions: archive selected, export selected (CSV)
- Empty state with a "Add your first client" CTA

**Acceptance criteria:**
- List renders with 200 seeded clients without performance degradation
- Filters and search work in combination

---

#### #23 — Client create & edit form
**Labels:** `type: feature` `module: clients` `priority: high` `effort: medium`

- Livewire form with real-time validation
- Fields: name, type, status, currency, website, VAT/tax number, notes
- Save drafts to session if the user navigates away
- Inline error messages
- Auto-generate a client slug used in portal URLs

**Acceptance criteria:**
- All fields validate correctly before submission
- Form is accessible (labels, ARIA, keyboard navigation)

---

#### #24 — Contact management within a client
**Labels:** `type: feature` `module: clients` `priority: high` `effort: medium`

- Add/edit/remove contacts on the client detail page
- Set a primary contact (shown in project and invoice headers)
- Contact avatar (initials fallback)
- Send a direct email to a contact without leaving the app (opens mailto or in-app compose)

**Acceptance criteria:**
- A client can have unlimited contacts
- Deleting a contact that is referenced by invoices shows a warning

---

#### #25 — Client detail page
**Labels:** `type: feature` `module: clients` `priority: high` `effort: medium`

Tabbed layout:
- **Overview** — key stats (open projects, unpaid invoices, total billed), primary contact card, notes
- **Projects** — list of projects linked to this client
- **Invoices** — invoice history with status badges
- **Files** — uploaded documents (contracts, briefs)
- **Activity** — audit trail for this client

**Acceptance criteria:**
- Each tab loads data independently (lazy Livewire)
- Stats are accurate against seeder data

---

#### #26 — Custom fields for clients
**Labels:** `type: feature` `module: clients` `priority: medium` `effort: large`

Allow workspace admins to define custom fields on the client model:
- Field types: text, number, date, select (with options), checkbox
- Fields are per-workspace, stored in a `custom_field_definitions` table
- Values stored in `custom_field_values` (EAV pattern)
- Custom fields appear in the client create/edit form and detail page
- Exportable in CSV export

**Acceptance criteria:**
- Admin can create a custom field and have it appear on all client forms
- Custom field values persist correctly for all field types

---

#### #27 — Client portal scaffold
**Labels:** `type: feature` `module: clients` `priority: medium` `effort: large`

A separate, minimal UI accessible to contacts with the `client` role:
- Unique portal URL per client: `/portal/{workspace-slug}/{client-slug}`
- Login via magic link sent to contact email (no password required)
- Portal shows: active projects (status only), invoices (view + download PDF), shared files
- Separate layout with the client's workspace branding (logo, accent colour)

**Acceptance criteria:**
- A portal user cannot access any internal workspace routes
- Magic link expires after 24 hours and is single-use

---

#### #28 — Client import from CSV
**Labels:** `type: feature` `module: clients` `priority: low` `effort: medium`

- Upload a CSV with header mapping UI
- Map CSV columns to client fields (name, email, phone, etc.)
- Preview first 5 rows before importing
- Duplicate detection by email: skip, update, or create new
- Import runs as a queued job; user notified on completion

**Acceptance criteria:**
- A 500-row CSV imports without timeout
- Duplicate handling works for all three modes

---

#### #29 — Client archiving and deletion
**Labels:** `type: feature` `module: clients` `priority: medium` `effort: small`

- Archive: client is hidden from default lists but all data is preserved
- Delete: soft-delete with a 30-day recovery window, then permanent
- Cannot delete a client with open invoices (guard + UI message)
- Bulk archive from list view

**Acceptance criteria:**
- Archived clients do not appear in project/invoice client selectors by default
- Recovery window restoration works correctly

---

### Milestone v0.4 — Projects Module

#### #30 — Projects database schema and model
**Labels:** `type: feature` `module: projects` `priority: high` `effort: medium`

Tables:
- `projects`: `id`, `workspace_id`, `client_id` (nullable), `name`, `description`, `status`, `type` (fixed/hourly/retainer), `budget`, `budget_type` (hours/money), `colour`, `starts_at`, `ends_at`, `timestamps`
- `project_members`: `project_id`, `user_id`, `role` (lead/member), `hourly_rate`
- `milestones`: `id`, `project_id`, `name`, `due_at`, `completed_at`

**Acceptance criteria:**
- Migrations clean on SQLite and MySQL
- A project can exist without a client (internal projects)

---

#### #31 — Project list view
**Labels:** `type: feature` `module: projects` `priority: high` `effort: medium`

- Card grid and table toggle
- Filter by status, client, assigned member
- Sort by name, deadline, last activity
- Colour-coded status badges (active, on hold, completed, archived)
- Project progress bar (tasks completed / total tasks)

**Acceptance criteria:**
- List view handles 100+ projects without layout issues
- Progress bar is accurate

---

#### #32 — Project create & edit form
**Labels:** `type: feature` `module: projects` `priority: high` `effort: medium`

- Name, client (searchable select), description, project type, colour picker
- Budget: fixed amount or hourly cap
- Date range picker (start/end)
- Add team members with role and optional rate override
- Template support: "start from template" option (see #41)

**Acceptance criteria:**
- Form validates budget fields based on project type
- Members can be added/removed without saving the whole form

---

#### #33 — Task model and core CRUD
**Labels:** `type: feature` `module: projects` `priority: high` `effort: medium`

Tables:
- `tasks`: `id`, `workspace_id`, `project_id`, `milestone_id` (nullable), `parent_id` (nullable, for sub-tasks), `assigned_to`, `created_by`, `title`, `description`, `status`, `priority`, `position`, `due_at`, `completed_at`, `estimated_hours`, `timestamps`
- `task_labels`: `id`, `workspace_id`, `name`, `colour`
- `task_label_task`: pivot

**Acceptance criteria:**
- Sub-tasks supported up to 2 levels deep
- Position field enables manual ordering within a status column

---

#### #34 — Kanban board view
**Labels:** `type: feature` `module: projects` `priority: high` `effort: xl`

- Columns map to task statuses: Backlog, To Do, In Progress, In Review, Done
- Drag-and-drop cards between columns (SortableJS)
- Card shows: title, assignee avatar, due date, priority badge, sub-task count
- Lazy-load card details on click (slide-over panel)
- Custom column names per project
- WIP limits (optional, per column)

**Acceptance criteria:**
- Drag-and-drop persists status change via AJAX without full page reload
- Board handles 50+ tasks per column without scroll performance issues

---

#### #35 — Task list view
**Labels:** `type: feature` `module: projects` `priority: high` `effort: medium`

- Flat list and grouped-by-milestone modes
- Inline editing: click title or status to edit without opening a panel
- Bulk actions: assign, change status, set due date, delete
- Filter by assignee, label, priority, due date range

**Acceptance criteria:**
- Inline edit saves via Livewire without a page reload
- Bulk actions work correctly when tasks span multiple milestones

---

#### #36 — Task detail slide-over panel
**Labels:** `type: feature` `module: projects` `priority: high` `effort: large`

Slide-over panel (not a modal) containing:
- Title (inline edit), description (rich text — Markdown with preview)
- Status, priority, assignee, due date, labels, milestone
- Sub-tasks checklist
- Time entries for this task
- Comments thread
- Attachments
- Activity log

**Acceptance criteria:**
- Panel opens without a full page navigation
- All fields editable inline without a separate form page

---

#### #37 — Task comments
**Labels:** `type: feature` `module: projects` `priority: medium` `effort: medium`

- Threaded comments on tasks (top-level only, no nested replies)
- Markdown support with preview toggle
- `@mention` autocomplete for workspace members (triggers a notification)
- Edit and delete own comments (admins can delete any)
- Emoji reactions (👍 👎 ❤️ 🎉)

**Acceptance criteria:**
- Mentioning a user creates a notification for them
- Reactions are stored per user (one per emoji per comment)

---

#### #38 — Time tracking
**Labels:** `type: feature` `module: projects` `priority: high` `effort: large`

- `time_entries`: `id`, `workspace_id`, `project_id`, `task_id` (nullable), `user_id`, `description`, `started_at`, `ended_at`, `duration_minutes`, `billable`, `hourly_rate`, `invoiced_at`
- Live timer in the top nav (start/stop, assign to project/task)
- Manual entry form (start time, end time, or duration)
- Time log view per project with totals by member and billable/non-billable split
- Time entries can be locked once invoiced

**Acceptance criteria:**
- Running timer persists across page navigations
- Invoiced time entries cannot be edited or deleted

---

#### #39 — Milestone management
**Labels:** `type: feature` `module: projects` `priority: medium` `effort: medium`

- Create/edit/delete milestones on a project
- Assign tasks to milestones
- Milestone progress: tasks completed vs total
- Milestone due date shown in project timeline view
- Overdue milestone badge

**Acceptance criteria:**
- Completing all tasks in a milestone auto-prompts to mark milestone complete
- Milestones are visible in the task list grouped view

---

#### #40 — Project timeline / Gantt view
**Labels:** `type: feature` `module: projects` `priority: low` `effort: xl`

- Horizontal timeline showing milestones and tasks with date ranges
- Drag handles to resize/move task bars
- Dependency arrows between tasks (finish-to-start only for v1.0)
- Zoom levels: week, month, quarter
- Export timeline as PNG

**Acceptance criteria:**
- Timeline renders correctly for a 6-month project
- Dependency arrows update when tasks are moved

---

#### #41 — Project templates
**Labels:** `type: feature` `module: projects` `priority: low` `effort: medium`

- Save any project as a template (strips dates, assignments, and actual data)
- Template stores: task structure, milestones, statuses, labels
- "New project from template" option in project creation form
- Ship with 3 built-in templates: Web Design Project, Software Sprint, Monthly Retainer

**Acceptance criteria:**
- Creating from a template produces the correct task/milestone structure
- Built-in templates are seeded and cannot be deleted (only copied)

---

### Milestone v0.5 — Billing Module

#### #42 — Billing database schema
**Labels:** `type: feature` `module: billing` `priority: high` `effort: medium`

Tables:
- `invoices`: `id`, `workspace_id`, `client_id`, `project_id` (nullable), `number`, `status`, `issue_date`, `due_date`, `currency`, `subtotal`, `tax_rate`, `tax_amount`, `total`, `notes`, `terms`, `paid_at`, `sent_at`
- `invoice_items`: `id`, `invoice_id`, `description`, `quantity`, `unit_price`, `amount`, `tax_rate`, `time_entry_ids` (JSON)
- `expenses`: `id`, `workspace_id`, `project_id` (nullable), `client_id` (nullable), `description`, `amount`, `currency`, `category`, `receipt_path`, `billable`, `invoiced_at`

**Acceptance criteria:**
- Invoice number sequence is per-workspace, gap-free, and concurrency-safe
- Foreign keys and indexes set correctly for reporting queries

---

#### #43 — Invoice number sequencing
**Labels:** `type: feature` `module: billing` `priority: high` `effort: medium`

- Workspace-configurable format: prefix + year + sequence (e.g. `INV-2025-0042`)
- Atomic increment using a database lock (no gaps, no duplicates under concurrent creation)
- Sequence counter stored on `workspace_settings`
- Preview the next invoice number in the create form

**Acceptance criteria:**
- Concurrent invoice creation test (10 parallel requests) produces no duplicate numbers
- Sequence restarts correctly when the year changes (if workspace prefers yearly reset)

---

#### #44 — Invoice create & edit form
**Labels:** `type: feature` `module: billing` `priority: high` `effort: large`

- Select client (required) and optional project
- Add line items: description, quantity, unit price, tax rate
- Import time entries as line items (filtered by project, date range, billable status)
- Import expenses as line items
- Apply discount (percentage or fixed)
- Notes and payment terms fields
- Live total calculation (subtotal, tax, discount, total)

**Acceptance criteria:**
- Importing time entries correctly converts logged minutes to hours and applies the correct rate
- Total recalculates instantly on any field change

---

#### #45 — Invoice list view
**Labels:** `type: feature` `module: billing` `priority: high` `effort: medium`

- Filter by status: draft, sent, viewed, partial, paid, overdue, void
- Filter by client and date range
- Summary row: total outstanding, total overdue, total paid (filtered period)
- Bulk actions: send, mark paid, download PDFs, void
- Overdue invoices highlighted in red

**Acceptance criteria:**
- Summary totals are correct for filtered results
- Bulk PDF download produces a zip of individual PDFs

---

#### #46 — Invoice PDF generation
**Labels:** `type: feature` `module: billing` `priority: high` `effort: large`

- PDF generated from a dedicated Blade template using `barryvdh/laravel-dompdf`
- Template includes: workspace logo, branding colour, client details, line items, totals, notes, payment instructions
- Workspace can customise: logo, accent colour, footer text
- PDFs stored in `storage/app/invoices/{workspace_id}/` and served via a signed URL
- Regenerated automatically when invoice is edited

**Acceptance criteria:**
- PDF renders correctly for invoices with 1 and 50+ line items
- Logo and branding appear correctly in the PDF

---

#### #47 — Invoice status workflow
**Labels:** `type: feature` `module: billing` `priority: high` `effort: medium`

Status transitions:
- `draft` → `sent` (via send action)
- `sent` → `viewed` (when client opens portal link)
- `sent`/`viewed` → `paid` (manual or payment confirmation)
- `sent`/`viewed` → `partial` (partial payment recorded)
- Any non-void → `void`

- Status change events fire notifications (e.g. invoice viewed alert to workspace owner)
- Overdue detection runs as a scheduled job (daily, 9:00 workspace timezone)

**Acceptance criteria:**
- Invalid status transitions are blocked at the model level
- Overdue job correctly flags invoices past their `due_date` with `paid_at = null`

---

#### #48 — Send invoice to client
**Labels:** `type: feature` `module: billing` `priority: high` `effort: medium`

- "Send invoice" action opens a compose panel pre-filled with email template
- Sends email to primary contact (or selected contacts) with PDF attached and portal link
- Email template customisable per workspace
- Copy sender option
- "Mark as sent" (no email) for invoices sent outside the system

**Acceptance criteria:**
- Email is sent with the correct PDF attachment
- Invoice status transitions to `sent` on email dispatch

---

#### #49 — Record payments
**Labels:** `type: feature` `module: billing` `priority: high` `effort: medium`

- `payments`: `id`, `invoice_id`, `amount`, `method`, `reference`, `notes`, `paid_at`
- Record full or partial payments
- Multiple payments per invoice (for instalment plans)
- Mark invoice as paid automatically when payments equal total
- Payment history on invoice detail

**Acceptance criteria:**
- Recording a partial payment sets status to `partial`
- Recording payments totalling the invoice amount sets status to `paid`

---

#### #50 — Expense tracking
**Labels:** `type: feature` `module: billing` `priority: medium` `effort: medium`

- Log expenses with: description, amount, category, project, billable flag
- Upload receipt image (stored in media library)
- Expense list with filters: project, category, billable, date range
- Import expenses to invoice as line items (same as time entries)
- Expense categories are workspace-configurable

**Acceptance criteria:**
- Receipt upload works for JPG, PNG, PDF
- Billable expenses appear correctly in the invoice line item importer

---

#### #51 — Recurring invoices
**Labels:** `type: feature` `module: billing` `priority: medium` `effort: large`

- Define a recurring schedule: weekly, monthly, quarterly, annually
- Template invoice with all line items defined
- Auto-generate draft invoice on schedule (does not auto-send)
- Option to auto-send if workspace enables it
- Pause and resume recurring schedules

**Acceptance criteria:**
- Recurring job fires on the correct schedule in the configured workspace timezone
- Generated invoice is a draft until reviewed (default behaviour)

---

#### #52 — Billing reports
**Labels:** `type: feature` `module: billing` `priority: low` `effort: large`

- Revenue by month (current year vs previous year)
- Outstanding vs collected by client
- Time logged vs billed ratio
- Top clients by revenue
- Exportable as CSV

**Acceptance criteria:**
- Charts use real data from the database (no hardcoded values)
- CSV export matches the chart data

---

### Milestone v0.6 — Comms Module

#### #53 — Comms database schema
**Labels:** `type: feature` `module: comms` `priority: high` `effort: medium`

Tables:
- `inboxes`: `id`, `workspace_id`, `name`, `email_address`, `provider` (imap/smtp), `settings` (JSON encrypted)
- `conversations`: `id`, `workspace_id`, `inbox_id`, `client_id` (nullable), `subject`, `status` (open/closed/snoozed), `assigned_to`, `last_message_at`
- `messages`: `id`, `conversation_id`, `direction` (inbound/outbound), `from`, `to`, `cc`, `bcc`, `body_html`, `body_text`, `external_id`, `sent_at`
- `message_attachments`: `id`, `message_id`, `filename`, `path`, `mime_type`, `size`

**Acceptance criteria:**
- Migrations clean on SQLite and MySQL
- Conversations correctly scoped to workspace and inbox

---

#### #54 — IMAP inbox connection
**Labels:** `type: feature` `module: comms` `priority: high` `effort: xl`

- Connect an external mailbox via IMAP credentials
- Poll for new messages every 5 minutes via a scheduled job
- Parse raw email: subject, body (HTML + text fallback), attachments, threading headers
- Match inbound emails to existing conversations by `In-Reply-To` / `References` headers
- Create new conversation for unrecognised threads
- Store attachments in media library

**Acceptance criteria:**
- New email in the connected mailbox appears as a conversation within 6 minutes
- Replies correctly thread into existing conversations

---

#### #55 — Conversation list (shared inbox)
**Labels:** `type: feature` `module: comms` `priority: high` `effort: medium`

- Unified inbox showing all conversations across all connected inboxes
- Filter by: inbox, assigned agent, status, client, unread
- Unread indicator and last-message preview
- Assign conversation to a team member
- Snooze conversation (resurfaces at a set time)
- Keyboard shortcuts: `j/k` navigate, `e` close, `r` reply

**Acceptance criteria:**
- Inbox updates in real time when a new message arrives (Livewire polling or Echo)
- Keyboard shortcuts work correctly

---

#### #56 — Conversation detail and reply
**Labels:** `type: feature` `module: comms` `priority: high` `effort: large`

- Full conversation thread (chronological)
- Reply composer with: rich text, attachments, CC/BCC
- Saved reply templates (canned responses)
- Internal notes (visible to team only, yellow background)
- Assign, tag, close, reopen actions in the sidebar
- Link conversation to a client record

**Acceptance criteria:**
- Outbound reply is sent via SMTP and appears in the thread
- Internal notes are never sent externally

---

#### #57 — Outbound email (SMTP)
**Labels:** `type: feature` `module: comms` `priority: high` `effort: medium`

- Configure outbound SMTP per inbox
- Replies sent from the inbox email address (correct `From:` header)
- Message threading headers set correctly (`In-Reply-To`, `References`)
- Delivery failure (bounce) captured and shown on the message

**Acceptance criteria:**
- A reply sent from the app arrives in the recipient's inbox with the correct sender
- Threading works in Gmail, Outlook, and Apple Mail

---

#### #58 — Conversation labels and organisation
**Labels:** `type: feature` `module: comms` `priority: medium` `effort: small`

- Workspace-defined labels (colour + name)
- Apply multiple labels per conversation
- Filter inbox by label
- Label management in comms settings

**Acceptance criteria:**
- Labels are workspace-scoped (not shared between workspaces)
- Filtering by label returns correct results

---

#### #59 — Canned responses (saved replies)
**Labels:** `type: feature` `module: comms` `priority: medium` `effort: small`

- Create/edit/delete canned responses with a name and body (rich text)
- Insert via `/` command in the reply composer
- Personal (only visible to creator) and shared (visible to all workspace members)
- Search by name when inserting

**Acceptance criteria:**
- Canned response inserts correctly into the composer without formatting issues
- Personal vs shared scoping is enforced

---

#### #60 — In-app notifications for new messages
**Labels:** `type: feature` `module: comms` `priority: medium` `effort: medium`

- Bell notification when a new message arrives in an assigned conversation
- Desktop push notification (browser Notification API) with user opt-in
- Email digest option: immediate / hourly / daily summary
- "You're mentioned" notification for @mentions in internal notes

**Acceptance criteria:**
- Browser push notification fires within 1 minute of message arrival
- Digest emails batch correctly by frequency setting

---

### Milestone v0.7 — Docs Module

#### #61 — Docs database schema
**Labels:** `type: feature` `module: docs` `priority: high` `effort: medium`

Tables:
- `doc_spaces`: `id`, `workspace_id`, `name`, `slug`, `visibility` (internal/client), `icon`, `timestamps`
- `docs`: `id`, `space_id`, `parent_id` (nullable, for nesting), `title`, `slug`, `content` (longtext), `format` (markdown), `status` (draft/published), `created_by`, `updated_by`, `published_at`, `timestamps`
- `doc_versions`: `id`, `doc_id`, `content`, `created_by`, `created_at` (append-only)

**Acceptance criteria:**
- Docs support unlimited nesting depth (via parent_id)
- Version history is append-only and cannot be deleted

---

#### #62 — Doc editor
**Labels:** `type: feature` `module: docs` `priority: high` `effort: xl`

- Rich Markdown editor with live preview toggle
- Toolbar: headings, bold, italic, code block, table, image upload, link
- Keyboard shortcuts matching common editors (Ctrl+B, Ctrl+K, etc.)
- Auto-save every 30 seconds (saves a version silently)
- Conflict detection: warn if another user has the doc open
- Word count and reading time in footer

**Acceptance criteria:**
- Editor works without JavaScript errors on latest Chrome, Firefox, Safari
- Auto-save does not interrupt typing

---

#### #63 — Doc spaces and navigation
**Labels:** `type: feature` `module: docs` `priority: high` `effort: medium`

- Left sidebar tree showing spaces and nested docs
- Collapse/expand spaces
- Drag to reorder docs within a space
- Breadcrumb navigation
- "New doc" and "New space" actions from the sidebar
- Space visibility: internal (team only) or client-visible (appears in client portal)

**Acceptance criteria:**
- Reordering docs persists correctly
- Client-visible spaces appear in the client portal

---

#### #64 — Doc version history
**Labels:** `type: feature` `module: docs` `priority: medium` `effort: medium`

- List of all saved versions (timestamp + author)
- Side-by-side diff view (added lines green, removed lines red)
- Restore any previous version (creates a new version, doesn't overwrite)
- Version history accessible from the doc toolbar

**Acceptance criteria:**
- Diff view correctly highlights changes between any two versions
- Restore creates a new version entry rather than deleting history

---

#### #65 — Doc sharing and client portal integration
**Labels:** `type: feature` `module: docs` `priority: medium` `effort: medium`

- Published docs in client-visible spaces appear in the client portal
- Generate a shareable public link for any published doc (no login required)
- Public links can be revoked
- Password-protected public links (optional)

**Acceptance criteria:**
- A revoked public link returns 404
- Unpublished docs never appear on public links regardless of space visibility

---

#### #66 — File manager
**Labels:** `type: feature` `module: docs` `priority: medium` `effort: large`

- Upload files attached to: workspace (global), client, project, or doc
- Supported types: images, PDFs, Office docs, archives (up to 50MB default, configurable)
- Thumbnail generation for images
- File list with search, filter by type, sort by date/name/size
- Download, rename, move, delete
- Storage usage indicator in workspace settings

**Acceptance criteria:**
- Files are scoped correctly (a client's file is not visible in another client's file manager)
- Thumbnail generation works for JPG, PNG, GIF, WebP

---

#### #67 — Proposal builder
**Labels:** `type: feature` `module: docs` `priority: low` `effort: xl`

Build client-facing proposals from docs:
- Proposal template with variable substitution (`{{client_name}}`, `{{project_name}}`, etc.)
- Sections: introduction, scope of work, timeline, pricing, terms
- Generate a shareable link with a clean presentation view (no editor chrome)
- Client can accept or decline the proposal (records name + timestamp)
- Accepted proposals generate a notification to the workspace

**Acceptance criteria:**
- Variable substitution replaces all known variables correctly
- Client acceptance is recorded with IP address and timestamp

---

### Milestone v0.8 — API & Webhooks

#### #68 — REST API foundation
**Labels:** `type: feature` `module: api` `priority: high` `effort: large`

- Versioned JSON API under `/api/v1/`
- Laravel Sanctum for API token authentication
- Standard response envelope: `{ data, meta, links }` (JSON:API-inspired, not strict)
- Rate limiting: 60 requests/minute per token
- API documentation auto-generated (Scribe or Scramble)

**Acceptance criteria:**
- All API endpoints return the correct envelope structure
- Rate limit headers present on every response
- Docs site generated from code annotations

---

#### #69 — API: Clients endpoints
**Labels:** `type: feature` `module: api` `priority: high` `effort: medium`

`GET /clients`, `POST /clients`, `GET /clients/{id}`, `PUT /clients/{id}`, `DELETE /clients/{id}`, `GET /clients/{id}/contacts`

**Acceptance criteria:**
- All endpoints respect workspace scoping
- Filter and pagination params documented and tested

---

#### #70 — API: Projects & Tasks endpoints
**Labels:** `type: feature` `module: api` `priority: high` `effort: medium`

`GET /projects`, `POST /projects`, `GET /projects/{id}`, `PUT /projects/{id}`, `DELETE /projects/{id}`, `GET /projects/{id}/tasks`, `POST /tasks`, `GET /tasks/{id}`, `PUT /tasks/{id}`, `DELETE /tasks/{id}`

**Acceptance criteria:**
- Task create/update correctly validates status transitions
- Nested tasks (sub-tasks) retrievable via `?include=children`

---

#### #71 — API: Billing endpoints
**Labels:** `type: feature` `module: api` `priority: high` `effort: medium`

`GET /invoices`, `POST /invoices`, `GET /invoices/{id}`, `PUT /invoices/{id}`, `POST /invoices/{id}/send`, `POST /invoices/{id}/payments`, `GET /time-entries`, `POST /time-entries`, `GET /expenses`, `POST /expenses`

**Acceptance criteria:**
- Sending an invoice via the API triggers the same email flow as the UI
- Time entry creation validates project membership

---

#### #72 — API token management UI
**Labels:** `type: feature` `module: api` `priority: high` `effort: small`

- Create named API tokens with optional expiry
- Assign scopes (read-only, full-access, per-module)
- Revoke tokens
- Last-used timestamp on each token

**Acceptance criteria:**
- A revoked token returns 401 immediately
- Scoped tokens cannot access endpoints outside their scope

---

#### #73 — Webhook system
**Labels:** `type: feature` `module: api` `priority: medium` `effort: large`

- `webhooks`: `id`, `workspace_id`, `url`, `secret`, `events` (JSON array), `active`
- Events for all major model changes (e.g. `invoice.sent`, `task.completed`, `client.created`)
- Delivery: signed HMAC-SHA256 payload, 3 retries with exponential backoff
- Delivery log: status, response code, duration, payload
- Webhook test button (sends a `ping` event)

**Acceptance criteria:**
- Signature verification can be reproduced by the receiving server
- Failed deliveries are retried and logged

---

#### #74 — Zapier / Make.com webhook compatibility
**Labels:** `type: feature` `module: api` `priority: low` `effort: medium`

- Webhook payloads formatted to be compatible with Zapier's standard trigger format
- Document example payloads for each event in the API docs
- Test webhook responses against real Zapier triggers in a fixture test

**Acceptance criteria:**
- A Zapier zap using the OnePointHub webhook trigger can be set up without custom payload mapping

---

### Milestone v0.9 — Polish & Hardening

#### #75 — Security audit: OWASP Top 10
**Labels:** `type: security` `module: core` `priority: high` `effort: xl`

Systematic audit against:
- SQL injection (parameterised queries, no raw interpolation)
- XSS (Blade auto-escaping, Content-Security-Policy header)
- CSRF (Laravel default, verified on all state-changing routes)
- IDOR (workspace scoping unit tests for every resource)
- Auth bypass (route middleware coverage check)
- Mass assignment (all models have explicit `$fillable`)

Produce a security checklist document as a deliverable.

**Acceptance criteria:**
- All `$fillable` arrays audited and documented
- CSP header configured and tested with CSP Evaluator
- Zero direct object reference vulnerabilities in automated tests

---

#### #76 — Performance: query optimisation pass
**Labels:** `type: chore` `module: core` `priority: high` `effort: large`

- Enable Laravel Debugbar in local env
- Identify and fix N+1 queries across all list views (target: < 20 queries per page)
- Add eager loading to all resource controllers
- Add database indexes for all foreign keys and common filter columns
- Benchmark dashboard and invoice list with 10,000 records

**Acceptance criteria:**
- Laravel Telescope shows < 20 queries on the project list view with 100 projects
- No N+1 queries in any Livewire component

---

#### #77 — Accessibility audit (WCAG 2.1 AA)
**Labels:** `type: chore` `module: core` `priority: medium` `effort: large`

- All interactive elements keyboard-navigable
- All form fields have associated labels
- Colour contrast ratios meet AA requirements
- Skip navigation link
- ARIA roles on dynamic components (modals, dropdowns, Livewire updates)
- Test with axe-core in CI

**Acceptance criteria:**
- axe-core reports zero violations on the 10 most-used pages
- All flows completable without a mouse

---

#### #78 — Localisation foundation (i18n)
**Labels:** `type: feature` `module: core` `priority: medium` `effort: large`

- All user-facing strings extracted to Laravel lang files
- English as the base locale
- Date/time displayed in the authenticated user's timezone preference
- Currency formatting respects locale
- Locale switcher in user settings (with available locales list)
- Ship with: `en`, `el` (Greek), `de`, `fr` as initial translations (community contribution welcome for others)

**Acceptance criteria:**
- Switching locale changes all UI strings without a code change
- No hardcoded English strings in Blade templates

---

#### #79 — Email delivery configuration
**Labels:** `type: feature` `module: core` `priority: high` `effort: medium`

- Support multiple mail drivers: SMTP, Mailgun, Postmark, SES, log (dev)
- `MAIL_*` env vars documented clearly
- Test email button in workspace settings
- All outbound emails use workspace name as sender name
- Unsubscribe link in non-transactional emails (digest, marketing)

**Acceptance criteria:**
- Test email delivers correctly for each supported driver
- All transactional emails render correctly in Litmus or Mail Tester

---

#### #80 — Backup and restore
**Labels:** `type: feature` `module: core` `priority: medium` `effort: medium`

Using `spatie/laravel-backup`:
- Daily automated backup (DB dump + uploaded files) to configured storage disk
- Manual backup trigger from workspace settings (admin only)
- Backup log showing last 10 backups with file size
- Documented restore procedure in `docs/operations.md`

**Acceptance criteria:**
- Automated backup job runs on schedule
- A restored backup produces a functional application

---

#### #81 — Two-factor authentication
**Labels:** `type: security` `module: core` `priority: medium` `effort: medium`

- TOTP-based 2FA (Google Authenticator, Authy compatible)
- Recovery codes (10 single-use codes, downloadable)
- Enforce 2FA workspace-wide (admin setting)
- 2FA setup/disable in user security settings

**Acceptance criteria:**
- 2FA challenge appears after correct password on login
- Recovery code login works and marks the code as used

---

#### #82 — Data export (workspace)
**Labels:** `type: feature` `module: core` `priority: medium` `effort: medium`

- Export all workspace data as a ZIP: clients (CSV), projects + tasks (CSV), invoices (CSV + PDFs), time entries (CSV), docs (Markdown files)
- Export triggered from workspace settings
- Runs as a queued job; download link emailed when ready
- Export complies with GDPR right to data portability

**Acceptance criteria:**
- Export completes for a workspace with 6 months of seeder data
- All CSVs are valid and importable into Excel

---

#### #83 — Rate limiting and abuse protection
**Labels:** `type: security` `module: core` `priority: medium` `effort: medium`

- Login: 10 attempts / 15 minutes per IP + email combination
- API: 60 requests / minute per token (configurable per workspace plan)
- File uploads: max 50MB per file, 500MB per workspace per day
- Public portal: 30 requests / minute per IP
- Throttle middleware applied consistently across all routes

**Acceptance criteria:**
- Exceeding login rate limit returns 429 with `Retry-After` header
- API rate limit headers present on every response

---

#### #84 — Scheduled tasks and queue configuration
**Labels:** `type: chore` `module: core` `priority: high` `effort: medium`

Document and test all scheduled commands:
- `invoices:mark-overdue` (daily)
- `notifications:send-digest` (hourly)
- `recurring-invoices:generate` (daily)
- `comms:fetch-mail` (every 5 minutes)
- `backup:run` (daily)
- `telescope:prune` (daily)

- `config/schedule.php` or equivalent well-documented
- All jobs have unique names for monitoring
- Failed job handling: retry 3×, then dispatch `JobFailed` event

**Acceptance criteria:**
- All scheduled tasks listed in `docs/operations.md` with their purpose and cadence
- Failed jobs produce a notification to workspace owner

---

### Milestone v1.0 — Launch

#### #85 — Production Docker image
**Labels:** `type: chore` `module: installer` `priority: high` `effort: large`

- Multi-stage Dockerfile: build stage (Node, Composer) + runtime stage (PHP-FPM + Nginx, ~150MB)
- `docker-compose.prod.yml` for single-server deployment
- Environment variables documented in `.env.example` with comments
- Health check endpoint: `GET /up` (Laravel's built-in)
- Image published to Docker Hub: `onepointHub/app`
- Signed image (Cosign)

**Acceptance criteria:**
- `docker pull onepointHub/app && docker compose up` produces a running application
- Image passes Trivy vulnerability scan with no CRITICAL findings

---

#### #86 — One-click deploy guides
**Labels:** `type: docs` `module: installer` `priority: high` `effort: medium`

Write deployment guides for:
- **Coolify** (recommended for non-technical users)
- **Laravel Forge + DigitalOcean**
- **Bare VPS** (Ubuntu 24.04 + Nginx + PHP-FPM + MySQL)
- **Railway / Render** (containerised PaaS)

Each guide covers: system requirements, install steps, env configuration, SSL, queue worker setup, and cron.

**Acceptance criteria:**
- Each guide tested end-to-end on a fresh server
- No step requires knowledge not explained in the guide

---

#### #87 — Documentation site
**Labels:** `type: docs` `module: core` `priority: high` `effort: large`

Using VitePress or Docusaurus:
- Getting Started (install, first workspace, first client)
- Module guides (one per module)
- API reference (auto-generated + hand-written examples)
- Configuration reference (all env vars)
- Operations guide (backups, upgrades, monitoring)
- Contributing guide

**Acceptance criteria:**
- Docs site deploys to GitHub Pages from `docs/` on every merge to `main`
- API reference reflects the actual implementation (generated, not manual)

---

#### #88 — Upgrade command and migration safety
**Labels:** `type: feature` `module: installer` `priority: high` `effort: medium`

- `php artisan onepointHub:upgrade` command that:
  1. Puts the app in maintenance mode
  2. Backs up the database
  3. Runs new migrations
  4. Clears and rebuilds caches
  5. Takes app out of maintenance mode
- Migration safety: all migrations are non-destructive by default (add columns/tables, never drop in a v1 migration)
- Changelog entry required for every migration

**Acceptance criteria:**
- Upgrade from any previous minor version completes without data loss
- Downtime is under 30 seconds for a typical upgrade

---

#### #89 — Workspace settings panel (complete)
**Labels:** `type: feature` `module: core` `priority: high` `effort: medium`

Consolidate all settings into a well-organised panel:
- **General** — name, slug, logo, accent colour, timezone, currency, date format
- **Members** — invite, roles, remove
- **Billing** — invoice prefix, default payment terms, tax settings
- **Integrations** — API tokens, webhooks, connected inboxes
- **Notifications** — global notification preferences
- **Security** — enforce 2FA, session timeout, API access toggle
- **Danger zone** — export data, delete workspace

**Acceptance criteria:**
- All settings save and take effect without a page reload where possible
- Danger zone actions require typed confirmation (`delete my workspace`)

---

#### #90 — Public changelog and versioning
**Labels:** `type: docs` `module: core` `priority: medium` `effort: small`

- `CHANGELOG.md` following Keep a Changelog format
- Semantic versioning enforced (no breaking changes in patch releases)
- GitHub release for every tag with auto-generated release notes
- In-app "What's new" panel showing the last 3 changelog entries (fetched from GitHub releases API)

**Acceptance criteria:**
- Every merged PR references an issue number
- GitHub release is created automatically by CI when a tag is pushed

---

#### #91 — End-to-end test suite (Playwright)
**Labels:** `type: chore` `module: core` `priority: high` `effort: xl`

Critical user journeys covered:
- Register → create workspace → invite member
- Create client → create project → add tasks
- Log time → create invoice from time → send invoice
- Client portal: login via magic link → view invoice → download PDF
- Shared inbox: receive email → reply → close conversation

**Acceptance criteria:**
- All 5 journeys pass on Chrome and Firefox in CI
- E2E suite runs against the production Docker image

---

#### #92 — Community and contribution infrastructure
**Labels:** `type: docs` `module: core` `priority: medium` `effort: medium`

- `CONTRIBUTING.md` with: code style, PR process, commit convention, how to add a module
- `CODE_OF_CONDUCT.md` (Contributor Covenant)
- Issue templates: bug report, feature request, module proposal
- PR template with checklist
- GitHub Discussions enabled for Q&A and ideas
- `SECURITY.md` with responsible disclosure process

**Acceptance criteria:**
- All templates render correctly in GitHub
- Security policy links to a contact method

---

#### #93 — Workspace plan / feature flags system
**Labels:** `type: feature` `module: core` `priority: low` `effort: large`

Lay the groundwork for future hosted/pro tiers without locking features in v1.0:
- `workspace_plan` field on workspaces (default: `self_hosted`)
- `Feature` enum listing all feature flags
- `HasFeature` middleware that checks plan before route access
- All flags enabled for `self_hosted` plan (no restrictions in OSS version)
- Configuration in `config/features.php`

**Acceptance criteria:**
- A feature flag check does not add measurable overhead to the request lifecycle
- `self_hosted` plan has all features enabled with zero configuration

---

#### #94 — In-app feedback and issue reporting
**Labels:** `type: feature` `module: core` `priority: low` `effort: small`

- "Report a bug" link in the user menu
- Pre-fills a GitHub issue template in a new tab with system info (Laravel version, PHP version, browser)
- No telemetry or data sent without user action (privacy-first)

**Acceptance criteria:**
- System info is accurate (pulled from the running environment)
- No data is sent automatically without user action

---

#### #95 — Performance: caching layer
**Labels:** `type: chore` `module: core` `priority: medium` `effort: medium`

- Cache expensive aggregations: dashboard stats, billing totals, project progress
- Cache tags per workspace (flush on model change via observers)
- `CACHE_DRIVER=array` for testing (no Redis dependency in tests)
- Cache hit/miss ratio visible in Telescope
- Document which cache keys exist and their TTLs in `docs/development.md`

**Acceptance criteria:**
- Dashboard stats served from cache on second load (verified via Telescope)
- Cache correctly invalidated when underlying data changes

---

#### #96 — Mobile-responsive audit
**Labels:** `type: chore` `module: core` `priority: medium` `effort: medium`

Test all primary views at 375px (iPhone SE), 390px (iPhone 14), 414px (large Android), 768px (iPad):
- Navigation (drawer mode)
- Client/project/invoice list views
- Task kanban (horizontal scroll, not broken)
- Invoice PDF view
- Time entry form

Fix layout breakages found.

**Acceptance criteria:**
- Zero horizontal overflow on any tested view at 375px
- Core CRUD actions completable on a mobile browser

---

#### #97 — Workspace onboarding checklist
**Labels:** `type: feature` `module: core` `priority: low` `effort: small`

Persistent onboarding checklist in the dashboard for new workspaces:
- ☐ Add your first client
- ☐ Create a project
- ☐ Invite a team member
- ☐ Set up your invoice branding
- ☐ Connect your inbox

Checklist auto-dismisses when all items complete (or can be manually dismissed).

**Acceptance criteria:**
- Each checklist item marks itself complete when the corresponding action is performed
- Dismissed state persists per workspace

---

#### #98 — Search indexing and performance
**Labels:** `type: chore` `module: core` `priority: medium` `effort: medium`

- Ensure all Scout-indexed models re-index correctly after bulk import
- `php artisan scout:import` works for all models
- `search:reindex` command that rebuilds the full index safely
- Document switching from database driver to TNTSearch in the ops guide

**Acceptance criteria:**
- `scout:import` for 1,000 records completes without timeout
- Search returns correct results after a reindex

---

#### #99 — Smoke test suite for production deploy
**Labels:** `type: chore` `module: core` `priority: high` `effort: medium`

A lightweight Playwright suite that verifies a fresh production deployment:
- App loads (200 response)
- Registration works
- Login works
- `/api/v1/ping` returns 200 with a valid token
- Queue is processing (enqueue a test job, verify it runs)
- Storage is writable (upload a small file)

Runs as part of the CI deploy pipeline after every release.

**Acceptance criteria:**
- All 6 checks pass after a clean Docker deploy
- Suite completes in under 2 minutes

---

#### #100 — Stable v1.0 release
**Labels:** `type: chore` `module: core` `priority: high` `effort: medium`

Final v1.0 release checklist:
- All v0.9 issues resolved
- CHANGELOG.md updated with full v1.0 entry
- `README.md` reflects current state (screenshots, install steps, feature list)
- Docker Hub image tagged `1.0.0` and `latest`
- Documentation site deployed and linked from README
- GitHub release published with a release announcement draft
- Social announcement post drafted (Dev.to, Hacker News, Laravel News)

**Acceptance criteria:**
- Smoke test suite passes against the `1.0.0` Docker image
- No open `priority: high` issues in any milestone ≤ v1.0
