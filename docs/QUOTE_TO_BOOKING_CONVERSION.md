# Quote to Booking Conversion

Updated: 2026-06-21

## Rule

A `BookingQuote` cannot become a booking or request unless it is valid, not expired, recalculated, and availability is rechecked immediately before conversion.

## Service

`App\Services\Bookings\BookingQuoteConversionService`

Methods:

- `convertToBooking()`
- `convertToBookingRequest()`
- `ensureQuoteStillValid()`
- `recheckAvailability()`
- `lockDatesForBookingAttempt()`
- `createSnapshots()`

## Date Locks

Quote preview does not lock dates.

During instant booking conversion, the service creates `payment_pending` quote locks, creates the booking, then converts those locks to active `booked` locks for the booking.

During request conversion, the project convention creates a `Booking` with `awaiting_host_approval` status and `host_confirmation_pending` locks.

The SQLite partial unique index on `sleeping_place_booking_date_locks` remains the database-level protection against double booking.

## Snapshots

Conversion copies:

- quote totals into booking money fields
- quote lines into `booking_price_lines`
- quote timeline dates into `booking_timeline_dates`
- sleeping place, room, property, guest, and host IDs into the booking

Old quotes must not be silently reused. Expired quotes require recalculation before a guest can continue.
