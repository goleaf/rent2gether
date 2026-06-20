# Sleeping Place Creation

`SleepingPlace` is the main rental unit. Guests search, compare, quote, book, review, and complain about a specific sleeping place.

Sleeping place details are split into focused tables:

- `sleeping_place_physical_details`
- `sleeping_place_comfort_details`
- `sleeping_place_storage_details`
- `sleeping_place_position_details`

Hosts can copy a sleeping place, apply `sleeping_place_templates`, or create several places through `sleeping_place_creation_batches`. Batch creation auto-numbers places inside the room and keeps the relationship chain:

`Host -> Property -> Room -> SleepingPlace`

Every host-created sleeping place receives its own calendar automatically through `SleepingPlaceCalendarBootstrapService`. The calendar belongs to the sleeping place, not to the room or property. Room and property calendar actions may cascade later, but availability is still stored and checked by `sleeping_place_id + date`.

Publishing a sleeping place requires type, price, photo, rules, access basics, and container readiness.
