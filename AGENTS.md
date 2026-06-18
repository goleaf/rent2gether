# AGENTS.md - rent2gether

This is the canonical instruction file for AI agents working in this repository.
Follow it before local preference, generated defaults, or framework guesses.

## Project identity

This project is a mobile-first Laravel 13 + Livewire 4 + Flux Pro marketplace for renting individual sleeping places inside rooms and properties.

The user can be:
- Guest
- Host
- Guest + Host

Do not build any admin panel yet.
Do not create administrator, moderator, support, finance, cleaner, helper, or property manager areas.
Do not use Filament.
Do not use Livewire Volt.
Do not use Inertia.
Do not build a SPA with Vue/React.
Use Livewire class components and Blade views.

## Core stack

- Laravel 13
- PHP 8.3+
- Livewire 4
- Flux Pro
- Tailwind CSS
- SQLite
- Laravel localization
- Laravel migrations, seeders, factories, policies, form validation, tests

## Product goal

Build a friendly mobile website where:
- Hosts can create properties, rooms, sleeping places, prices, rules, calendars, media, and availability.
- Guests can search, filter, compare, favorite, request, book, pay, check in, extend, check out, review, complain, and manage trips.
- The main rental unit is a sleeping place, not a whole apartment.
- The system automatically calculates nights, calendar days, price, discounts, deposit, fees, refund estimates, and availability.
- The system supports Russian and English from day one and can add more languages later.

## Mandatory mobile-first rules

Design every page first for 320px–430px wide screens.
The UI must work on old Android devices, including Samsung S4-class devices.
Assume slow 3G.
Avoid large DOM trees.
Avoid heavy JS.
Avoid client-side rendering frameworks.
Avoid large modal stacks.
Avoid huge select lists.
Avoid loading maps, galleries, or filters until needed.
Prefer progressive disclosure, drawers, bottom sheets, accordions, and step-by-step forms.
Use Flux components where practical.
Keep tap targets large.
Keep forms short per step.
Show skeletons and loading states for every network action.
Use Livewire data-loading styling and wire:loading where appropriate.
Use wire:navigate for internal navigation where it improves perceived speed.
Use lazy loading for below-the-fold Livewire components.

## Livewire rules

Use Livewire class components.
Do not use Livewire Volt.
Keep public properties small.
Never store huge arrays in Livewire public properties.
Store IDs, filters, and compact state only.
Use computed properties for derived data.
Use form objects or dedicated component state when useful.
Use wire:model.blur for most text fields.
Use wire:model.change for selects, checkboxes, radios.
Use wire:model.live.debounce.500ms or slower only for search fields that need live results.
Never use live typing updates for long textareas.
Use pagination or cursor pagination for lists.
Use URL query state for search filters that should be shareable.
Use events carefully and keep component boundaries simple.
Use WithFileUploads only for upload components.
Validate every action server-side.
Show friendly validation errors in the active locale.

## SQLite rules

SQLite is the selected database.
Use migrations for all schema.
Use foreign keys.
Use indexes for every search, filter, join, calendar lookup, booking lookup, and translation lookup.
Use composite indexes for common queries.
Use cursor pagination for large datasets where possible.
Avoid offset pagination for very large search result pages.
Avoid N+1 queries.
Use eager loading with selected columns.
Use query scopes.
Use EXPLAIN QUERY PLAN for critical queries.
Enable WAL mode in local/dev setup documentation where appropriate.
Keep seeders realistic but not enormous by default.

## Localization rules

The app must support at least:
- English: en
- Russian: ru

Every UI string must use translation keys.
No hard-coded visible text in Blade or Livewire components.
Support future languages without schema rewrites.
Use localized routes or locale middleware.
Store user locale preference.
Allow switching language on mobile.
Use fallback locale when a translation is missing.
Translatable user-generated content must be stored separately from base records.
For listings, rooms, sleeping places, rules, amenities, and help text, support translations per locale.

## Geo data rules

Countries and cities must come from open data sources, not manually typed lists.
Use offline imports into SQLite, not live API calls during search.
Use GeoNames for cities and populated places.
Use ISO 3166-compatible country codes.
Use Natural Earth only if map/country shape data is needed.
Use Nominatim/OpenStreetMap only for occasional geocoding with respect for usage limits; do not bulk-geocode through public Nominatim.

## Friendly UX rules

The system tone must be calm, simple, and helpful.
Avoid scary technical messages.
Every empty state must explain the next action.
Every error must explain how to fix it.
Every booking calculation must be transparent.
Every price must show what is included and what is refundable.
Every rule must be visible before booking.
Every important action must have a confirmation step.

## Testing rules

Every feature must include tests.
Use feature tests for routes and Livewire components.
Use unit tests for pricing, availability, date calculation, refund calculation, and compatibility scoring.
Use factories and seeders.
Run:
- php artisan test
- ./vendor/bin/pint
- npm run build

## Prohibited for now

Do not build:
- Admin dashboard
- Moderator tools
- Support staff tools
- Finance staff tools
- Cleaner tools
- Property manager tools
- Filament resources
- Livewire Volt components
- Inertia pages
- React/Vue frontend

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
