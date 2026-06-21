# Host Calendar

The host calendar is a mobile-first management surface for sleeping-place availability.

## Views

- Sleeping place calendar: exact day list for one rentable place.
- Room calendar: aggregates sleeping places in one room.
- Property calendar: aggregates rooms and sleeping places in one property.
- Status legend: explains host-facing statuses.
- Occupancy summary: counts available, request-only, and blocked dates.

## Actions

Host actions are implemented through services:

- Open dates.
- Close dates.
- Mark request-only.
- Mark repair.
- Mark cleaning.
- Create a period block.
- Edit one calendar day.
- Save turnover rules.

Bulk actions must not overwrite active booking locks. They skip dates with active locks and leave bookings intact.

## Livewire Components

Components live under:

- `App\Livewire\Host\Availability`
- `resources/views/livewire/host/availability`

They are class-based Livewire components only. No Volt, no Filament, no admin panel.
