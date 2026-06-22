# Notification Deduplication And Throttling

Deduplication prevents repeated notifications for the same event window, such as several chat messages in a row.

`NotificationDeduplicationService` builds a stable key from template, recipient, and booking context. Recent duplicates are merged into the existing notification.

Throttling prevents noisy flows like saved search or favorite updates from producing too many notifications. `NotificationThrottleService` stores a short throttle window per user and throttle key.
