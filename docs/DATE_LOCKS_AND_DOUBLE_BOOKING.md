# Date Locks and Double Booking

Date locks are the database-level protection against selling the same sleeping place twice.

## Range Rule

Nightly rentals use a half-open range:

```text
[check_in_date, check_out_date)
```

For July 10 to July 15, active locks are created for July 10, 11, 12, 13, and 14. July 15 is checkout day and is not locked as a night.

## Lock Table

`sleeping_place_booking_date_locks` stores one row per protected night:

- `sleeping_place_id`
- booking/quote/request/extension/relocation nullable references
- `date`
- `lock_type`
- `status`
- `expires_at`
- `released_at`

Active locks use the critical SQLite partial unique index:

```sql
CREATE UNIQUE INDEX sleeping_place_active_date_lock_unique
ON sleeping_place_booking_date_locks (sleeping_place_id, date)
WHERE status = 'active';
```

This allows historical `released`, `expired`, `converted`, and `cancelled` locks to remain in the table while preventing two active locks for the same sleeping place and date.

## Service Rules

Use `SleepingPlaceDateLockService` for lock changes.

- Create locks inside a database transaction.
- Insert one active lock for each occupied night.
- Do not insert a lock for checkout date.
- Catch unique constraint conflicts and return a friendly translated availability message.
- Expire old payment holds opportunistically when availability is checked.
- Release active locks when a booking, quote, or request is released or cancelled.

## Race Conditions

UI date hiding is not protection. The final booking/request/quote flow must create active date locks before treating dates as held. The partial unique index is the last line of defense.
