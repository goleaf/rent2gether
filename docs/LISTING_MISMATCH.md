# Listing Mismatch

Listing mismatch is a structured booking flow for cases where the real sleeping place, room, property, amenities, access, cleanliness, safety, or photos do not match what the guest booked.

The report is always tied to:

- `booking_id`
- `sleeping_place_id`
- `room_id`
- `property_id`
- guest and host users

Guests can report a mismatch during check-in, during stay, at checkout, or from related cancellation and complaint flows. The host is notified and can accept, deny, ask for more evidence, or offer a resolution.

Mismatch reports are not rating penalties by themselves. Rating impact is recorded only after a report is confirmed or partially confirmed.
