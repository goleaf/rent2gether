# Listing Mismatch Refunds

Refunds and compensation are not applied automatically just because a report exists.

Refunds should be created after one of these conditions:

- mismatch is confirmed
- host offers and guest accepts a refund
- serious mismatch leads to guest-friendly cancellation
- dispute or complaint flow confirms the guest could not reasonably stay

Refund rows are created through `BookingRefundService` and linked back to `booking_listing_mismatch_reports.booking_refund_id`.
