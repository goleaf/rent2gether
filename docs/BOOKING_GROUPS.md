# Booking Groups

A group booking is a set of individual sleeping-place bookings.

The system must not store multiple sleeping places in one booking row. Each sleeping place keeps its own booking, price, lifecycle, and date locks.

`BookingGroupService::createGroupBooking()` creates one booking per accepted quote and links those bookings with a shared `group_booking_number` in `booking_group_links`.

This allows one sleeping place in the group to be cancelled, extended, disputed, or relocated without corrupting the other sleeping-place calendars.
