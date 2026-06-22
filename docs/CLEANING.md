# Cleaning

Cleaning tasks prepare a `Property`, `Room`, or `SleepingPlace` for the next guest. The canonical task model is `cleaning_tasks`; the older `host_cleaning_*` demo tables are left intact for existing host screens.

## When Tasks Are Created

- after checkout completion;
- before check-in when a policy requires it;
- after cleanliness complaints;
- after repairs;
- after relocation;
- manually by the host;
- during same-day turnover checks.

No cleaner role is created. Each task stores a neutral responsible person through `responsible_type`, `responsible_user_id`, `responsible_name_snapshot`, and `responsible_contact_snapshot`.

## Task Flow

1. Create task in booking/listing context.
2. Create checklist items.
3. Block the affected sleeping-place calendar date when needed.
4. Upload before/after photos if required.
5. Complete the checklist.
6. Mark the task completed or completed with issues.
7. Create inspection, maintenance, complaint, or deposit-review follow-up only when the result requires it.

Ratings are not affected just because a cleaning task exists. Only confirmed outcomes can become rating signals.
