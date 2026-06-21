# No-show Policy

`booking_no_show_policies` stores active no-show policy settings per `SleepingPlace`.

Important policy fields:

- `waiting_period_minutes`
- `same_day_waiting_period_minutes`
- `night_arrival_waiting_period_minutes`
- `hold_first_night_on_no_show`
- `release_remaining_nights_after_no_show`
- `refund_deposit_on_no_show`
- `refund_cleaning_fee_on_no_show`
- `refund_service_fee_on_no_show`
- `host_payout_rule`
- `guest_penalty_rule`

`booking_no_show_policy_snapshots` freezes the no-show policy for a booking. No-show calculation uses the snapshot, not the current listing policy, so hosts cannot change terms after booking creation.

The default policy waits 180 minutes, holds the first night, releases remaining nights, refunds deposit and cleaning fee, does not refund service fee, and uses policy-based payout and penalty rules.
