# Listing Mismatch Snapshot Compare

Mismatch comparison uses the booking-time listing snapshot stored under `booking.nightly_price_snapshot['_snapshots']['listing']`.

The system must not compare against the current listing because the host can edit the listing after booking. Snapshot comparison checks promised features such as Wi-Fi, lockers, bedding, bed type, bunk level, people count, address, kitchen access, and self check-in.

If the snapshot shows the feature was promised, confidence increases and the report receives `claimed_missing_amenity_was_listed`. If the feature was not promised, the report is still allowed, but receives `claimed_feature_was_not_listed`.
