# Extension Pricing

Extension pricing calculates only the additional nights between the current
checkout date and requested new checkout date.

Example:

- Current booking: July 10 to July 15
- Extension: July 15 to July 18
- Charged nights: July 15, July 16, July 17

The module stores price lines in `booking_extension_lines`.

Line types include:

- `extension_night`
- `weekday_night`
- `weekend_night`
- `holiday_night`
- `date_override`
- `weekly_discount`
- `monthly_discount`
- `long_stay_discount`
- `extension_discount`
- `promo_discount`
- `late_checkout_fee`
- `cleaning_fee`
- `service_fee`
- `additional_deposit`
- future tax and city fee lines

`total_payable` is only the extension amount. It is not the original booking
total. Applying an extension adds the extension accommodation, fees, deposit,
refundable amount, non-refundable amount, and host payout deltas to the original
booking totals.
