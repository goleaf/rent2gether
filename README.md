# rent2gether

`rent2gether` is a Laravel application prepared for Blade, Livewire, Flux UI Pro, and Tailwind CSS development.

The project contract is documented for agents and humans in [AGENTS.md](AGENTS.md). Read it before changing code.

## Stack

| Area | Current Choice |
| --- | --- |
| Runtime | PHP 8.5 |
| Framework | Laravel 13.16 |
| Frontend | Blade server-rendered views with Livewire 4 |
| Database | SQLite locally |
| Query layer | Eloquent models and relationships only |
| Components | Flux UI Pro 2.14 |
| CSS/build | Tailwind CSS 4 and Vite |
| Tests | PHPUnit 12 via `php artisan test` |
| Agent tooling | Laravel Boost MCP |

Do not add Filament, admin panels, staff panels, Livewire Volt, Inertia, React, or Vue.
Flux Pro is installed from the local `_data/flux-pro` Composer path repository. The proprietary package payload is ignored by Git.

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Laravel Herd serves the project locally at the project `.test` domain. Agents should use Laravel Boost `get-absolute-url` before sharing URLs.

## Common Commands

| Command | Purpose |
| --- | --- |
| `php artisan migrate` | Run database migrations |
| `php artisan db:seed --class=Database\\Seeders\\DatabaseSeeder --no-interaction` | Seed the demo plus 1000-row bulk marketplace dataset |
| `php artisan db:seed --class=Database\\Seeders\\GeoNamesFullSeeder --no-interaction` | Manually run the optional full GeoNames import |
| `php artisan route:list --except-vendor` | Inspect application routes |
| `php artisan test --compact` | Run the test suite |
| `php artisan test --compact tests/Feature/DemoSeederTest.php` | Verify demo and bulk seed coverage |
| `php artisan test --compact --filter=testName` | Run a focused test |
| `vendor/bin/pint --dirty --format agent` | Format changed PHP files |
| `npm run dev` | Start Vite during frontend work |
| `npm run build` | Build production frontend assets |

Do not use `php artisan serve` for normal local work because Herd already serves the app.

## Architecture

- This project is controllerless for web UI. Do not create `app/Http/Controllers/`.
- Mount user-facing pages as Livewire class components from `routes/web.php`.
- Put business behavior in actions, services, model scopes/methods, policies, jobs, events/listeners, and observers.
- Use Form Requests for validation.
- Use Policies for authorization.
- Use Eloquent relationships and scopes for all data access.
- Pass fully prepared data into Blade views.
- Avoid business logic in Blade.

See [docs/PROJECT_STRUCTURE.md](docs/PROJECT_STRUCTURE.md) and [docs/architecture.md](docs/architecture.md) for file placement conventions and the current app map.

## Query Policy

This codebase follows a strict Eloquent-only policy:

- No raw SQL strings.
- No unbounded `Model::all()`.
- No queries in Blade templates or loops.
- Eager load relationships used by views and tables.
- Use `withCount`, `withSum`, `withAvg`, `withMin`, `withMax`, and `withExists` for aggregates.
- Inspect schema and indexes before query-sensitive changes.

## Documentation

- [AGENTS.md](AGENTS.md): canonical agent rules.
- [CLAUDE.md](CLAUDE.md): compact mirror for Claude-based tools.
- [docs/PROJECT_STRUCTURE.md](docs/PROJECT_STRUCTURE.md): canonical file placement map and deleted legacy surfaces.
- [docs/architecture.md](docs/architecture.md): app structure, current state, and placement rules.
- [docs/BULK_SEEDING.md](docs/BULK_SEEDING.md): default seed path, 1000-row model contract, and GeoNames import boundary.
- [docs/component-system.md](docs/component-system.md): Blade, Tailwind, Livewire, and Flux component rules.
- [docs/development-workflow.md](docs/development-workflow.md): implementation workflow and verification checklist.
- [docs/flux-pro-integration.md](docs/flux-pro-integration.md): Flux Pro installation and maintenance runbook.
- [docs/ui-flux-pro-migration.md](docs/ui-flux-pro-migration.md): latest Flux component migration notes and guard tests.
- [docs/decisions/ADR-001-laravel-blade-eloquent-architecture.md](docs/decisions/ADR-001-laravel-blade-eloquent-architecture.md): accepted stack and data-access decision.
- [docs/decisions/ADR-002-livewire-controllerless-web-ui.md](docs/decisions/ADR-002-livewire-controllerless-web-ui.md): controllerless Livewire web UI decision.
