# Extended Sleeping Place Fields

## Purpose

The sleeping place is the rental unit guests actually book:

```text
Property -> Room -> SleepingPlace -> Dates -> Price -> Booking
```

This module describes the exact bed, bunk, sofa, mattress, or capsule: size, mattress, bedding, privacy, socket, lamp, locker, storage, position in the room, noise, light, safety, condition, pricing, and public warnings.

## Table Structure

`sleeping_places` stays compact and keeps list/search fields:

- status, type, subtype, place number, internal host name
- bunk flags, single/double/couple flags, max guests and age limits
- sort order
- base/weekly/monthly/weekend/holiday prices, cleaning fee, deposit, currency
- min/max nights, instant booking, host approval, extension, early/late timing, second guest, cancellation policy

Rich details live in one-to-one tables:

- `sleeping_place_physical_details`: dimensions, ladder, safety rail, max weight, accessibility, frame, squeak level
- `sleeping_place_comfort_details`: mattress, pillow, blanket, bedding, towel, protector, cleanliness flags
- `sleeping_place_storage_details`: suitcase/shoe/backpack space, personal locker, lock, valuables, documents, laptop
- `sleeping_place_position_details`: privacy, curtain, lamp, socket, USB, shelf, hook, room position, noise, light, draft
- `sleeping_place_condition_details`: condition state, component conditions, damage, stains, smell, squeaks, repair flags, check dates
- `sleeping_place_translations`: public localized content for `en`, `ru`, and future locales

Important indexes:

- `sleeping_places`: `property_id + status`, `room_id + status`, `room_id + sort_order`, `sleeping_place_type + status`, `bunk_level + status`, price, booking flags, min/max nights
- detail tables: unique `sleeping_place_id` plus common filters such as dimensions, max weight, tall/heavy suitability, bedding, towel, locker, socket, curtain, near door/window, condition, repair, and last check

## Privacy Rules

Guests may see public facts that affect the booking decision:

- exact bed type and size
- privacy, curtain, socket, lamp, locker, bedding, towel
- room-position warnings such as near door, near passage, morning light, draft
- condition warnings such as squeak, smell, repair, mattress replacement

Guests must not see:

- host-only internal names
- host private condition notes
- private guest data
- exact private context not allowed before booking

`SleepingPlacePrivacyService` decides whether internal names or host notes are visible. Public pages use `SleepingPlaceGuestSummaryService`.

## Host Wizard

The extended host wizard uses Livewire class components only:

- `Host/SleepingPlaces/SleepingPlaceMainInfoStep`
- `Host/SleepingPlaces/SleepingPlacePhysicalStep`
- `Host/SleepingPlaces/SleepingPlaceComfortStep`
- `Host/SleepingPlaces/SleepingPlaceStorageStep`
- `Host/SleepingPlaces/SleepingPlacePositionStep`
- `Host/SleepingPlaces/SleepingPlacePricingStep`
- `Host/SleepingPlaces/SleepingPlaceConditionStep`
- `Host/SleepingPlaces/SleepingPlaceMediaStep`
- `Host/SleepingPlaces/SleepingPlaceCompletionPanel`

Routes live under:

```text
/{locale}/host/sleeping-places/{sleepingPlace}/extended/*
```

Each component stores only the sleeping place ID and compact form fields in public Livewire state. Text uses `wire:model.blur`; toggles/selects use `wire:model.change`.

## Public Display

The listing detail page shows a compact “About this sleeping place” block before the room block. It renders mobile accordions for:

- about the place
- size and safety
- mattress and bedding
- storage
- position in the room
- condition
- safety notes

Empty rows are hidden. Important warnings are callouts. Video and full galleries are not loaded by this module.

Public section components are available for lazy use:

- `Listings/Detail/SleepingPlaceInfoSection`
- `Listings/Detail/SleepingPlaceComfortSection`
- `Listings/Detail/SleepingPlaceStorageSection`
- `Listings/Detail/SleepingPlacePositionSection`
- `Listings/Detail/SleepingPlaceSafetySection`

## Services

- `SleepingPlaceProfileService`: host/guest profile assembly and main info updates
- `SleepingPlacePhysicalService`: physical details, dimensions, physical warnings
- `SleepingPlaceComfortService`: mattress, bedding, towel summaries and warnings
- `SleepingPlaceStorageService`: locker/storage summaries and valuables checks
- `SleepingPlacePositionService`: privacy, amenities, room-position warnings
- `SleepingPlaceConditionService`: condition summaries and repair warnings
- `SleepingPlacePricingService`: pricing-field updates only; final quotes still use the central `PricingService`
- `SleepingPlaceAvailabilityService`: wrapper around the central `AvailabilityService`
- `SleepingPlacePrivacyService`: public/private field decisions
- `SleepingPlaceCompletionService`: readiness checklist and percentage
- `SleepingPlaceGuestSummaryService`: privacy-safe public DTO array

## Translations

UI labels live in:

- `lang/en/sleeping_place.php`
- `lang/ru/sleeping_place.php`

Public sleeping-place content lives in `sleeping_place_translations`. Add languages by adding rows, not columns.

## Performance Rules

- Query selected columns only for public details.
- Eager-load detail rows needed by the public summary.
- Do not query inside Blade.
- Do not load video on first render.
- Do not render all media in the sleeping-place detail block.
- Keep Livewire public properties scalar and small.
- Use detail-table indexes for filterable fields instead of JSON blobs.

## Tests

Primary coverage:

```bash
php artisan test tests/Feature/ExtendedSleepingPlaceFieldsTest.php --compact
```

Covered behavior:

- detail tables, columns, indexes, relationships, and cascade delete
- English/Russian translation fields
- host step updates
- cross-host authorization
- completion percentage
- privacy-safe guest summary
- public listing sleeping-place summary without private host notes
