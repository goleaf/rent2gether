# Development Workflow

Use this workflow for code changes in `rent2gether`.

## Before Changing Code

1. Read [AGENTS.md](../AGENTS.md).
2. Check `git status --short` and preserve user changes.
3. Use Laravel Boost `application_info` for version-specific work.
4. Use Laravel Boost `search-docs` before changing Laravel ecosystem APIs.
5. Use Laravel Boost `database-schema` before migrations, models, scopes, filters, resources, or relationship-heavy queries.
6. Use `fluxui-development` before Flux, Flux Pro, Livewire UI, or Laravel component-system work.
7. Inspect sibling files and follow existing conventions.

## Implementation Rules

- Keep controllers thin.
- Use actions/services for behavior.
- Use Form Requests for validation.
- Use Policies for authorization.
- Use Eloquent scopes and relationships for data access.
- Eager load relationships before views or tables use them.
- Use aggregate eager loaders for counts and sums.
- Use Blade components for reusable UI.
- Keep Blade free of queries and business logic.
- Use Flux components for common UI primitives after Flux Pro is installed.
- Use Filament schemas, resource queries, filters, and actions when Filament is installed.

## Query-Sensitive Changes

For any change involving filters, lists, dashboards, widgets, or relationship data:

1. Inspect schema and indexes with Boost `database-schema`.
2. Confirm every `where`, `orderBy`, foreign key, and join-like relationship path has a supporting index when data can grow.
3. Use eager loading or aggregate eager loading.
4. Estimate or measure query count before and after.
5. Document caveats in the final response.

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
5. `BLADE USAGE` - controller-to-view data flow, if Blade-related.
6. `FILAMENT INTEGRATION` - resource/widget/action integration, if applicable.
7. `TESTS` - focused tests run or added.
8. `CAVEATS` - index requirements, cache invalidation, Laravel version notes, or MCP checks.

For documentation-only changes, summarize changed files and verification.

## Pre-Commit Checklist

- [ ] No query inside a loop, Blade view, or Filament renderer.
- [ ] No unbounded `Model::all()`.
- [ ] Relationships used by views/tables are eager loaded.
- [ ] Aggregates are calculated with database aggregate loaders, not in loops.
- [ ] HTTP input uses Form Requests.
- [ ] Authorization uses Policies.
- [ ] Routes are named, grouped, and use route model binding.
- [ ] New environment values are in config and `.env.example`.
- [ ] Focused tests pass.
- [ ] PHP files are formatted with Pint.
