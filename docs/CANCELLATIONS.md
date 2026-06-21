# Booking Cancellations

Cancellation is a process around a `Booking`, not just a status update. It stores who requested the cancellation, the booking/sleeping-place context, the preview that was shown before confirmation, the final refund amounts, calendar-release state, status logs, and events.

Paid bookings must go through `booking_cancellation_previews` before a `booking_cancellations` record is created. Unpaid bookings may be cancelled directly, but the service still creates a cancellation record so history stays consistent.

The final cancellation updates the booking status to the guest or host cancellation lifecycle status, writes refund lines, creates a `booking_refund` when money should be returned, releases date locks only when safe, and notifies both sides.

No admin, support, moderator, finance, manager, or cleaner workflow is created by this module.
