# Architecture

This document records the working architecture for `rent2gether`. Keep it factual and update it when the codebase gains real domain modules.

## Current State

Laravel Boost verified the application on 2026-06-18:

- PHP `8.5`
- Laravel `13.16.1`
- Livewire `4.3.1`
- Flux UI `2.14.1`
- Flux UI Pro `2.14.1`
- Laravel Boost `2.4.10`
- Laravel MCP `0.8.1`
- SQLite database
- Tailwind CSS `4.3.1`
- PHPUnit `12.5.30`

The database currently contains the default Laravel baseline tables:

- `users`
- `sessions`
- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`
- `password_reset_tokens`
- `migrations`

The application code is also baseline:

- `routes/web.php` defines the root welcome route.
- `app/Models/User.php` is the only application model.
- `resources/views/components/layouts/app.blade.php` is the shared Blade layout component.
- `resources/views/layouts/app.blade.php` bridges Livewire's default layout to the shared Blade layout.
- `resources/views/welcome.blade.php` is a Flux-backed setup screen.

## Architectural Contract

- Laravel is the application framework.
- Blade is the server-side frontend layer.
- Eloquent models are the only query layer.
- Filament is the intended admin-panel layer when installed.
- Flux Pro is the application component system.
- Laravel Boost MCP is the preferred source for app information, docs, schema inspection, browser logs, and URL resolution.

## File Placement

| Concern | Location |
| --- | --- |
| Single-model scopes | `app/Models/ModelName.php` |
| Shared model concerns | `app/Models/Concerns/` |
| Business actions | `app/Actions/` |
| Domain/application services | `app/Services/` |
| HTTP validation | `app/Http/Requests/` |
| Authorization | `app/Policies/` |
| JSON resources | `app/Http/Resources/` |
| Jobs | `app/Jobs/` |
| Events and listeners | `app/Events/`, `app/Listeners/` |
| Observers | `app/Observers/` |
| Blade components | `resources/views/components/` |
| View composers | `app/Providers/ViewServiceProvider.php` |
| Filament resources | `app/Filament/Resources/` |
| Filament widgets | `app/Filament/Widgets/` |
| Filament custom actions | `app/Filament/Actions/` |

Create framework files with Artisan where possible, for example `php artisan make:class`, `php artisan make:model`, `php artisan make:request`, and `php artisan make:test --phpunit`.

## Data Flow

For Blade pages:

1. Route resolves named route and model bindings.
2. Controller or invokable action authorizes through a policy.
3. Form Request validates input when relevant.
4. Action/service/model scope prepares data with eager loads and aggregates.
5. Controller returns a Blade view with named data.
6. Blade renders presentation only.

For Filament resources, once Filament exists:

1. Resource query is scoped and eager loaded in `getEloquentQuery()`.
2. Table columns read already-loaded relationships or database-provided aggregates.
3. Actions and bulk actions authorize explicitly.
4. Forms delegate business behavior to actions/services where behavior is non-trivial.

## Boundaries

- Do not introduce React, Vue, Inertia, or SPA routing.
- Do not put business logic into Blade, resources, route closures, or table render callbacks.
- Do not add raw SQL strings.
- Do not add unbounded reads.
- Do not hide authorization failures with UI-only controls.
