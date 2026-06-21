# Booking Request Warnings

Warnings are decision support for hosts. They are not moderation, support, or staff workflows.

Warnings use neutral logistics and compatibility keys such as:

- `late_night_arrival`
- `very_early_checkout`
- `identity_not_verified`
- `phone_not_verified`
- `no_reviews`
- `last_minute_request`
- `cleaning_gap_conflict`
- `smoking_conflict`
- `pet_conflict`
- `too_many_guests`
- `same_day_urgent_request`

Warnings are stored in `booking_request_warnings` with translation keys. Host-visible warnings avoid sensitive private details and should not be discriminatory.
