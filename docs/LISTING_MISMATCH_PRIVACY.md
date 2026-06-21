# Listing Mismatch Privacy

Guests can view only their own mismatch reports. Hosts can view only reports tied to their own bookings.

Normal guest and host views hide:

- `future_review_required`
- `future_review_comment`
- internal media
- private provider/payment details

Evidence visibility is enforced by `ListingMismatchPrivacyService`:

- `guest_and_host`
- `guest_only`
- `host_only`
- `internal`
- `future_review_only`
