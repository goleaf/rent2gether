# Check-out Cleaning Integration

Cleaning after check-out can be created through `BookingCheckOutCleaningIntegrationService`.

The responsible person can be the host, representative, or external contact in future modules. No cleaner role or cleaner panel is created.

Calendar rule:

The sleeping place must not be opened while:

- cleaning is required;
- inspection is required;
- repair is required;
- a complaint blocks the place;
- a dispute blocks the place.

`BookingCheckOutCalendarIntegrationService::openSleepingPlaceIfReady()` enforces this rule before writing `available` to the sleeping-place calendar.

