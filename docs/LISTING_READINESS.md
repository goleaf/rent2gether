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
- cancellation and deposit policy fields when the feature is enabled

The publication service publishes the `SleepingPlace` only when required checks pass. Property and room statuses may be updated as container context, but the sleeping place remains the public rentable unit.

Host-facing creation flows automatically create the initial calendar per `SleepingPlace`, so readiness should not require a separate manual calendar creation step. Missing calendar checks still protect imported, legacy, or manually damaged records.
