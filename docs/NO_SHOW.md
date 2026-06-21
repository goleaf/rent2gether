# No-show Flow

No-show is a verification process for a confirmed, paid, or ready-for-check-in `Booking` when the check-in time has passed and the guest has not arrived.

The process belongs to the booking, the check-in record when one exists, and the exact `SleepingPlace`. It is not a one-click punishment. The host can report that the guest did not arrive, but the guest must be contacted and given a waiting period before no-show can be confirmed.

No-show differs from cancellation because the guest did not clearly cancel before arrival. It differs from host unresponsive because a guest who arrived but cannot access the place is reporting a check-in problem, not failing to show up.

The main flow is:

1. Start watch for an eligible booking.
2. Host reports guest absent or the system starts watching.
3. A contact attempt is created and the guest is notified.
4. Guest can say they are on the way, late, arrived, want to cancel, have a check-in problem, or host is not answering.
5. The waiting period blocks premature confirmation.
6. If rules allow confirmation, money, check-in, calendar, cancellation, refund, rating, and timeline records are updated.

Future support review fields are stored only as hidden data. This module does not create support, admin, moderator, finance, cleaner, manager, or staff panels.
