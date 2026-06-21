# Quote Validation

Updated: 2026-06-21

## Purpose

Quote validation decides whether selected dates can become a quote and whether the quote is bookable, request-only, or invalid.

## Service

`App\Services\Bookings\BookingDateValidationService`

Validation results are persisted in `booking_quote_validation_results` with:

- `validation_key`
- `severity`
- `message_key`
- `message_params_json`
- `blocking`
- guest/host visibility flags

## Validation Layers

The service checks:

- date order
- sleeping place, room, and property active status
- min/max nights
- check-in and checkout weekday rules
- active date locks
- active calendar blocks
- cleaning and inspection gaps
- guest verification flag when supplied
- age rule when profile data is available
- room gender policy when configured and legally applicable
- guest count limit
- host confirmation/request-only warnings

Blocking validation results make the quote `invalid`. Non-blocking results keep the quote valid with `validation_status = warnings`.

## Privacy

Internal reasons such as complaints or service-only states are mapped to guest-safe messages. Guest-facing messages come from `lang/*/booking_dates.php`.
