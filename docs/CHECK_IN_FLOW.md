# Check-in Flow

Check-in starts from a confirmed or paid `Booking` and is always tied to one `sleeping_place_id`.

Core flow:

1. `BookingCheckInService::createForBooking()` creates one `booking_check_ins` row per booking.
2. The service creates legacy checklist items, point-10 steps, and an instruction snapshot.
3. The guest can mark `guest_on_the_way` and `guest_arrived`.
4. Guest confirmation writes `guest_confirmed_at` and moves the booking to `guest_checked_in` when allowed.
5. Host confirmation writes `host_confirmed_at`, completes the check-in, and moves the booking to `stay_in_progress` when allowed.

The booking history is not deleted when check-in fails or has a problem. Problem states remain attached to the booking and sleeping place.

There is no required cron job. Future reminders can call the same services from page loads, dashboards, calendars, notifications, or a scheduler.
