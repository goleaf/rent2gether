# Listing Readiness

Readiness checks live in `listing_readiness_checks` and can target a property, room, or sleeping place.

Required checks block publication. Warning checks help hosts improve the listing without creating staff workflows.

Core checks include:

- property title and safe location
- at least one room
- at least one sleeping place
- sleeping place type
- sleeping place price
- sleeping place calendar settings and available dates
- sleeping place photo
- house and room rules
- access instructions
- check-in and check-out time basics from calendar settings or host defaults
- cancellation policy from the sleeping place or host default
- deposit policy, where `0` means the host explicitly chose no deposit and `null` means not filled
- kitchen and bathroom rules
- emergency contact from the property or access details

The publication service publishes the `SleepingPlace` only when required checks pass. Property and room statuses may be updated as container context, but the sleeping place remains the public rentable unit.

Host suggestions mirror the same mobile-first gaps with friendly actions: missing photos, living rules, kitchen and bathroom rules, key pickup, emergency contact, cancellation policy, deposit, cleaning fee, and check-in/check-out times. These suggestions do not create staff, support, moderation, finance, manager, or cleaner workflows.

Host-facing creation flows automatically create the initial calendar per `SleepingPlace`, so readiness should not require a separate manual calendar creation step. Missing calendar checks still protect imported, legacy, or manually damaged records.
