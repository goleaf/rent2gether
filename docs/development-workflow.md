# Development Workflow

Use this workflow for code changes in `rent2gether`.

## Before Changing Code

1. Read [AGENTS.md](../AGENTS.md).
2. Read [PROJECT_STRUCTURE.md](PROJECT_STRUCTURE.md) before creating files.
3. Check `git status --short` and preserve user changes.
4. Use Laravel Boost `application_info` for version-specific work.
5. Use Laravel Boost `search-docs` before changing Laravel ecosystem APIs.
6. Use Laravel Boost `database-schema` before migrations, models, scopes, filters, resources, or relationship-heavy queries.
7. Use `fluxui-development` before Flux, Flux Pro, Livewire UI, or Laravel component-system work.
8. Inspect sibling files and follow existing conventions.

Keep the core marketplace loop in view: guest chooses city, dates, and sleeping place; the system calculates availability, nights, calendar days, price, discount, deposit, rules, and compatibility; host controls property, rooms, sleeping places, calendar, price, rules, and requests.

## Implementation Rules

- Do not create `app/Http/Controllers/`.
- Do not create controller-backed web routes, `resources/views/auth/`, or `resources/views/search/`.
- Use Livewire class components for user-facing workflows and actions.
- Use actions/services for behavior.
- Use Form Requests for validation.
- Use Policies for authorization.
- Use Eloquent scopes and relationships for data access.
- Eager load relationships before views or tables use them.
- Use aggregate eager loaders for counts and sums.
- Use Blade components for reusable UI.
- Keep Blade free of queries and business logic.
- Use Flux components for common UI primitives after Flux Pro is installed.
- Use Livewire class components and Blade views for user-facing workflows.
- Do not introduce Filament, Livewire Volt, admin/staff panels, Inertia, React, Vue, or SPA routing.
- Put new services under `app/Services/<Domain>/`; do not create root-level service files directly under `app/Services/`.

## Query-Sensitive Changes

For any change involving filters, lists, dashboards, widgets, or relationship data:

1. Inspect schema and indexes with Boost `database-schema`.
2. Confirm every `where`, `orderBy`, foreign key, and join-like relationship path has a supporting index when data can grow.
3. Use eager loading or aggregate eager loading.
4. Estimate or measure query count before and after.
5. Document caveats in the final response.

## Seed Data

The default local seed path is `DatabaseSeeder`: `GeoSeeder`, `AmenityRuleSeeder`, `MarketplaceDemoSeeder`, then `BulkMarketplaceSeeder`.

- Add new application models to `database/seeders/BulkMarketplaceSeeder.php`; do not create a parallel bulk-data path.
- Reuse existing parent IDs in seed blocks instead of relying on recursive factory defaults.
- Keep full GeoNames imports manual through `GeoNamesFullSeeder`; do not add it back to `DatabaseSeeder`.
- Update [BULK_SEEDING.md](BULK_SEEDING.md), schema docs, and tests when the seed contract changes.
- Verify seed coverage with `php artisan test --compact tests/Feature/DemoSeederTest.php`.

## Testing

Use PHPUnit tests.

```bash
php artisan test --compact --filter=testName
```

Run the narrowest meaningful test first. If a test file was changed, run that file:

```bash
php artisan test --compact tests/Feature/ExampleTest.php
```

Use the full suite when the touched surface is broad:

```bash
php artisan test --compact
```

## Formatting

After PHP edits:

```bash
vendor/bin/pint --dirty --format agent
```

Do not run Pint for Markdown-only changes.

## Final Response For Code Tasks

Use this structure when code was written, reviewed, or refactored:

1. `PROBLEM` - what was wrong or what was built.
2. `SOLUTION` - implementation summary and file placement.
3. `QUERY DELTA` - before/after query count or estimate, if query-related.
4. `REUSABLE SNIPPET` - extracted scope, action, component, trait, or pattern.
5. `BLADE USAGE` - Livewire-to-view data flow, if Blade-related.
6. `LIVEWIRE/FLUX UI` - component and view integration, if applicable.
7. `TESTS` - focused tests run or added.
8. `CAVEATS` - index requirements, cache invalidation, Laravel version notes, or MCP checks.

For documentation-only changes, summarize changed files and verification.

## Pre-Commit Checklist

- [ ] No query inside a loop, Blade view, or Livewire render method.
- [ ] No `app/Http/Controllers/`, controller-backed web route, `resources/views/auth/`, or `resources/views/search/`.
- [ ] No Filament, Livewire Volt, admin/staff panels, Inertia, React, Vue, or SPA routing.
- [ ] No unbounded `Model::all()`.
- [ ] Relationships used by views/tables are eager loaded.
- [ ] Aggregates are calculated with database aggregate loaders, not in loops.
- [ ] HTTP input uses Form Requests.
- [ ] Authorization uses Policies.
- [ ] Routes are named, grouped, and use route model binding.
- [ ] New environment values are in config and `.env.example`.
- [ ] Focused tests pass.
- [ ] PHP files are formatted with Pint.
