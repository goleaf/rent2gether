# Extension Payment

When `total_payable > 0`, the extension has:

- `requires_payment = true`
- `payment_status = waiting_payment` or `unpaid`
- optional `payment_deadline_at`

MVP payment is internal only. No real provider is required.

When payment is required, applying the extension is blocked until
`payment_status = paid`. Payment failure releases temporary extension holds and
sets extension status to `payment_failed`.

Future provider integration should use a booking payment with:

- `payment_purpose = extension_payment`
- `booking_extension_id`
- provider reference fields only

Do not store card number, CVV, or raw provider payload visible to normal users.
