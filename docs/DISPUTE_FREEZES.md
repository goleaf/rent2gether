# Dispute Freezes

Dispute freezes pause only the process related to the conflict. The system must not freeze an entire booking without a reason.

Supported freeze flags:

- `booking_frozen`
- `refund_frozen`
- `deposit_frozen`
- `host_payout_frozen`
- `rating_impact_frozen`

Examples:

- Refund dispute: freeze the refund and possibly payout.
- Deposit dispute: freeze deposit action and rating impact.
- Safety dispute: freeze rating/search impact until resolved.

When the dispute is resolved or closed, related freezes should be released by `DisputeFreezeService::releaseFreezesAfterResolution`.
