# Architecture Rules

Hard constraints:

- Laravel 13
- PHP 8.3+
- Livewire 4 class components
- Flux Pro
- SQLite
- Blade server-side rendered UI
- Mobile-first, starting at 320px
- English and Russian from day one
- SleepingPlace as the main rentable unit

Do not introduce:

- Filament
- Livewire Volt
- Inertia
- React/Vue SPA routing
- admin panels
- support, moderator, staff, cleaner, manager, or finance panels
- required cron, queues, or jobs for the foundation layer

Business logic belongs in services, actions, policies, models, or DTO/presenter classes. Blade views should render prepared data only.

Routes must stay grouped by middleware, prefix, and names. Public routes should keep locale support. Authenticated guest and host flows should remain separate from any future staff/admin concept.

SQLite-facing features need indexes for foreign keys, search filters, calendar lookups, booking ranges, translation lookups, and common sorting paths. Use `EXPLAIN QUERY PLAN` for critical search and availability queries before broad release.
