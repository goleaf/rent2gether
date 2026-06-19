# Project Rules

## Product boundary

rent2gether is a mobile-first marketplace for renting an individual sleeping place inside a room and property. A user may act as a guest, a host, or both.

The product hierarchy is:

`User -> HostProfile -> Property -> Room -> SleepingPlace -> Availability -> Booking`

Do not add administrator, moderator, support, finance, cleaner, helper, or property-manager workflows.

## Required stack

The audited local stack on 2026-06-18 is:

- PHP 8.5.7, with project support declared as PHP 8.3+
- Laravel 13.16.1
- Livewire 4.3.1 class components
- Flux Pro 2.14.1
- Tailwind CSS 4.3.1
- SQLite
- PHPUnit 12

Keep Blade server-rendered. Do not introduce Volt, Filament, Inertia, React, Vue, or another SPA layer.

## Architecture

- Keep Livewire public state compact; store IDs and filters rather than full models or large arrays.
- Put booking, pricing, availability, cancellation, and compatibility logic in testable actions or services.
- Keep database access out of Blade views.
- Use Eloquent relationships, scopes, selected columns, eager loading, and pagination.
- Keep translated public content in indexed translation tables instead of adding one column per language.
- Keep media metadata in the database and physical files in configured storage.
- Use policies and server-side validation for every state-changing action.

## Mobile UI

- Design at 320px first and verify through 430px before adding wider layouts.
- Use one-column content, large tap targets, compact forms, and sticky primary actions.
- Prefer Flux components, progressive disclosure, drawers, and accordions.
- Do not load maps, large galleries, or long lookup lists on the initial page.
- Add `wire:loading` or Livewire 4 `data-loading` styling to every network action.
- Use translation keys for all visible copy, accessible labels, validation, notifications, and emails.

## Delivery checks

Before considering a slice complete, run:

```bash
php artisan test
./vendor/bin/pint
npm run build
```

Also inspect `php artisan route:list --except-vendor` and confirm there are no admin routes or Volt components.
