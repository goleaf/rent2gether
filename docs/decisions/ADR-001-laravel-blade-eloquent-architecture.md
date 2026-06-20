# ADR-001: Use Laravel, Livewire Class Components, Blade, Flux, and Eloquent

## Status

Accepted

## Date

2026-06-18

## Context

`rent2gether` is starting from a clean Laravel baseline. The project needs durable conventions before domain code is added so future changes do not drift into inconsistent architecture.

Important constraints:

- The app should remain a Laravel application using framework conventions.
- The frontend should be server-side rendered with Blade and enhanced through Livewire class components.
- The query layer should be Eloquent models and relationships only.
- Flux Pro is the UI component system.
- No Filament, Livewire Volt, Inertia, React/Vue SPA, admin panel, or staff tooling should be introduced.
- Agents should use Laravel Boost MCP for version-aware docs, schema inspection, logs, and URL generation.
- Query safety matters from the start: no N+1s, no unbounded reads, no business logic in templates, and no raw SQL strings.
- The system is built around the loop: guest chooses city, dates, and sleeping place; system calculates availability, nights, calendar days, price, discount, deposit, rules, and compatibility; host controls property, rooms, sleeping places, calendar, price, rules, and requests.

## Decision

Use Laravel 13 conventions with Livewire class components, Blade-rendered views, Flux Pro UI, and Eloquent-only data access.

The project will:

- Prefer Livewire class components for user-facing interaction.
- Use actions/services for business behavior.
- Use validation rules, form objects, or Form Requests where appropriate.
- Use Policies for authorization.
- Use Eloquent relationships, scopes, eager loading, and aggregate loaders for data access.
- Use Blade and Flux components for reusable server-rendered UI.
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

### Add Filament or an admin/staff panel now

- Pros: Strong CRUD tooling for back-office workflows.
- Cons: The product explicitly forbids admin/staff surfaces for now and should focus on guest/host marketplace flows.
- Rejected until the user explicitly changes the project boundary.

### Put business logic directly in Livewire render methods or Blade templates

- Pros: Faster for prototypes.
- Cons: Duplicates behavior, makes testing harder, and hides authorization/performance problems in presentation code.
- Rejected because behavior should live in actions, services, policies, models, jobs, events/listeners, or observers.

## Consequences

- New domain behavior should be testable without rendering Blade.
- Query-heavy features need schema/index inspection before implementation.
- Future staff/admin work, if explicitly requested later, can share the same actions, policies, scopes, and validation rules as guest/host web flows.
- Documentation must stay factual: this ADR describes current architectural intent, not implemented domain features.
