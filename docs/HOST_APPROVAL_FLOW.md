# Host Approval Flow

Host approval is used when a sleeping place is not instant-bookable or when the quote needs host confirmation.

Flow:

1. Guest creates a valid `BookingQuote`.
2. Guest submits a `BookingRequest`.
3. The request stores price, guest profile, rating, warnings, and compatibility snapshots.
4. If configured, request dates are held with `host_confirmation_pending` date locks.
5. Host approves, rejects, asks a question, proposes changes, or offers an alternative.
6. Approved requests can convert to `Booking` after quote and availability recheck.

Rejected, expired, withdrawn, or cancelled requests release temporary holds.
