# Extension Timeline Rescheduling

Applying an extension changes the future timeline of the booking.

The apply service updates or records:

- booking checkout date and time
- stay planned checkout date and time
- checkout record date
- extension date locks converted to booked locks
- checkout reminder rescheduling event
- cleaning rescheduling event
- deposit review rescheduling event
- review request rescheduling event
- occupancy snapshot recalculation timestamp
- guest and host database notifications

Old checkout, cleaning, inspection, deposit review, and review request moments
must not remain attached to the previous checkout date after the extension is
applied.

The MVP records future integrations as events when the full target module is not
yet responsible for the actual task object.
