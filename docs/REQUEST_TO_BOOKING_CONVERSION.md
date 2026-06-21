# Request To Booking Conversion

An approved request can become a booking only after rechecking the source quote and availability.

Conversion rules:

- request status must be `approved`
- request must belong to the same guest and host context stored on the quote
- temporary request holds are released before booking locks are created
- the source quote is recalculated before conversion
- booking creation reuses `BookingQuoteConversionService`
- price lines, timeline dates, date locks, and price snapshots are created by existing booking services
- request status becomes `converted_to_booking`

Expired, rejected, or withdrawn requests cannot be converted.
