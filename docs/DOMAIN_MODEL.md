# Domain Model

Rent2Gether is a marketplace for renting individual sleeping places inside rooms and properties.

The central hierarchy is:

```text
Host -> Property -> Room -> SleepingPlace
```

`Property` is a container. It stores location, address privacy, building structure, common rules, and shared context.

`Room` is a container inside a property. It stores room format, gender policy, shared comfort details, and room-level rules.

`SleepingPlace` is the main rentable unit. Search results, public listing cards, availability checks, price quotes, booking requests, bookings, stay events, reviews, ratings, complaints, cleaning tasks, maintenance, and inventory must all point to the exact sleeping place whenever the feature is about rental or stay behavior.

The preferred direction for rental workflows is:

```text
SleepingPlace -> Room -> Property -> Host
```

Avoid designing booking, pricing, availability, or stay workflows that begin with `Property` or `Room` as the booked unit. Those models provide context, but the guest books the sleeping place.

Core user modes are:

- `guest`
- `host`
- `guest_host`

Do not create admin, support, moderator, staff, manager, cleaner, or finance roles in this foundation. Future-ready data fields may exist, but no staff/admin panels or workflows should be introduced until explicitly requested.
