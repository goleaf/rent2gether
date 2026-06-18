# ADR-001: Use Laravel, Blade, Eloquent, and Filament Conventions

## Status

Accepted

## Date

2026-06-18

## Context

`rent2gether` is starting from a clean Laravel baseline. The project needs durable conventions before domain code is added so future changes do not drift into inconsistent architecture.

Important constraints:

- The app should remain a Laravel application using framework conventions.
- The frontend should be server-side rendered with Blade.
- The query layer should be Eloquent models and relationships only.
- Filament is the intended admin-panel layer when installed.
- Agents should use Laravel Boost MCP for version-aware docs, schema inspection, logs, and URL generation.
- Query safety matters from the start: no N+1s, no unbounded reads, no business logic in templates, and no raw SQL strings.

## Decision

Use Laravel 13 conventions with Blade-rendered frontend views, Eloquent-only data access, and Filament conventions for future admin surfaces.

The project will:

- Keep controllers thin.
- Use actions/services for business behavior.
- Use Form Requests for validation.
- Use Policies for authorization.
- Use Eloquent relationships, scopes, eager loading, and aggregate loaders for data access.
- Use Blade components for reusable server-rendered UI.
- Use Filament resources, widgets, actions, filters, and notifications when Filament is introduced.
- Use Laravel Boost MCP before version-sensitive code, schema-sensitive queries, and URL/log inspection.

## Alternatives Considered

### Add React, Vue, Inertia, or a SPA layer

- Pros: Rich client-side interactivity and familiar ecosystems.
- Cons: Adds a second application architecture before the domain requires it.
- Rejected because the project contract explicitly requires Blade server-side rendering only.

### Use query builder or raw SQL for complex queries

- Pros: Can be direct for one-off database work.
- Cons: Violates the project query policy, creates harder-to-review data access, and increases risk of SQL injection or duplicated logic.
- Rejected because Eloquent models and scopes are the durable query boundary.

### Put business logic directly in Filament resources or Blade templates

- Pros: Faster for prototypes.
- Cons: Duplicates behavior, makes testing harder, and hides authorization/performance problems in presentation code.
- Rejected because behavior should live in actions, services, policies, models, jobs, events/listeners, or observers.

## Consequences

- New domain behavior should be testable without rendering Blade or Filament.
- Query-heavy features need schema/index inspection before implementation.
- Future admin work can share the same actions, policies, scopes, and validation rules as web flows.
- Documentation must stay factual: this ADR describes current architectural intent, not implemented domain features.
