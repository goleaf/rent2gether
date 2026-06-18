# AGENTS.md - rent2gether

This is the canonical instruction file for AI agents working in this repository.
Follow it before local preference, generated defaults, or framework guesses.

## Project Snapshot

- Project: `rent2gether`
- Current app state: Laravel baseline with `User`, SQLite, cache, jobs, sessions, tests, Livewire 4, Flux UI Pro, shared app layout, and a Flux-backed welcome screen.
- Runtime: Laravel Herd serves the app at the project `.test` domain. Do not start a local web server unless the user explicitly asks.
- Verified by Laravel Boost on 2026-06-18:
  - PHP `8.5`
  - Laravel `13.16.1`
  - Laravel Boost `2.4.10`
  - Laravel MCP `0.8.1`
  - Laravel Pail `1.2.7`
  - Laravel Pint `1.29.3`
  - Livewire `4.3.1`
  - Flux UI `2.14.1`
  - Flux UI Pro `2.14.1`
  - PHPUnit `12.5.30`
  - Tailwind CSS `4.3.1`
  - Database engine: SQLite
- Planned/allowed stack: Laravel, Filament admin, Blade server-side rendering, Eloquent-only query layer.
- Frontend rule: Blade only. Do not introduce React, Vue, Inertia, or a SPA framework without explicit approval.
- Component system: Blade + Tailwind CSS v4 + Livewire 4 + Flux UI Pro.

## Hard Rules

- Never write raw SQL strings anywhere in the codebase.
- Never use `DB::select()`, `DB::statement()`, or `DB::raw()` outside a model's internal scope.
- Never query inside Blade views, `@foreach`, or `@if` blocks.
- Never call `count()`, `sum()`, or any aggregate inside a loop.
- Never use `Model::all()` without a limit, scope, or pagination.
- Never add a new query when an eager-loaded relationship already covers the data.
- Never duplicate controller logic. Extract reusable behavior to model scopes, actions, services, or policies.
- Never put business logic in Blade templates or Filament resources directly.
- Never register routes in `routes/web.php` without middleware, prefix, and name grouping.
- Never store secrets, API keys, or credentials in code. Use `.env` plus `config()`.
- Never access `env()` outside config files.
- Never change dependencies without explicit approval.
- Never create documentation files unless the user asks for documentation. This file set was created because the user explicitly asked for project Markdown guidance.

## Laravel Boost And MCP

- Use Laravel Boost tools for this application whenever they apply.
- On each new implementation session, call Boost `application_info` before version-specific work.
- Use Boost `search-docs` before code changes that depend on Laravel ecosystem APIs.
- Use Boost `database-schema` before writing migrations, models, scopes, filters, or relationship-heavy queries.
- Use Boost `database-query` only for read-only inspection.
- Use Boost `get-absolute-url` before sharing project URLs.
- Use Boost `browser-logs` for recent browser-side errors.
- Run Artisan commands directly with `--no-interaction` where supported.
- Prefer tests over one-off verification scripts or tinker snippets.

## Architecture Rules

- Controllers stay thin and delegate to actions, services, form requests, policies, events, listeners, and jobs.
- Business logic belongs in `App\Actions\`, `App\Services\`, model methods/scopes, policies, observers, jobs, events, or listeners.
- Validation belongs in Form Requests for HTTP inputs.
- Authorization belongs in policies. Do not hide sensitive actions only in the UI.
- JSON responses use API Resources.
- Side effects should be decoupled with events/listeners.
- Work that may take more than 200 ms belongs in a queued job.
- Create framework files with `php artisan make:* --no-interaction` when possible.
- Follow sibling file conventions before introducing a new pattern.

## Eloquent And Query Rules

- Use Eloquent models and relationships only.
- Use `with()`, `load()`, and `loadMissing()` to prevent N+1 queries.
- Use `withCount()`, `withSum()`, `withAvg()`, `withMin()`, `withMax()`, and `withExists()` for aggregates.
- Prefer model scopes for repeated constraints such as `active`, `verified`, tenant, visibility, region, or status filters.
- Scope methods must do one thing and chain cleanly.
- Select the required columns intentionally inside scopes and query builders; avoid accidental payload bloat.
- Use pagination for user-facing collections.
- Use `cursorPaginate()` for large append-only data, `simplePaginate()` when total counts are not needed, and regular pagination only when totals matter.
- Use `chunkById()`, `lazyById()`, `lazy()`, or `cursor()` for large background iteration.
- Add indexes in migrations for foreign keys and frequently filtered or ordered columns.
- If a query can touch more than 10k rows, inspect schema/indexes and use explain tooling before shipping.

## Blade Rules

- All Blade data must arrive preloaded from the controller, view model, DTO, view composer, or Livewire/Filament layer.
- Blade templates contain presentation only.
- Use anonymous Blade components for reusable UI and class-based components only when meaningful PHP logic is needed.
- Put `@props(...)` at the top of every component.
- Use `@forelse` instead of bare `@foreach` for user-facing lists.
- Use `@csrf` on all POST, PUT, PATCH, and DELETE forms.
- Use method spoofing for PUT, PATCH, and DELETE forms.
- Use `old()` and `$errors->first('field')` for validation feedback.
- Do not call unloaded relationships or model methods from loops.

## Flux Pro Rules

Flux Pro is installed from the local `_data/flux-pro` Composer path repository. When a task mentions Flux, Flux Pro, Livewire UI components, or Laravel component system work:

- Use the `fluxui-development` project skill.
- Read [docs/flux-pro-integration.md](docs/flux-pro-integration.md) and [docs/component-system.md](docs/component-system.md).
- Do not run `php artisan flux:activate` or create `auth.json` unless switching from the local path repository to official Composer authentication.
- Do not commit `auth.json`, Flux license material, or `_data/flux-pro`.
- Prefer Flux primitives over custom Tailwind markup for standard UI controls after Flux is installed.
- Publish only the Flux components that require project-level customization.

## Filament Rules

Filament is part of the target stack, but the package is not currently installed in this checkout. When it is introduced:

- Keep resource forms and tables in dedicated schema methods/classes.
- Override `getEloquentQuery()` in every resource to apply default scopes and eager loads.
- Eager load relationships used by table columns.
- Use relationship columns for belongs-to labels, never raw foreign key IDs.
- Use filters and actions that push work to the database/query layer.
- Add `->authorize()` to every action and bulk action.
- Use confirmation modals for destructive work.
- Use Filament notifications for feedback.
- Do not run raw queries in widgets. Use scoped and cached model aggregate methods.
- Scope resource queries to the current tenant when multi-tenancy exists.

## Routing Rules

- Routes must be grouped by middleware, prefix, and name prefix.
- Use named routes.
- Use route model binding rather than manual `find()` calls.
- Use scoped bindings for nested resources.
- Keep `web.php` for Blade/web routes and `api.php` for JSON APIs.

## Models, Migrations, And Config

- Every model must define fillable fields or explicit guarded behavior; this project currently uses Laravel 13 attributes such as `#[Fillable]` and `#[Hidden]`.
- Define casts for booleans, dates, enums, JSON, and sensitive values.
- Use PHP enums for status fields.
- Use factories for seeders and tests.
- Use observers for audit logs, cache busting, notifications, and other cross-cutting model behavior.
- One concern per migration.
- Always define reversible migrations unless the migration explicitly documents why it is irreversible.
- Never modify migrations that may already have run in a shared/production environment.
- Every new `.env` key needs `config/*` support and an `.env.example` entry.

## Testing And Formatting

- Tests must be PHPUnit classes.
- Create tests with `php artisan make:test --phpunit`.
- Prefer feature tests for HTTP behavior and unit tests for action/service/model logic.
- Use factories, not manual inserts.
- Use fakes for external HTTP, events, notifications, queues, and mail.
- Run the focused relevant tests before finalizing a code change.
- Run `vendor/bin/pint --dirty --format agent` after modifying PHP files.
- For this project, the standard full suite command is `php artisan test --compact`.

## Required Response Format For Code Tasks

When writing, reviewing, or refactoring code, structure the final response with:

1. `PROBLEM` - what is wrong or what is being built.
2. `SOLUTION` - implementation summary and file placement.
3. `QUERY DELTA` - before/after query count or estimate, if query-related.
4. `REUSABLE SNIPPET` - extracted scope, action, component, trait, or reusable pattern.
5. `BLADE USAGE` - controller-to-view data flow, if Blade-related.
6. `FILAMENT INTEGRATION` - resource/widget/action integration, if applicable.
7. `TESTS` - focused tests run or added.
8. `CAVEATS` - index requirements, cache invalidation, version notes, or MCP checks.

For tiny non-code tasks, keep the response short and explain only what changed.

## Pre-Commit Self-Check

- [ ] No query inside a loop, Blade view, or Filament renderer.
- [ ] No accidental `SELECT *` where a focused select is appropriate.
- [ ] No unbounded `Model::all()`.
- [ ] Relationships used by views/tables are eager loaded.
- [ ] HTTP input is validated by Form Requests.
- [ ] Filament actions and bulk actions are authorized.
- [ ] New routes are named, grouped, and use route model binding.
- [ ] New environment values are represented in config and `.env.example`.
- [ ] Focused tests pass.
- [ ] Query count and index impact are checked for query-sensitive changes.

## Project Docs

- Read [README.md](README.md) for setup and command basics.
- Read [docs/architecture.md](docs/architecture.md) for the current architecture map and file placement conventions.
- Read [docs/component-system.md](docs/component-system.md) for Blade/Tailwind/Flux component rules.
- Read [docs/development-workflow.md](docs/development-workflow.md) for implementation workflow, MCP usage, and verification.
- Read [docs/flux-pro-integration.md](docs/flux-pro-integration.md) before installing or changing Flux.
- Read [docs/decisions/ADR-001-laravel-blade-eloquent-architecture.md](docs/decisions/ADR-001-laravel-blade-eloquent-architecture.md) before changing the framework, frontend, query layer, or admin stack.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- livewire/flux (FLUXUI_FREE) - v2
- livewire/flux-pro (FLUXUI_PRO) - v2
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
