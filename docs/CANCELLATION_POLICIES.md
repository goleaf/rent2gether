# Cancellation Policies

`sleeping_place_cancellation_policies` stores the active cancellation policy for a `SleepingPlace`. Supported policy types are `flexible`, `moderate`, `strict`, `non_refundable`, and `custom`.

When a booking is created, the cancellation policy must be copied into `booking_cancellation_policy_snapshots`. Cancellation math uses that snapshot, not the current sleeping-place policy, so guests keep the terms they saw when they booked.

Policy rules live in `sleeping_place_cancellation_policy_rules` and are copied into the snapshot JSON for auditability and future display.
