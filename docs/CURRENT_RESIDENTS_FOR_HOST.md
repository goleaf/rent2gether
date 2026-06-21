# Current Residents for Host

Hosts see current residents only for their own bookings and sleeping places.

`HostCurrentResidentsService` returns cursor-paginated stay cards with selected columns and eager-loaded guest, property, room, and sleeping place context.

Supported filters:

- all active residents
- checkout today
- checkout soon
- open complaints
- payment issue
- extension requested
- relocation requested
- property
- room

The host view may include operational details such as guest name, room, sleeping place, planned checkout, payment state, host notes, complaints, maintenance flags, and extension or relocation flags.

Hosts still do not see private payment provider payloads, guest documents, or unrelated guest data.
