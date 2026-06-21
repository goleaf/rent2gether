# Price Lines

Booking quotes store transparent price rows in `booking_quote_lines`.

Night rows use:

- `night`
- `weekday_night`
- `weekend_night`
- `holiday_night`
- `date_override`

Adjustment rows use:

- `weekly_discount`
- `monthly_discount`
- `long_stay_discount`
- `early_booking_discount`
- `last_minute_discount`
- `new_guest_discount`
- `personal_discount`
- `promo_discount`
- `early_check_in_fee`
- `late_checkout_fee`
- `extra_guest_fee`
- `cleaning_fee`
- `service_fee`
- `tax_future`
- `city_fee_future`
- `deposit`

Every recalculation deletes old quote lines and creates fresh lines so stale prices are not silently kept.
