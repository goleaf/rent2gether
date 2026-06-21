# Turnover Rules

Turnover rules decide whether a new guest can check in on the same day another guest checks out.

## Fields

`sleeping_place_turnover_rules` stores:

- `min_gap_minutes`
- `cleaning_required_between_guests`
- `cleaning_gap_minutes`
- `inspection_required_after_checkout`
- `inspection_gap_minutes`
- `same_day_turnover_allowed`
- `same_day_turnover_requires_cleaning_done`
- `same_day_turnover_requires_inspection_done`
- `earliest_new_check_in_time`
- `latest_previous_check_out_time`

## Same-Day Turnover

Checkout date is not locked as a night, but same-day check-in still depends on turnover rules.

Example:

```text
Previous checkout: July 15, 11:00
Cleaning gap: 240 minutes
Next check-in: July 15, 17:00
```

The place is ready at 15:00, so 17:00 check-in can be allowed when same-day turnover is enabled and cleaning/inspection completion requirements are satisfied.

## Service Rules

Use `SleepingPlaceTurnoverService` for turnover checks.

- If same-day turnover is disabled, a guest cannot check in on the previous guest checkout date.
- If same-day turnover is enabled, compare checkout time plus required gap to the new check-in time.
- Cleaning and inspection requirements can block instant booking or make the date request-only.
- Turnover logic belongs in services, not Blade or Livewire views.
