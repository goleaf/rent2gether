# Host Listing Creation

Hosts create listings in a mobile-first wizard:

1. Create a property.
2. Add rooms inside that property.
3. Add sleeping places inside each room.
4. Add photos, amenities, rules, and access basics.
5. Run readiness checks.
6. Keep the listing as a draft or publish each ready sleeping place.

`Property` and `Room` are containers. `SleepingPlace` is the rentable unit that becomes searchable and bookable.

When a host creates, duplicates, copies, or bulk-creates a `SleepingPlace`, the system automatically bootstraps its own per-place calendar. The bootstrap creates calendar settings, public calendar days, and availability days for the sleeping place. Hosts use the calendar step to adjust dates, prices, rules, and blocks, not to create the first calendar manually.

The wizard stores incomplete work in `listing_creation_drafts`. Draft data is compact JSON state, not a large Livewire public payload. Hosts can leave the flow and continue later.

Publishing is blocked by required readiness checks. The platform does not need an admin, support, staff, manager, cleaner, or finance panel for this flow.

Readiness is evaluated for the `SleepingPlace` before publication. Required blockers include the container property and room basics, public photos, price, access instructions, check-in and check-out times, cancellation policy, deposit policy, kitchen rules, bathroom rules, and an emergency contact. Host suggestions use the same gaps as friendly next actions so the mobile wizard can show what remains without exposing internal statuses to guests.
