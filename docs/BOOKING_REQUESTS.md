# Booking Requests

`BookingRequest` is a host decision step between a valid `BookingQuote` and a confirmed `Booking`.

The rentable unit is still `SleepingPlace`. A request stores the full context:

- `sleeping_place_id`
- `room_id`
- `property_id`
- `guest_user_id`
- `host_user_id`
- optional `booking_quote_id`

Request types:

- `host_approval`
- `stay_request`
- `preliminary_inquiry`
- `long_term_request`
- `same_day_urgent`
- `request_only`

`preliminary_inquiry` does not hold dates. Date-based requests may create `host_confirmation_pending` date locks when `hold_dates` is enabled.

Host responses can approve, reject, ask a question, propose date or time changes, or offer another sleeping place. Guest responses can answer, accept or reject proposals, change the request, or withdraw it.
