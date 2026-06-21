# Stay Length Calculation

Updated: 2026-06-21

## Rule

Nightly sleeping-place rentals use a half-open range:

`[check_in_date, check_out_date)`

The checkout date is not an occupied night.

## Example

Check-in: July 10, 2026

Checkout: July 13, 2026

Result:

- `nights_count`: 3
- `chargeable_days_count`: 3
- `calendar_presence_days_count`: 4

Calendar presence is 4 because the guest is present on July 10, 11, 12, and part of July 13. Billing uses the 3 nights.

## Service

`App\Services\Bookings\StayLengthCalculatorService`

Methods:

- `calculateNights()`
- `calculateChargeableDays()`
- `calculateCalendarPresenceDays()`
- `validateBasicDateOrder()`

Same-day checkout is invalid in the current `nightly` mode. Future `daily_future` and `hourly_future` modes are reserved but not implemented.
