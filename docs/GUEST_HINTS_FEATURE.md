# Automatic Guest Hints

## Purpose

Automatic guest hints help guests quickly understand useful facts about a sleeping place during search, listing review, favorites, saved searches, and booking review.

The module is intentionally honest:

- no fake urgency;
- no private occupant data;
- no claims like "will be booked soon" without real data;
- critical booking warnings remain visible before booking.

## Hint Categories

Hints use short category keys:

- `price`
- `availability`
- `trust`
- `host`
- `room`
- `occupants`
- `rules`
- `safety`
- `compatibility`
- `cancellation`
- `address`
- `discount`

Hint types are `positive`, `warning`, `neutral`, `info`, `urgent`, `discount`, `privacy`, `rule`, and `compatibility`.

Importance levels are `low`, `medium`, `high`, and `critical`.

## Database

The feature stores reusable and user-specific state in three tables:

- `listing_hint_snapshots`
- `guest_hint_dismissals`
- `guest_hint_impressions`

`listing_hint_snapshots` stores calculated listing hints for a sleeping place, property, room, and optional city.

Important indexed fields:

- `sleeping_place_id + category`
- `sleeping_place_id + priority`
- `city_id + category`
- `hint_key`
- `expires_at`
- `show_on_card`
- `show_on_detail`
- `show_before_booking`

`guest_hint_dismissals` stores temporary guest dismissals by hint key, context, and optional sleeping place.

`guest_hint_impressions` stores optional display/click/dismiss tracking.

Because `SleepingPlace` uses soft deletes, the model deletes related hint snapshots, dismissals, and impressions when a sleeping place is deleted.

## Services

Main orchestration:

- `GuestHintService`
- `ListingHintCalculatorService`
- `HintPriorityService`
- `HintVisibilityService`
- `HintDismissalService`

Domain hint services:

- `PriceHintService`
- `AvailabilityHintService`
- `TrustHintService`
- `HostHintService`
- `RoomOccupancyHintService`
- `RulesHintService`
- `SafetyHintService`
- `CompatibilityHintService`
- `CancellationHintService`

DTOs:

- `HintContext`
- `GuestHintData`
- `GuestHintCollectionData`
- `HintPriorityData`

## UI Placement

Livewire class components:

- `Hints/ListingCardHints`
- `Hints/ListingDetailHints`
- `Hints/BeforeBookingHints`
- `Hints/DismissHintButton`
- `Hints/GuestHintsList`
- `Hints/HintDetailsSheet`

Listing cards show at most three compact hints.

Listing detail shows grouped hints inside mobile-friendly accordions.

Before booking shows only important warnings and required notes.

## Privacy Rules

Hints can use aggregate occupant summaries, but must not expose:

- full names;
- phone numbers;
- email addresses;
- private notes;
- complaint details;
- private messages;
- sensitive personal data.

Occupant-related hints use `RoomOccupantSummaryService`.

Compatibility-related hints use guest compatibility data only for the current guest.

## Honesty Rules

Hints must be based on real data:

- `one_place_left` uses real room availability context;
- `people_already_in_room` uses occupant snapshots or room occupancy fields;
- `cheaper_than_area_average` compares with same-city sleeping place prices;
- `deposit_required` uses the sleeping place deposit amount;
- `address_after_booking` uses address visibility rules;
- `strict_quiet_hours` uses room/rule data;
- compatibility mismatch uses the guest compatibility profile.

## Translations

All visible text lives in:

- `lang/en/guest_hints.php`
- `lang/ru/guest_hints.php`

Blade and Livewire components use translation keys only.

## Tests

Covered behavior:

- tables, indexes, relationships, and cleanup;
- snapshot, dismissal, and impression factories;
- dynamic hint calculation;
- expired hints ignored;
- dismissed hints hidden;
- critical before-booking hints still visible;
- card/detail/before-booking Livewire rendering;
- non-critical dismiss action;
- English and Russian copy.
