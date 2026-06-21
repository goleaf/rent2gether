# Stays and Current Occupants

`BookingStay` is the active residence record created after check-in.

The chain is always:

`BookingStay -> Booking -> SleepingPlace -> Room -> Property -> Host`

Booking is the reservation, check-in is the arrival process, and stay is the real living period after the guest has access to the sleeping place.

## Creation

`BookingStayService` creates or updates one stay per booking when:

1. A check-in is confirmed by the guest.
2. A check-in is confirmed by the host.
3. Self check-in completes.
4. A booking moves to `stay_in_progress`.

The stay copies immutable context ids from the booking: guest, host, property, room, and sleeping place. Relocation should create a linked booking segment and therefore a new stay for the new sleeping place.

## Lifecycle

Main statuses are `not_started`, `pending_check_in_confirmation`, `active`, `checkout_soon`, `guest_checked_out`, `waiting_inspection`, `completed`, `disputed`, and `closed`.

Every status transition writes a `booking_stay_status_logs` row. Important business moments write `booking_stay_events`.

## Current Occupants

`booking_stay_occupants` stores the people connected to the stay. A double sleeping place can have a main guest and a second guest, while group bookings still stay as separate booking/stay records per sleeping place.

Host resident lists use `HostCurrentResidentsService`. Guest roommate previews use `GuestRoommatesPreviewService` and privacy filters.
