# Payments

Payments are internal records attached to a `Booking`.

The rentable unit stays unchanged:

`BookingPayment -> Booking -> SleepingPlace -> Room -> Property -> Host`

The MVP does not connect a real provider and never stores card data, CVV, or full card numbers. Provider identifiers and payload columns are future-ready and hidden from normal guest and host UI.

## Flow

1. A booking is created and has a fixed price snapshot.
2. `BookingPaymentCreationService` creates a `booking_payments` row.
3. `BookingPaymentAllocationService` creates allocation rows for accommodation, cleaning fee, service fee, deposit, and future fees.
4. `BookingPaymentDeadlineService` creates the initial payment deadline.
5. The guest starts one or more payment attempts.
6. A successful attempt marks the payment paid or partially paid.
7. A failed attempt keeps the booking payable while the deadline is still valid.
8. An expired payment releases active payment locks and marks the booking payment flow failed.

## Main Services

- `BookingPaymentCreationService`
- `BookingPaymentAttemptService`
- `BookingPaymentStateService`
- `BookingPaymentExpirationService`
- `BookingPaymentAllocationService`
- `BookingPaymentDeadlineService`
- `BookingPaymentPrivacyService`

