# Dispute Proposals And Decisions

Dispute proposals let guest or host suggest an outcome. A proposal stores resolution type, optional amount, currency, description, guest acceptance, host acceptance, and status.

Proposal statuses include offered, accepted by guest, accepted by host, accepted by both, rejected, expired, cancelled, and applied.

When both sides accept, the system records a mutual-agreement decision. Decisions store the resolution type, amounts for guest/host/deposit/payout adjustment, reason summary, note, decision type, and status.

Decision types:

- `mutual_agreement`
- `system_rule`
- `future_reviewer`

`future_reviewer` is future-ready data only and must not create a staff role or review panel.
