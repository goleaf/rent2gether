# Check-in Flow

Check-in starts from a confirmed or paid `Booking` and is always tied to one `sleeping_place_id`.

Core flow:

1. `BookingCheckInService::createForBooking()` creates one `booking_check_ins` row per booking.
2. Re-loading the check-in refreshes booking context but does not reset an existing workflow status.
3. The service creates legacy checklist items, point-10 steps, and an instruction snapshot.
4. The guest can see allowed instructions, host contact, mark `guest_on_the_way`, mark `guest_arrived`, upload a before photo, report a problem, and confirm check-in.
5. The host records actual arrival details, who met the guest, keys/codes/room/place/rules/bedding/towel/locker checklist state, and then confirms check-in.
6. Guest confirmation writes `guest_confirmed_at` and moves the booking to `guest_checked_in` when allowed.
7. Host confirmation writes `host_confirmed_at`, completes the check-in, and moves the booking to `stay_in_progress` when allowed.

The booking history is not deleted when check-in fails or has a problem. Problem states remain attached to the booking and sleeping place.

Guest and host check-in reminders are separate notifications. `check-in:send-reminders` sends due reminders for all upcoming check-ins one day before arrival and is scheduled hourly with overlap protection.
