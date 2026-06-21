# No-show Calendar Release

No-show calendar release is handled by `BookingNoShowCalendarService`.

Remaining dates can be released only after no-show is confirmed. If the policy requires first-night hold, the check-in date remains blocked while later dates are released.

The calendar must not be opened when another blocking reason exists, such as repair, complaint, or a manual host block. This module records no-show release state on `booking_no_shows.calendar_release_status`.

Release states include:

- `not_released`
- `pending`
- `released_remaining_dates`
- `kept_first_night`
- `kept_blocked`
- `failed`

When dates are released, waitlist and saved-search integrations can record notifications without requiring jobs, queues, or support panels.
