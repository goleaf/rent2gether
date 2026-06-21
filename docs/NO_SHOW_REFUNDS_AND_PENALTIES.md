# No-show Refunds And Penalties

No-show money is calculated separately for:

- deposit refund
- cleaning fee refund
- service fee refund
- guest penalty
- host payout

The deposit is usually refunded when the guest never checked in and no damage happened. No-show alone is not a reason to keep the deposit.

Cleaning fee follows the no-show snapshot. If the guest never stayed, the post-stay cleaning fee can usually be refunded, while preparation costs may depend on policy.

Service fee follows the no-show snapshot. Accommodation penalty and host payout follow no-show policy and can be combined with the booking cancellation snapshot through `BookingNoShowCancellationIntegrationService`.

Confirmed no-show creates a no-show-related cancellation and creates a `booking_refund` when `refund_amount` is greater than zero.
