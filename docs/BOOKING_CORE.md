# Booking Core

Booking is the confirmed booking record for one concrete `SleepingPlace`.

Quote and request records are temporary or decision records:

- `BookingQuote` stores price and availability preview.
- `BookingRequest` stores host approval workflow.
- `Booking` stores the real stay lifecycle, locks, payment state, verification state, snapshots, check-in, checkout, complaints, reviews, and closure state.

Bookings always store `sleeping_place_id`, plus `room_id`, `property_id`, `guest_user_id`, and `host_user_id` as context. Group bookings still create one booking per sleeping place and link those rows through `booking_group_links`.

Creation entry points:

- `BookingCreationService::createInstantBooking()`
- `BookingCreationService::createHostApprovalBooking()`
- `BookingCreationService::createFromApprovedRequest()`
- `BookingGroupService::createGroupBooking()`

Booking creation rechecks the quote/request path, creates date locks through the sleeping-place date lock system, copies price data, creates requirements, records lifecycle events, and writes snapshots.
