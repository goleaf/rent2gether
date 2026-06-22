# Cleaning Policies

`cleaning_policies` define defaults at property, room, or sleeping-place level.

Policy resolution prefers the most specific context:

1. SleepingPlace policy
2. Room policy
3. Property policy
4. Generated sleeping-place default

Policies control whether cleaning is required after checkout, whether inspection is required after cleaning, default cleaning and inspection durations, same-day turnover buffer, required photos, checklist completion, and auto-create triggers.

These settings do not create roles or background jobs. They only guide synchronous services that create tasks and readiness checks.
