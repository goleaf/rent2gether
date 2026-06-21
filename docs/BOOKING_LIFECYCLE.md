# Booking Lifecycle

`BookingStatusService` owns lifecycle transitions. Direct status mutation should be avoided outside creation/decorator code.

Main instant flow:

1. Quote is valid.
2. Booking is created.
3. Booking waits for payment when payment is required.
4. Paid booking becomes confirmed.
5. Confirmed booking becomes ready for check-in.
6. Guest checks in.
7. Stay moves in progress.
8. Checkout starts inspection, deposit return, review, completion, and closure.

Host approval flow:

1. Booking waits for host confirmation.
2. Host approves, rejects, asks a question, or proposes a time change.
3. Approval moves to payment or confirmation.
4. Rejection releases active locks.

Payment failure, rejection, and cancellation release active booking locks through `BookingCalendarIntegrationService`.

Disputes do not delete bookings. They move the booking to `dispute_opened` and then `frozen_until_dispute_resolved` while preserving the audit trail.
