# Notification Templates

`notification_templates` define reusable notification copy and behavior.

Each template stores:

- `template_key`
- category
- title/body translation keys
- default priority
- default action type
- supported channels
- booking/action/critical flags

Default templates are seeded by `NotificationTemplateService::seedDefaultTemplates()`. Visible text is resolved from `lang/en/notifications.php` and `lang/ru/notifications.php`, not stored as final rendered strings.
