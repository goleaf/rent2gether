# Relocation vs Extension

Extension and relocation solve different product problems.

## Extension

Extension means:

- same property;
- same room;
- same `SleepingPlace`;
- later checkout date;
- price calculated only for the additional nights.

Extension must never change `sleeping_place_id`.

## Relocation

Relocation means:

- a different `SleepingPlace`;
- possibly a different room or property;
- old and new place history preserved;
- price difference calculated for the remaining period;
- consent and payment/refund may be required.

Relocation must never be implemented by overwriting `sleeping_place_id` on the original booking.

## Why They Stay Separate

A guest may live part of the stay in one bed and the rest in another. Each bed has its own calendar, pricing, inventory, cleaning state, inspection state, complaints, and review context.

Keeping extension and relocation separate protects history, payments, reviews, deposit evidence, and availability.

