# Extension Availability

Extension availability checks only the additional period:

`[current_check_out_date, new_check_out_date)`

The current booking range is not checked again because it already belongs to the
guest. This prevents the existing booking from blocking its own extension.

The check blocks extension when the additional range contains:

- another active booking for the same sleeping place
- an active date lock from another booking, request, quote, or payment flow
- a payment-pending lock
- a host-confirmation lock
- a sleeping-place repair block
- a complaint block
- a room block
- a property block
- max-stay or guest-count violations
- an open dispute that blocks extension

Temporary extension holds use `extension_pending` locks and are created only for
the additional dates. On rejection, cancellation, expiration, or payment failure,
the holds are released. On apply, the holds are converted into booked locks.

Cleaning gap between different guests does not apply when the same guest stays
on the same sleeping place.
