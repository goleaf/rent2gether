# Guest Compatibility Feature

## Purpose

Guest compatibility helps a guest understand whether a specific room and sleeping place fits everyday living needs before booking. It is a soft decision helper, not a discriminatory filter.

Compatibility may consider practical factors:

- smoking and pets
- sleep schedule, quiet hours, and night light rules
- remote work, workspace, Wi-Fi, and sockets
- shared/private room preference and people count
- sleeping-place type, bunk level, locker, curtain, bedding, and towel
- kitchen, washing machine, late entry, and stay length

It must not expose private profile data or make decisions from protected or sensitive personal attributes.

## Tables

`guest_compatibility_profiles` stores the guest's matching answers. It belongs to `users` and is deleted with the user. Important indexed fields include quiet-at-night, remote-worker, workspace, fast Wi-Fi, room people limit, upper-bunk avoidance, locker need, pet allergy, and travelling with pet.

`guest_compatibility_visibility_settings` stores privacy choices. Defaults allow matching but do not expose detailed answers to hosts or future roommates.

`room_compatibility_profiles` stores a compact room profile for matching: shared/private shape, max/current people, noise/light, quiet hours, night work/light, workspace, lockers, smoking, pets, kitchen night use, washing machine, long-stay support, and late entry.

`sleeping_place_compatibility_profiles` stores a compact sleeping-place profile: type, bunk level, sofa/floor mattress flags, curtain, locker, lock, socket, USB, lamp, shelf, luggage space, bedding, towel, privacy/noise/light, mobility suitability, min/max nights, extension, and instant booking.

`compatibility_results` caches date-aware calculation results by user, property, room, sleeping place, selected dates, score, fit status, reason JSON, and expiry.

## Models

- `GuestCompatibilityProfile`
- `GuestCompatibilityVisibilitySetting`
- `RoomCompatibilityProfile`
- `SleepingPlaceCompatibilityProfile`
- `CompatibilityResult`

Relationships are defined from `User`, `Room`, and `SleepingPlace` so services and cards can eager-load compact profiles without Blade queries.

## Services

- `GuestCompatibilityProfileService` creates, updates, completes, and audits missing profile fields.
- `CompatibilityVisibilityService` decides whether matching can use the profile and filters safe host/public display data.
- `RoomCompatibilityProfileService` syncs a compact profile from a room and invalidates room cache.
- `SleepingPlaceCompatibilityProfileService` syncs a compact profile from a sleeping place and invalidates place cache.
- `CompatibilityReasonService` builds positive, warning, and blocking reason DTOs from a context.
- `CompatibilityCalculatorService` calculates score/status, uses cached results, and stores fresh results.
- `CompatibilityCacheService` reads/stores/invalidates cached compatibility results.

Blade and Livewire views must call these services or receive DTO arrays. They must not calculate score or inspect relationships directly.

## Scoring

The calculator starts at 100.

- positive reasons add points
- warning reasons subtract points
- blocking reasons subtract an additional penalty and force `not_suitable`
- final score is clamped from 0 to 100

Fit status:

- `great`: 85-100
- `good`: 70-84
- `attention`: 50-69
- `uncomfortable`: 30-49
- `not_suitable`: 0-29 or any blocking reason

## Blocking Vs Warning

Blocking reasons are important rule conflicts:

- guest travels with a pet but pets are not allowed
- guest has pet allergy and pets are present
- selected stay violates min/max nights
- guest needs self check-in or late/24-7 entry and it is unavailable
- sleeping place is explicitly unsuitable for limited mobility needs

Warnings are practical comfort risks:

- quiet preference with noisy room
- night work but no night light/work support
- smoking mismatch
- night kitchen use not clearly allowed
- upper bunk when guest avoids it
- missing locker, lock, workspace, fast Wi-Fi, socket, bedding, or curtain
- room has more people than the guest prefers

Warnings should help the guest decide; they should not block booking unless the flow explicitly requires confirmation.

## UI

Livewire class components:

- `Profile/GuestCompatibilityProfileForm`
- `Profile/GuestCompatibilityPrivacySettings`
- `Listings/Detail/CompatibilitySummarySection`
- `Listings/Detail/CompatibilityDetailsSheet`
- `Search/CompatibilityFilter`
- `Search/CompatibilityBadge`
- `Bookings/CompatibilityCheckBeforeBooking`

The profile form is a mobile wizard with short steps. Public listing cards show only a compact score badge and up to two warnings. Listing detail lazy-loads the summary and details sheet next to the booking form. Booking pre-check blocks only blocking reasons; warnings can be acknowledged.

## Privacy

Visibility defaults are conservative. Matching may run without exposing raw profile answers. Host display must remove sensitive/private data and should show only safe hints when explicitly allowed.

Never expose:

- phone or email
- exact date of birth or documents
- exact workplace or school
- private notes, internal flags, complaint details, or messages
- full personal profile without permission

## Performance

- Cards use selected columns and eager-load compact room/place compatibility profiles.
- `ListingCardService` caches the current compatibility user inside the service instance to avoid one user query per card.
- Date-aware compatibility results are cached in `compatibility_results`.
- Detail summary and details sheet are lazy Livewire components.
- Do not load full galleries, reviews, bookings, private profiles, or occupant personal data for compatibility.

## Translations

Visible strings live in:

- `lang/en/compatibility.php`
- `lang/ru/compatibility.php`

Reason keys must be translated. Services should return reason keys and translated messages, never raw hard-coded visible copy.

## Tests

Covered by `tests/Feature/GuestCompatibilityFeatureTest.php`:

- table creation, indexes, relationships, and cascade deletion
- room and sleeping-place profile syncing
- calculator scores, warnings, blocking reasons, cache, and invalidation
- perfect match with translated reasons
- Livewire profile, privacy, summary, badge, filter, booking check
- listing card compatibility badge
- English and Russian copy

Run:

```bash
php artisan test tests/Feature/GuestCompatibilityFeatureTest.php
php artisan test tests/Unit/CompatibilityServiceTest.php tests/Feature/ListingCardFeatureTest.php tests/Feature/PublicSleepingPlaceDetailTest.php
./vendor/bin/pint
npm run build
```
