# Payment Deadlines

Payment deadlines are stored in `booking_payment_deadlines` and mirrored on `booking_payments.payment_deadline_at`.

Deadline types:

- `initial_payment`
- `remaining_balance`
- `deposit_payment`
- `extension_payment`
- `relocation_payment`
- `manual_future`

No cron is required for MVP. `BookingPaymentExpirationService` can be called when a guest or host opens payment, booking, dashboard, calendar, or notification pages.

When a payment expires:

1. payment status becomes `expired`;
2. deadline status becomes `expired`;
3. booking payment status becomes failed;
4. booking lifecycle can move to `payment_failed`;
5. active payment locks are released.

