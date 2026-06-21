# Check-in Problems

Guests can report check-in problems through `BookingCheckInProblemService`.

The module keeps the legacy `booking_check_in_problem_reports` table for existing flows and adds point-10 `booking_check_in_problems` for structured future integrations.

Problem types include:

- cannot find address
- wrong address
- access code or key does not work
- host or representative is not answering
- room or sleeping place is occupied
- listing mismatch
- dirty place
- unsafe situation
- other

`host_not_answering` can mark the booking as `host_unresponsive` through the future-ready integration service. `unsafe_situation` can create a complaint placeholder. These are service integrations only and do not create support, staff, manager, or admin panels.
