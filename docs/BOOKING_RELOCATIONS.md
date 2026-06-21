# Booking Relocations

Booking relocation moves a guest from one `SleepingPlace` to another during an active booking or stay.

Relocation is not an extension. Extension keeps the same `sleeping_place_id` and changes checkout date. Relocation changes `SleepingPlace` and must preserve the old place history.

## When Relocation Starts

A relocation can be created by:

- the guest requesting a different place;
- the host offering another place;
- a complaint, maintenance issue, listing mismatch, or future system process.

Future support fields may store context, but there is no support role, support panel, staff panel, finance panel, or admin panel.

## Core Flow

1. Create a `booking_relocations` record linked to the original booking, current stay, guest, host, old place, and optional new place.
2. Check the new `SleepingPlace` for `[relocation_date, original_check_out_date)`.
3. Calculate old remaining value, new remaining value, price difference, payment/refund, and payer.
4. Store required guest and host consents.
5. Hold the new place with `relocation_pending` locks while waiting for consent or payment.
6. Apply only after required consents are accepted and required payment is paid.
7. Create a new booking segment for the new `SleepingPlace`.
8. Shorten or link the original booking without overwriting its original `sleeping_place_id`.
9. Keep the old place blocked until cleaning, inspection, repair, complaint, and inventory checks allow reopening.

## Important Records

- `booking_relocations`: main relocation process.
- `booking_relocation_options`: possible new places.
- `booking_relocation_price_lines`: transparent price difference.
- `booking_relocation_consents`: guest and host agreements.
- `booking_relocation_inventory_transfers`: keys, lockers, bedding, towels, and similar handoff.
- `booking_relocation_status_logs` and `booking_relocation_events`: audit timeline.

