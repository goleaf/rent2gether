# Users and profiles

The account layer supports only three role modes:

- `guest`
- `host`
- `guest_host`

There are no `admin`, `support`, `moderator`, `staff`, `manager`, `cleaner`, or `finance` roles in the product surface.

`users` stores authentication and account flags. `user_profiles` stores shared public profile fields. `guest_profiles` stores stay and search preferences. `host_profiles` stores host-facing public display and operational defaults. User languages, privacy, saved preferences, verifications, documents, and activity summaries stay in separate tables so sensitive data does not leak into booking/request views.

The same user may search and book as a guest while also creating properties, rooms, and sleeping places as a host.
