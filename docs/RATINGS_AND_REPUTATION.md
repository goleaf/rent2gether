# Ratings And Reputation

Ratings are not calculated directly from raw reviews in listing cards or search. Published reviews create rating events and refresh aggregate rows and snapshots.

The main snapshots are:

- `sleeping_place_rating_snapshots`
- `room_rating_snapshots`
- `property_rating_snapshots`
- `host_reputation_snapshots`
- `guest_reputation_snapshots`

Host and guest reputation summaries expose only safe aggregate values. Guest reputation is visible to a host only in a booking or request context.
