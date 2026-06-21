# Occupancy Snapshots

Occupancy snapshots keep room and property cards fast.

Tables:

- `room_current_occupancy_snapshots`
- `property_current_occupancy_snapshots`

Snapshots are recalculated by `CurrentOccupancyService` after check-in, check-out, extension, and relocation.

The snapshot stores counts for current occupants, active bookings, occupied sleeping places, free sleeping places, checkout today, check-in today, checkout this week, open complaints, maintenance, noise, and cleanliness warnings.

The source of truth remains `booking_stays` and `booking_stay_occupants`. Snapshot rows are derived read models.
