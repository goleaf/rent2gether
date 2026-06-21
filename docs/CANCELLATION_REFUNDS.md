# Cancellation Refunds

Refund calculation separates accommodation, cleaning fee, service fee, deposit, tax future fields, city-fee future fields, penalty, and host payout impact.

`BookingCancellationPreviewService` creates the preview shown before cancellation. `BookingCancellationService` converts that preview into a final cancellation. `BookingCancellationRefundService` creates `booking_cancellation_refund_lines` and, when needed, a `booking_refund` with `source_type = booking_cancellation`.

Deposits are calculated separately from accommodation. Cleaning fees and service fees follow the snapshot flags. Host cancellations usually produce a full guest refund.
