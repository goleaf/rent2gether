# Host Payout Preview

Host payout preview is calculated from the accepted quote context.

The current formula is:

```text
accommodation_after_discount
+ early_check_in_fee
+ late_checkout_fee
+ extra_guest_fee
+ cleaning_fee
- host_service_fee
= host_payout_preview_amount
```

Refundable deposit and guest service fee are not included in the host payout preview.

The guest-facing UI does not expose host payout internals.
