# Deposit Pricing

Deposit is separate from accommodation.

Settings live on `sleeping_place_pricing_settings`:

- `deposit_required`
- `deposit_amount`
- `deposit_payable_now`
- `deposit_refundable`

When `deposit_payable_now` is true, the deposit is included in `total_payable`.
When it is false, the deposit is shown as a policy amount but not collected in the current quote total.

Refundable deposit lines are marked with `is_refundable`.
