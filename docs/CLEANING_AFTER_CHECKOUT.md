# Cleaning After Checkout

`CleaningTaskService::createAfterCheckout()` creates the canonical after-checkout task from `BookingCheckOut`.

The created task stores:

- booking, stay, and checkout IDs;
- host, property, room, and sleeping-place IDs;
- cleaning type `after_check_out`;
- scheduled date/time from checkout;
- policy-derived photo and inspection requirements.

The service creates default checklist items and blocks the affected sleeping-place calendar date with status `cleaning`. If the task completes without required inspection or repair, the calendar date can be released by `CleaningCalendarIntegrationService`.
