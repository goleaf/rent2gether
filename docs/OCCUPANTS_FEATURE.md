# Current Occupants Feature

## Purpose

The current occupants module helps guests understand the atmosphere of a shared room without exposing private data about other guests. It shows co-living signals such as guest count, broad guest types, quiet/smoking preferences, languages, and safe checkout timing.

The rule is: show the room atmosphere, not people's private lives.

## Privacy Rules

Never expose phone numbers, email addresses, exact birth dates, documents, exact workplace, exact school, private notes, internal flags, complaint details, or private messages.

Before booking, show aggregate information only:

- number of overlapping occupants;
- guest type badges;
- general lifestyle signals;
- languages when allowed;
- quiet/smoking summary;
- compatibility warnings.

After a confirmed booking, show individual roommate cards only for fields allowed by the occupant's visibility settings.

## Tables

`co_living_profiles` stores the user's co-living profile: public alias, age range, optional country/city, languages, guest type, stay purpose, lifestyle fields, smoking/pet signals, quiet preference, cleaning style, and roommate rating.

`co_living_visibility_settings` stores the user's roommate privacy choices. Defaults allow a safe minimum: alias, age range, languages, guest type, sleep schedule, home presence, smoking status, quiet preference, roommate rating, checkout date, pre-booking summary, and after-booking details.

`room_occupant_snapshots` stores a privacy-filtered booking snapshot for fast room atmosphere summaries. The source of truth remains `bookings`; snapshots are refreshed when bookings, profile data, or visibility settings change.

Important indexes:

- `co_living_profiles`: user, country, city, stay purpose, guest type, sleep schedule, home presence, smoking, quiet preference, roommate rating.
- `co_living_visibility_settings`: user.
- `room_occupant_snapshots`: room/status, room/date range, booking, user, sleeping place, status/checkout, before-booking visibility, after-booking visibility.

## Date Overlap

Occupants use the half-open stay range `[check_in_date, check_out_date)`.

A roommate is relevant when their booking starts before the requested checkout and ends after the requested check-in. If one guest checks out on July 15 and another checks in on July 15, they are not roommates unless a future time-based mode says their times overlap.

Cancelled, declined, expired, no-show, checked-out, completed, and closed bookings are not visible occupants.

## Services

- `CoLivingProfileService` creates defaults, updates co-living profiles, returns public profile DTOs, and marks profiles complete.
- `CoLivingPrivacyService` applies before-booking and after-booking privacy rules and always removes sensitive fields.
- `RoomOccupantSnapshotService` creates and refreshes privacy-filtered snapshots from bookings.
- `RoomOccupantSummaryService` builds aggregate pre-booking summaries and confirmed roommate cards.
- `RoommateCompatibilityService` detects quiet, smoking, sleep-schedule, and home-presence conflicts.
- `RoommateRatingService` exposes the public roommate rating.

## Livewire UI

- `Profile/CoLivingProfileForm`
- `Profile/CoLivingPrivacySettings`
- `Listings/Detail/CurrentOccupantsSection`
- `Listings/Detail/RoommateCompatibilitySection`
- `Bookings/ConfirmedRoommatesSection`
- `Host/Rooms/RoomOccupantsPreview`

Components keep public state to IDs, dates, booleans, and short form values. Listing-detail and host previews render compact DTO arrays, not full models.

## Mobile UX

- compact summary first;
- badges for guest types and safe lifestyle signals;
- cards only after confirmed booking;
- no tables;
- no full private profiles;
- privacy note always visible;
- loading states on forms and date-driven components.

## Translation Keys

Visible strings live in:

- `lang/en/occupants.php`
- `lang/ru/occupants.php`

## Tests

Coverage lives in `tests/Feature/CurrentOccupantsFeatureTest.php`:

- table structure, indexes, relationships, cascade deletion;
- snapshot creation from booking;
- half-open date overlap logic;
- pre-booking privacy-safe summaries;
- confirmed roommate cards with allowed fields only;
- hidden fields not rendered;
- co-living profile and privacy forms;
- listing detail, dedicated section, compatibility warnings, and host preview.
