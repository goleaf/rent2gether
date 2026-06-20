# Architecture

This document records the working architecture for `rent2gether`. Keep it factual and update it when the codebase gains real domain modules.

## Stack Baseline

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

The canonical domain inventory lives in [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md). Do not infer the current feature surface from older Laravel starter-kit defaults.

## Architectural Contract

- Laravel is the application framework.
- Blade is the server-side frontend layer.
- Eloquent models are the only query layer.
- Livewire class components are the interactive application layer.
- Flux Pro is the application component system.
- No Filament, Livewire Volt, Inertia, React/Vue SPA, or admin/staff panel should be introduced.
- Laravel Boost MCP is the preferred source for app information, docs, schema inspection, browser logs, and URL resolution.

The core marketplace loop is:

- Guest chooses city, dates, and sleeping place.
- System calculates availability, nights, calendar days, price, discount, deposit, rules, and compatibility.
- Host controls property, rooms, sleeping places, calendar, price, rules, and requests.

Everything must stay mobile-first, multilingual, fast, friendly, and Livewire-native.

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
| Livewire class components | `app/Livewire/` |
| Blade components | `resources/views/components/` |
| Livewire Blade views | `resources/views/livewire/` |
| View composers | `app/Providers/ViewServiceProvider.php` |

Create framework files with Artisan where possible, for example `php artisan make:class`, `php artisan make:model`, `php artisan make:request`, and `php artisan make:test --phpunit`.

## Data Flow

For Livewire/Blade pages:

1. Route resolves named route and model bindings.
2. Livewire class component authorizes through a policy when needed.
3. Component validation, form objects, actions, or services validate input.
4. Action/service/model scope prepares data with selected columns, eager loads, and aggregates.
5. Component passes compact scalar state or DTO arrays to Blade.
6. Blade renders presentation only.

## Boundaries

- Do not introduce React, Vue, Inertia, or SPA routing.
- Do not introduce Filament, Livewire Volt, admin panels, or staff tools.
- Do not put business logic into Blade, Livewire render methods, route closures, or table render callbacks.
- Do not add raw SQL strings.
- Do not add unbounded reads.
- Do not hide authorization failures with UI-only controls.
