# Guest Calendar

The guest calendar shows simple availability for one sleeping place.

## Guest Statuses

Guests should see:

- available
- occupied
- request only
- unavailable
- repair

Sensitive internal reasons are masked. For example, a complaint block resolves privately to `unavailable_complaint`, but guests see `unavailable`.

## UX Rules

- Keep the calendar compact for mobile.
- After check-in is selected, show available checkout dates.
- Disable or warn about unavailable ranges.
- Suggest nearby available date ranges.
- Suggest same-room, same-property, or same-host alternatives through services.

## Livewire Components

Guest components live under:

- `App\Livewire\Bookings\Availability`
- `resources/views/livewire/bookings/availability`

They use Flux components and translation keys for visible UI text.
