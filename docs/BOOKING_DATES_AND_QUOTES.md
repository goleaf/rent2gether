# Booking Dates and Quotes

Updated: 2026-06-21

## Purpose

`BookingQuote` is the temporary calculation between date selection and a real booking/request. It is not a booking and does not reserve dates by itself.

Every quote belongs to one `sleeping_place_id`. `room_id`, `property_id`, and `host_user_id` are copied as context so later booking snapshots do not need to infer the listing hierarchy again.

## Flow

1. Guest selects check-in, checkout, guest count, and optional time preferences.
2. `StayLengthCalculatorService` calculates nights, chargeable days, and calendar presence days.
3. `BookingDateValidationService` checks date order, min/max nights, weekday rules, availability locks, calendar blocks, room policy, guest count, and eligibility.
4. `BookingPriceQuoteService` creates or recalculates the `BookingQuote`.
5. `BookingQuoteLineService` rebuilds transparent price lines.
6. `BookingTimelineDateService` stores payment, reminder, cancellation, payout, deposit, and review dates.
7. Invalid quotes receive validation results and suggestions.
8. A valid quote can become a booking or request only after conversion rechecks availability.

## UI

The mobile UI lives in Livewire class components under:

- `app/Livewire/Bookings/Dates/*`
- `app/Livewire/Bookings/Quotes/*`

Views use Flux components and translation keys only. Quote preview changes do not create date locks. Locks are created during booking/request conversion.

## Tables

- `booking_quotes`
- `booking_quote_lines`
- `booking_quote_validation_results`
- `booking_timeline_dates`
- `booking_quote_suggestions`

## Current convention

The project currently represents booking requests as `Booking` records with host-approval status, not as a separate `BookingRequest` model. `BookingQuoteConversionService::convertToBookingRequest()` follows that convention.
