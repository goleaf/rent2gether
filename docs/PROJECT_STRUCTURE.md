# Project Structure

This file is the placement map for `rent2gether`. Read it before creating files. If a new prompt asks for a feature, extend the existing surface listed here instead of creating a parallel folder or resurrecting a deleted layer.

## Current Rule

The application is Livewire-first and controllerless for web UI.

Do not create:

- `app/Http/Controllers/`
- root-level Blade page files under `resources/views/*.blade.php`
- `resources/views/auth/`
- `resources/views/beds/`
- `resources/views/search/`
- controller-backed web routes
- Filament resources, panels, widgets, or admin pages
- Livewire Volt components
- Inertia, React, or Vue pages

The existing `app/Http/` surface is limited to middleware and form requests. User-facing pages belong in Livewire class components. Page and feature views must live under `resources/views/livewire/...`; `resources/views/components/...` and `resources/views/layouts/...` are support surfaces for Livewire layouts and reusable Blade components only.

## Route And Page Placement

| Feature surface | Route target | PHP class location | Blade view location |
| --- | --- | --- | --- |
| Home and utility pages | Livewire page route | `app/Livewire/Pages/` | `resources/views/livewire/pages/` |
| Auth pages | Livewire page route | `app/Livewire/Auth/` | `resources/views/livewire/auth/` |
| Account and profile | Livewire page route | `app/Livewire/Account/`, `app/Livewire/Profile/` | `resources/views/livewire/account/`, `resources/views/livewire/profile/` |
| Public search | `search.index` | `app/Livewire/Search/SleepingPlaceSearch.php` | `resources/views/livewire/search/` |
| Legacy bed detail bridge | `beds.show` | `app/Livewire/Beds/ShowBed.php` | `resources/views/livewire/beds/show-bed.blade.php` |
| Canonical sleeping-place detail | `places.show` | `app/Livewire/Places/ShowSleepingPlace.php` | `resources/views/livewire/places/` |
| Guest booking flow | Livewire page routes | `app/Livewire/Booking/`, `app/Livewire/Bookings/`, `app/Livewire/Trips/` | Matching `resources/views/livewire/...` folders |
| Host listings | Livewire page routes | `app/Livewire/Host/Listings/`, `app/Livewire/Host/Properties/`, `app/Livewire/Host/Rooms/`, `app/Livewire/Host/SleepingPlaces/` | Matching `resources/views/livewire/host/...` folders |
| Host calendar, requests, income, occupants | Livewire page routes | `app/Livewire/Shell/`, `app/Livewire/Host/`, `app/Livewire/Host/Calendar/`, `app/Livewire/Host/Occupants/` | Matching `resources/views/livewire/...` folders |
| Favorites, saved searches, waitlist, compare | Livewire page/components | `app/Livewire/Favorites/`, `app/Livewire/SavedSearches/`, `app/Livewire/Waitlist/`, `app/Livewire/Compare/` | Matching `resources/views/livewire/...` folders |
| Messages, notifications, reviews, complaints | Livewire page/components | `app/Livewire/Messages/`, `app/Livewire/Notifications/`, `app/Livewire/Reviews/`, `app/Livewire/Complaints/` | Matching `resources/views/livewire/...` folders |

When adding a page:

1. Add a Livewire class component under the closest existing `app/Livewire/...` folder.
2. Add its Blade view under the matching `resources/views/livewire/...` folder.
3. Register the route in `routes/web.php` as a named Livewire route inside the existing locale/auth/host grouping.
4. Keep route model binding in the Livewire `mount()` method.
5. Put behavior in actions/services and keep Blade presentation-only.

## Service Placement

All services are grouped by domain under `app/Services/<Domain>/`. Do not add new PHP files directly under `app/Services/`.

| Domain | Location |
| --- | --- |
| Availability | `app/Services/Availability/` |
| Booking core, cancellation, refunds, extensions | `app/Services/Bookings/` |
| Booking guest intake | `app/Services/BookingGuestIntake/` |
| Calendar rules, pricing, cleaning gaps, bootstrap | `app/Services/Calendar/` |
| Check-in and check-out | `app/Services/CheckIn/`, `app/Services/CheckOut/` |
| Compatibility | `app/Services/Compatibility/` |
| Complaints | `app/Services/Complaints/` |
| Domain ownership | `app/Services/Domain/` |
| Favorites | `app/Services/Favorites/` |
| Geo search/import helpers | `app/Services/Geo/` |
| Guest and host hints | `app/Services/Hints/`, `app/Services/HostHints/` |
| Host bulk actions | `app/Services/HostBulk/` |
| Host calendar, cleaning, income, occupants | `app/Services/HostCalendar/`, `app/Services/HostCleaning/`, `app/Services/HostIncome/`, `app/Services/HostOccupants/` |
| Host listing creation/readiness | `app/Services/HostListings/` |
| Listing cards/details/publication | `app/Services/Listings/` |
| Localization | `app/Services/Localization/` |
| Media | `app/Services/Media/` |
| Messaging and notifications | `app/Services/Messaging/`, `app/Services/Notifications/` |
| Occupants and privacy | `app/Services/Occupants/`, `app/Services/Privacy/` |
| Pricing | `app/Services/Pricing/` |
| Properties, rooms, sleeping places | `app/Services/Properties/`, `app/Services/Rooms/`, `app/Services/SleepingPlaces/` |
| Reviews | `app/Services/Reviews/` |
| Saved searches and waitlist | `app/Services/SavedSearches/`, `app/Services/Waitlist/` |
| Users and view state | `app/Services/Users/`, `app/Services/ViewState/` |

If a domain already has a service, extend it or add a sibling in the same folder. Do not create duplicate root-level services such as `app/Services/PricingService.php` or `app/Services/AvailabilityService.php`.

## Action Placement

Actions live under `app/Actions/<Domain>/` when they represent a single application operation:

- account actions: `app/Actions/Account/`
- booking actions: `app/Actions/Bookings/`
- decision tool actions: `app/Actions/DecisionTools/`
- geo actions: `app/Actions/Geo/`
- media actions: `app/Actions/Media/`
- payment actions: `app/Actions/Payments/`
- room and sleeping-place actions: `app/Actions/Rooms/`, `app/Actions/SleepingPlaces/`

Use actions for state changes that should stay testable and reusable across Livewire components.

## Model And Data Placement

| Concern | Location |
| --- | --- |
| Eloquent models | `app/Models/` |
| Shared model concerns | `app/Models/Concerns/` |
| Enums | `app/Enums/` |
| DTO/data objects | `app/Data/` |
| Named Eloquent query classes | `app/Queries/<Domain>/` |
| Support helpers | `app/Support/` |
| Policies | `app/Policies/` |
| Form requests | `app/Http/Requests/` |
| Factories | `database/factories/` |
| Seeders | `database/seeders/` |
| Bulk marketplace seed contract | `database/seeders/BulkMarketplaceSeeder.php` |
| Migrations | `database/migrations/` |
| Translation files | `lang/en/`, `lang/ru/` |
| Seed documentation | `docs/BULK_SEEDING.md` |

Simple reusable constraints belong in Eloquent scopes, services, actions, or model relationships. Important reusable database use cases with business meaning may live in `app/Queries/<Domain>/` as focused query classes. Query classes must have one public `handle()` method, should usually return an Eloquent `Builder` when callers need to choose pagination or execution, and must not become generic repositories. Blade views must not query.

## Model Method Documentation

Every class-level method in `app/Models/*.php` must have a short English PHPDoc summary directly above the method.

Relationship methods need to explain the purpose of the relationship in human language, not only repeat the relation type. For example, describe whether the method links the model to its owner, lists child records, fetches a detail record, connects through a pivot table, or attaches polymorphic media/notifications.

Keep model comments concise. They should help future humans and agents understand the domain reason for the method without duplicating the method body.

## Seeder Placement

`DatabaseSeeder` is the default local dataset entry point. It seeds lightweight geo/catalog/demo foundations first, then runs `BulkMarketplaceSeeder` so every Eloquent model in `app/Models/*.php` has at least 1000 rows.

When a new model is added, extend the closest existing block in `BulkMarketplaceSeeder` and override recursive factory foreign keys with already-created parent IDs. Do not create a second bulk seeder, a root-level seed folder, or a one-off script that bypasses the documented seed contract.

`GeoNamesFullSeeder` is manual only because it can import millions of city rows. Keep it out of `DatabaseSeeder`; run it explicitly when a full offline geo catalog is needed.

## Deleted Legacy Surfaces

These paths were intentionally removed. Do not recreate them:

- `app/Http/Controllers/Auth/LoginController.php`
- `app/Http/Controllers/Auth/RegisterController.php`
- `app/Http/Controllers/BedController.php`
- `app/Http/Controllers/ProfileIndexRedirectController.php`
- `app/Http/Controllers/SearchController.php`
- `app/Http/Controllers/Host/*`
- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- `resources/views/beds/show.blade.php`
- `resources/views/search/index.blade.php`
- `resources/views/welcome.blade.php`

Replacement surfaces:

- login/register: `app/Livewire/Auth/LoginPage.php`, `app/Livewire/Auth/RegisterPage.php`
- logout: `app/Livewire/Auth/LogoutButton.php`
- search: `app/Livewire/Search/SleepingPlaceSearch.php`
- legacy bed detail: `app/Livewire/Beds/ShowBed.php` with `resources/views/livewire/beds/show-bed.blade.php`
- profile index/edit: `app/Livewire/Profile/EditProfile.php`

The architecture regression tests are `Tests\Feature\FoundationPointOneArchitectureTest::test_http_controller_surface_has_been_removed_for_livewire_pages` and `Tests\Feature\FoundationPointOneArchitectureTest::test_view_surface_is_limited_to_livewire_views_and_support_layouts`.
