# Booking Snapshots

Booking creation freezes booking-critical data so later host changes do not rewrite history.

Snapshots include:

- price snapshot
- cancellation snapshot
- deposit snapshot
- listing snapshot
- guest intake snapshot
- rules snapshot

The pricing module owns `booking_price_snapshots`. Booking core stores lightweight lifecycle snapshots in `nightly_price_snapshot['_snapshots']` until dedicated snapshot tables are introduced for every domain.

Snapshots protect the guest and host from future changes to price, rules, description, deposit policy, and cancellation policy.
