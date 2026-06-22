# Notification Reminders

Reminders are future notifications stored in `notification_reminders`.

Examples include payment deadlines, check-in reminders, checkout reminders, deposit response deadlines, cleaning, inspection, saved search digests, favorites, and waitlist offers.

There is no required cron, queue, or scheduled job. Due reminders can be processed when a user opens relevant pages:

- dashboard
- host dashboard
- booking page
- notification center
- messages page

`NotificationDueProcessorService` handles due reminders for one user, one booking, one host, or a capped global batch.
