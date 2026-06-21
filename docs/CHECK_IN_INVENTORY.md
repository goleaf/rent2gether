# Check-in Inventory

Inventory issued during check-in is recorded on `booking_check_ins` and synchronized with point-10 steps.

Supported issued items in the MVP:

- keys
- door code
- intercom code
- key safe code
- bedding
- towel
- locker

`BookingCheckInInventoryService` updates the relevant booleans and completes matching `booking_check_in_steps`.

This is intentionally lightweight. A future inventory module can attach detailed item records without changing the booking or check-in ownership model.
