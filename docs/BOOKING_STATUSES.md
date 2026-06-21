# Booking Statuses

Booking status tracks the stay lifecycle. Payment status remains separate in `payment_status`.

Core statuses include:

- `created`
- `waiting_host_confirmation`
- `waiting_guest_response`
- `waiting_payment`
- `waiting_identity_verification`
- `waiting_document_verification`
- `confirmed`
- `paid`
- `ready_for_check_in`
- `guest_checked_in`
- `stay_in_progress`
- `check_out_soon`
- `guest_checked_out`
- `waiting_property_inspection`
- `waiting_deposit_return`
- `waiting_review`
- `completed`
- `closed`

Failure and exception states include `rejected_by_host`, `cancelled_by_guest`, `cancelled_by_host`, `payment_failed`, `no_show`, `host_unresponsive`, `dispute_opened`, and `frozen_until_dispute_resolved`.

Future-ready statuses such as `cancelled_by_service_future` and `future_support_required` are data states only. They do not create support, staff, moderator, admin, or finance panels.
