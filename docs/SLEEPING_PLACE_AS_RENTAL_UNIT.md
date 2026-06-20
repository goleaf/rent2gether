# SleepingPlace As Rental Unit

The public marketplace is sleeping-place-first.

Search returns `SleepingPlace` records. A listing card represents one sleeping place, not a whole property. The listing detail page shows the sleeping place first, then adds room, property, and host context.

The sleeping place owns or anchors:

- price and currency
- availability and calendar rows
- booking requests and bookings
- check-in and check-out state
- cleaning and maintenance state
- inventory notes
- reviews and ratings
- complaints
- media and rules that are specific to the place

Rooms and properties can close or constrain a sleeping place, but final availability is checked per `sleeping_place_id + date`.

Bookings and holds use half-open date ranges:

```text
[check_in_date, check_out_date)
```

This prevents blocking the checkout date by default and allows a same-day boundary only when host turnover, cleaning, inspection, and check-in/check-out rules allow it.

Public cards should use selected columns and compact DTO arrays. They should not load full calendars, maps, galleries, or unrelated results just to render the first mobile viewport.
