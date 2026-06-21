# Booking Extensions

Booking extension is the flow where an active guest stays longer on the same
`sleeping_place_id`.

Extension is tied to:

- `booking_id`
- optional `booking_stay_id`
- `guest_user_id`
- `host_user_id`
- `property_id`
- `room_id`
- `sleeping_place_id`

The flow starts from a stay page, a checkout page, or a direct booking extension
page. The guest selects a new checkout date, and the system creates a
`booking_extensions` record with price lines, validation results, temporary
date holds, status logs, and events.

The extension can require host confirmation, payment, or both. The original
booking dates and stay dates are updated only after the extension is approved
and paid when payment is required.

Extension is not relocation. It must never change `sleeping_place_id`.

## Lifecycle

1. Guest requests a new checkout date.
2. System validates only `[current_check_out_date, new_check_out_date)`.
3. System creates quote lines for additional nights only.
4. System creates temporary extension date locks.
5. Host approves when confirmation is required.
6. Guest pays when payment is required.
7. System reapplies availability checks.
8. System updates booking, stay, checkout, calendar locks, reminders, deposit
   review timing, review request timing, and occupancy snapshots.

No cron, queue, provider, admin panel, finance panel, support panel, manager
role, or cleaner role is required for the MVP.
