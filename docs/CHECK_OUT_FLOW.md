# Check-out Flow

Check-out belongs to a specific `Booking`, optional `BookingStay`, and exact `SleepingPlace`.

The flow starts when a stay is close to the planned check-out date, when the guest starts preparing, or when the guest confirms they have left.

Main path:

1. `BookingCheckOutService::createForBooking()` or `createForStay()` creates the check-out.
2. Default steps are created in `booking_check_out_steps`.
3. The guest can move to `guest_preparing`.
4. Guest confirmation sets `guest_confirmed_checkout_at`, `actual_check_out_at`, and moves booking/stay state to checked out.
5. Host confirmation moves the check-out to `waiting_inspection`.
6. Inspection, cleaning, deposit review, and review requests run before closure.
7. Calendar availability opens only when the sleeping place is ready.

The old `booking_check_out_checklist_items` and `booking_check_out_issue_reports` tables remain for backward compatibility. Point 12 adds richer steps, media, inventory checks, issues, status logs, and events.

