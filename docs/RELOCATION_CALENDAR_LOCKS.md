# Relocation Calendar Locks

Relocation uses date locks to protect both guest safety and double-booking prevention.

## New Place Hold

While relocation waits for consent or payment, lock only the new `SleepingPlace` for:

```text
[relocation_date, original_check_out_date)
```

Use `relocation_pending` locks.

## Apply

When relocation is applied:

- convert new place relocation holds to booked locks;
- create or link the new booking segment;
- do not recreate old booking locks before the relocation date.

## Old Place

The old place must not be released immediately. Keep it blocked when cleaning, inspection, repair, complaint, forgotten items, or inventory checks are still open.

Only open the old `SleepingPlace` when the place is genuinely ready for the next guest.

