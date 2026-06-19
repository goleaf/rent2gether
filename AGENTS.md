# AGENTS.md

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
For listings, rooms, sleeping places, rules, amenities, policies, and help text, support translations per locale.

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

## Global constraints prompt block

Add this block to the end of almost every implementation prompt:

Global constraints:
- Laravel 13, PHP 8.3+, Livewire 4, Flux Pro, SQLite.
- Do not use Livewire Volt.
- Do not use Filament.
- Do not use Inertia.
- Do not create admin/staff/moderation/support/finance panels.
- Mobile-first, 320px first.
- Must work well on old Android and slow 3G.
- Keep Livewire public properties small.
- Use translations for every visible string.
- Support en and ru from day one.
- Prepare architecture for future languages.
- Add migrations, models, factories, seeders where needed.
- Add Livewire feature tests and unit tests where needed.
- Add indexes for all filtering/search/calendar queries.
- Run php artisan test, Pint, and npm build.
- Update docs when behavior or schema changes.
