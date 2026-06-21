# Sleeping Place Availability

Every rentable unit has its own calendar. `Property` and `Room` are context containers; `SleepingPlace` is the unit that guests search, quote, request, book, and review.

## Rules

- Availability is checked by `sleeping_place_id`.
- Booking one sleeping place must not block another sleeping place in the same room.
- Room-level or property-level problems may block affected sleeping places through calendar blocks.
- Guest-facing availability must hide sensitive internal reasons such as complaints or service closures.
- Livewire and Blade must not calculate availability inline. Use services in `App\Services\Availability`.

## Data model

- `sleeping_place_calendar_settings`: default mode, request-only, active, weekday, and time settings.
- `sleeping_place_turnover_rules`: same-day turnover, cleaning, inspection, and gap rules.
- `sleeping_place_calendar_days`: day overrides for status, price, check-in, and check-out rules.
- `sleeping_place_calendar_blocks`: period blocks for host closures, cleaning, repairs, complaints, request-only, and future service closures.
- `sleeping_place_booking_date_locks`: active per-night locks that protect against double booking.
- `sleeping_place_availability_status_logs`: audit trail for availability changes.

## Services

- `AvailabilityService` remains the central check service and now includes locks, blocks, calendar days, booking mode, and turnover rules.
- `SleepingPlaceCalendarStatusService` resolves private and public day statuses using the documented priority order.
- `SleepingPlaceCalendarDayService` changes individual days and skips active locks during bulk edits.
- `SleepingPlaceCalendarBlockService` creates and releases period blocks.
- `SleepingPlaceAvailabilitySuggestionService` suggests checkout dates, nearby ranges, and alternative sleeping places.

## UI

Guest and host calendar components are Livewire class components only. Host screens use mobile cards instead of large tables.
