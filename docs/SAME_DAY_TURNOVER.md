# Same-Day Turnover

Same-day turnover happens when one guest checks out and another checks in on the same date.

The preparation window is calculated as:

```text
checkout_time
+ cleaning_duration
+ inspection_duration
+ access/reset buffer
<= next_check_in_time
```

`TurnoverReadinessService` calculates the available gap, required preparation minutes, warnings, and same-day turnover cleaning tasks.

If the gap is not enough, instant booking should not be treated as safe. The place can remain blocked or move to request-only behavior until the host confirms readiness.
