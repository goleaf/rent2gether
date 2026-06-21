# Relocation Price Difference

Relocation compares the remaining value of the old place with the remaining value of the new place.

The period is:

```text
[relocation_date, original_check_out_date)
```

## Formula

```text
old_remaining_value = old place price for remaining nights
new_remaining_value = new place price for remaining nights
price_difference = new_remaining_value - old_remaining_value
```

## Payer Rules

- `guest`: guest requested an upgrade or more comfortable/private place.
- `host`: host accepts the difference.
- `shared`: future-ready split.
- `platform_future`: future-ready only, no finance panel.
- `no_extra_charge`: host offers a better place as a solution or compensation.
- `refund_to_guest`: new place is cheaper or compensation is due.

## Payment and Refund

When the guest must pay, create an internal booking payment with relocation purpose. A real payment provider is not required for MVP.

When the guest should receive money back, create an internal refund record and keep `refund_status` on the relocation.

All guest-facing price labels must come from translation keys.

