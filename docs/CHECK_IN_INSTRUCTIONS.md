# Check-in Instructions

Check-in instructions are stored as a booking-specific snapshot in `booking_check_in_instructions`.

The snapshot copies booking context:

- booking
- property
- room
- sleeping place
- exact address
- room and sleeping-place identifiers
- public check-in text
- key pickup and return notes
- night entry notes
- access codes

Access code fields use encrypted model casts on `BookingCheckInInstruction`. The UI reads decrypted values only when `BookingCheckInInstructionService` says they are visible.

Snapshots protect existing bookings from later host edits to listing access details.
