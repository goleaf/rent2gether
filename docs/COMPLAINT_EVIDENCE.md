# Complaint Evidence

Evidence records are linked to `complaint_cases` and optionally to booking records, message records, or source media from check-in, checkout, mismatch, or payment flows.

Evidence can be photos, future video/document formats, screenshots, messages, system events, booking snapshots, payment records, or other proof.

Visibility is explicit:

- `guest_and_host`: visible to both sides.
- `reporter_only`: visible only to the reporter.
- `against_only`: visible only to the other party.
- `host_only` or `guest_only`: role-scoped.
- `internal` and `future_review_only`: hidden from normal UI.

Emergency complaints can start without evidence. The system should ask for evidence later, but must not block the user from reporting danger.
