# Payment Statuses

`booking_payments.status` tracks the payment record lifecycle:

- `unpaid`
- `waiting_payment`
- `payment_started`
- `pending`
- `partially_paid`
- `paid`
- `failed`
- `expired`
- `cancelled`
- `refunded`
- `partially_refunded`
- `disputed`

`booking_payment_attempts.status` tracks individual attempts:

- `created`
- `started`
- `requires_action`
- `processing`
- `succeeded`
- `failed`
- `cancelled`
- `expired`
- `provider_redirect_required`
- `provider_webhook_pending`
- `provider_confirmed`
- `provider_failed`

Booking payment state is synchronized separately through `bookings.payment_status`.

