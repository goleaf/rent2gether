# Review Requests

`review_requests` are lightweight invitations to leave a review for a completed stay. They store booking, checkout, property, room, sleeping place, reviewer, subject type, status, and due date context.

Requests are created by `ReviewRequestService::createRequestsAfterCheckout()` and expire without a required cron. Expiration is checked when review, booking, dashboard, notification, or related pages process due state.

No-show bookings, cancellations before check-in, and bookings without a completed stay do not create normal place review requests.
