# Discount Rules

Sleeping-place discounts live in `sleeping_place_discount_rules`.

Supported discount types:

- `weekly`
- `monthly`
- `long_stay`
- `early_booking`
- `last_minute`
- `new_guest`
- `personal`
- `manual`

Supported value types:

- `percent`
- `fixed_amount`
- `fixed_price`

Default stacking rule: `monthly` and `weekly` do not stack unless the rules explicitly allow stacking. Discounts are capped so accommodation never becomes negative.
