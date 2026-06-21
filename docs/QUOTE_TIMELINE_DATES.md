# Quote Timeline Dates

Updated: 2026-06-21

## Purpose

`booking_timeline_dates` stores important scheduled dates from the quote before a booking exists. These dates can be copied to the booking during conversion.

## Events

- `payment_deadline`
- `free_cancellation_until`
- `cancellation_penalty_starts`
- `guest_check_in_reminder`
- `host_check_in_reminder`
- `guest_check_out_reminder`
- `host_check_out_reminder`
- `deposit_review_start`
- `host_payout_due`
- `review_request`

## Service

`App\Services\Bookings\BookingTimelineDateService`

Methods:

- `buildForQuote()`
- `copyToBooking()`
- `createNotificationEventsForBooking()`
- `rescheduleForBooking()`

No cron or queue is required for point 5. Due notifications can be processed later by the same service when relevant pages are opened.
