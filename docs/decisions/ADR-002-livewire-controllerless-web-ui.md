# ADR-002: Use Livewire Class Components Instead Of Web Controllers

## Status

Accepted

## Date

2026-06-20

## Context

The project is a Laravel 13 + Livewire 4 + Flux Pro + SQLite marketplace. The product must stay mobile-first, localized, Livewire-native, and free of Filament, Volt, Inertia, React/Vue SPA, and admin/staff surfaces.

Older starter-style web controllers and Blade wrapper views remained in the repository after the app had already moved to Livewire pages. Those files encouraged future prompts to recreate controller-backed routes, duplicate auth/search views, and scatter behavior across parallel surfaces.

## Decision

User-facing web UI routes use Livewire class components directly. The `app/Http/Controllers` directory is not part of this application architecture.

Routes in `routes/web.php` should point to Livewire page components inside existing locale/auth/host groups. Route model binding is received by the Livewire component `mount()` method. Business logic belongs in actions, services, models, policies, and form objects. Blade renders prepared values only.

The following legacy surfaces were removed and must not be recreated:

- `app/Http/Controllers/`
- `resources/views/auth/`
- `resources/views/search/`

## Consequences

- Future pages should be added under `app/Livewire/...` with matching views under `resources/views/livewire/...`.
- Auth forms stay in Livewire components; logout is a Livewire action component.
- Public search is `App\Livewire\Search\SleepingPlaceSearch`.
- Legacy `beds.show` is bridged by `App\Livewire\Beds\ShowBed`; canonical sleeping-place detail is `App\Livewire\Places\ShowSleepingPlace`.
- Documentation and skills must point agents to `docs/PROJECT_STRUCTURE.md` before creating files.
- Architecture tests must keep `app/Http/Controllers` absent and prevent routes from referencing `App\Http\Controllers`.

## Alternatives Considered

### Keep Thin Controllers

Rejected. Thin controllers still invite future controller scaffolding and duplicate route/view surfaces in this repo.

### Use Route Closures For Small Actions

Rejected for user-facing flows. Route closures are harder to test and would create another orchestration style beside Livewire.

### Use Filament Or An Admin Panel

Rejected. The project explicitly prohibits Filament and admin/staff panels for now.
