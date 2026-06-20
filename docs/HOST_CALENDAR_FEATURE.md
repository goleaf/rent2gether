# Host Calendar Feature

## Purpose

The host calendar is the host's mobile-first living operations center. It combines availability, bookings, guests, cleaning tasks, repairs, prices, payouts, notes, and occupancy into one indexed event layer.

The calendar is not a large desktop grid. Mobile screens use summary cards, a day list, day details, filters, and quick actions.

## Calendar Views

- `property`: all rooms and sleeping places for one property.
- `room`: all sleeping places in one room.
- `sleeping_place`: one exact sleeping place.
- `check_ins`: guests arriving.
- `check_outs`: guests leaving.
- `cleaning`: host cleaning tasks.
- `repairs`: repair blocks and repair reminders.
- `payouts`: internal host payout overview.
- `prices`: date prices and overrides.
- `occupancy`: occupancy by property, room, or sleeping place.

## Event Types

Supported event types:

- `booking`
- `check_in`
- `check_out`
- `cleaning`
- `repair`
- `blocked`
- `available`
- `price`
- `payment`
- `payout`
- `note`
- `inspection`

## Database Structure

`host_calendar_events` is the snapshot table used for fast display. It stores host-owned event rows by `user_id`, property, room, sleeping place, booking, cleaning task, event type, event date, status, guest display name, price, payout, and flags such as `needs_cleaning`, `needs_inspection`, and `needs_repair`.

Important indexes:

- `user_id + event_date`
- `user_id + event_type + event_date`
- `property_id + event_date`
- `room_id + event_date`
- `sleeping_place_id + event_date`
- `booking_id`
- `cleaning_task_id`
- problem flags and payout status

`host_calendar_notes` stores private host notes attached to a date and optionally to a property, room, sleeping place, or booking.

`host_calendar_view_settings` stores each host's default calendar view, default property/room, compact mode, and visibility toggles for prices, guest names, cleaning, repairs, payouts, and occupancy.

## Services

- `HostCalendarService`: reads calendar events, day details, and summary data.
- `HostCalendarEventService`: creates and queries event snapshots.
- `HostCalendarSnapshotService`: refreshes events from bookings, cleaning tasks, sleeping place calendar days, rooms, and properties.
- `HostCalendarFilterService`: applies property, room, sleeping place, type, status, payout, and problem filters.
- `HostCalendarOccupancyService`: calculates occupancy percentages for properties, rooms, sleeping places, and daily ranges.
- `HostCalendarPriceService`: reads daily prices, finds price events, finds missing prices, and changes one date price through calendar services.
- `HostCalendarCleaningService`: creates cleaning-after-checkout tasks, marks needs-cleaning, and marks cleaning done.
- `HostCalendarRepairService`: creates repair events and blocks affected sleeping place calendar days.
- `HostCalendarPayoutService`: reads expected, pending, paid, and general payout events.
- `HostCalendarNoteService`: creates, updates, deletes, and lists private host notes.
- `HostCalendarViewSettingsService`: creates and updates per-host view settings.

## Snapshot Refresh Logic

No required jobs, queues, cron, staff, or admin tools are needed.

Snapshots can be refreshed synchronously:

- when a booking is created, confirmed, cancelled, checked in, or checked out;
- when a cleaning task is created or completed;
- when a date price or calendar day changes;
- when repair events are created;
- when a host opens the calendar and stale data is detected.

`HostCalendarSnapshotService::refreshForBooking()` creates booking, check-in, check-out, and payout events. Cancelled bookings remove their snapshot rows.

`HostCalendarSnapshotService::refreshForSleepingPlace()` turns calendar days into price, blocked, repair, and cleaning events.

`HostCalendarSnapshotService::refreshForCleaningTask()` turns host cleaning tasks into cleaning events.

## Mobile UX

The default mobile view is a day list with summary cards, not a large calendar grid.

The Livewire components are class components:

- `Host/Calendar/HostCalendarPage`
- `Host/Calendar/HostCalendarFilters`
- `Host/Calendar/HostCalendarSummary`
- `Host/Calendar/HostCalendarDayList`
- `Host/Calendar/HostCalendarDayDetails`
- `Host/Calendar/HostCalendarEventCard`
- `Host/Calendar/HostCalendarObjectSelector`
- `Host/Calendar/HostCalendarQuickActions`
- `Host/Calendar/HostCalendarPriceEditor`
- `Host/Calendar/HostCalendarNoteSheet`
- `Host/Calendar/HostCalendarCleaningSheet`
- `Host/Calendar/HostCalendarRepairSheet`
- `Host/Calendar/HostCalendarPayoutsPanel`
- `Host/Calendar/HostCalendarOccupancyPanel`

The shared Blade shell uses compact cards, badges, mobile-first spacing, sticky actions, and translated strings only.

## Quick Actions

Safe quick actions include:

- open booking details;
- message guest;
- create cleaning task;
- mark cleaning done;
- add host note.

Dangerous quick actions require confirmation:

- close date;
- change price;
- mark place free when bookings exist;
- create repair block;
- hide place;
- activate place with missing readiness checks.

`HostCalendarQuickActions` delegates work to services. It keeps Livewire state small and never performs calendar business logic in Blade.

## Permission Rules

- Hosts can see only events where `user_id` is their own id.
- Hosts can create notes only for their own properties, rooms, sleeping places, and bookings.
- Hosts can update only their own notes.
- Price changes require ownership of the sleeping place through its property.
- Cleaning completion requires the cleaning task to belong to the current host.
- Payout events are limited to host-owned bookings.

Guest private data is not exposed. The snapshot stores only a safe guest display name for bookings belonging to the host.

## Payout Overview Rules

The payout calendar is an internal overview, not a banking integration.

If a `Payout` exists for the booking, the snapshot uses its scheduled or paid date, status, currency, and net amount.

If no `Payout` exists, the snapshot may create an expected payout event from the booking total so the host still sees an operational reminder.

## Translation Keys

Translations live in:

- `lang/en/host_calendar.php`
- `lang/ru/host_calendar.php`

The files include keys for:

- title;
- sections and helper text;
- views;
- fields;
- event types;
- statuses;
- actions;
- filters;
- summary messages;
- event titles and descriptions;
- validation;
- empty states;
- confirmations.

No visible string should be hard-coded in Host Calendar Blade or Livewire components.

## Tests

`tests/Feature/HostCalendarFeatureTest.php` covers:

- tables, indexes, models, factories, and relationships;
- booking snapshot events;
- cancelled booking cleanup;
- cleaning events;
- repair events and calendar blocking;
- price events;
- payout events;
- property and room occupancy;
- filtering by property and event type;
- host scoping;
- private notes;
- view settings;
- quick actions for confirmation, price changes, and cleaning completion;
- English and Russian Livewire rendering.
