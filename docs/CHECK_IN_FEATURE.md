# Booking Check-In Feature

## Purpose

The check-in module turns guest arrival into a clear flow instead of scattered chat messages.

Flow:

1. A confirmed booking gets a `booking_check_ins` record.
2. The guest sees address and instructions only when booking/privacy rules allow it.
3. The guest can mark arrival.
4. The host works through a compact checklist.
5. Guest and host confirm check-in.
6. The booking moves into the stay period.
7. Current occupants and host calendar snapshots are refreshed.

There is no support, staff, manager, moderator, or admin panel. A check-in problem creates a future-ready internal alert, notifies the host through stored alert state, and can create a `support_attention_required` alert when the guest asks for help or the problem is severe.

## Database

Tables:

- `booking_check_ins`
- `booking_check_in_checklist_items`
- `booking_check_in_problem_reports`
- `booking_check_in_alerts`
- `booking_check_in_instructions`
- `booking_check_in_access_disclosures`
- `booking_check_in_steps`
- `booking_check_in_media`
- `booking_check_in_problems`
- `booking_check_in_status_logs`

`booking_check_ins` is the process record for one booking. It stores planned and actual arrival/check-in time, method, checklist booleans, confirmations, problem state, and reminder state.

`booking_check_in_checklist_items` stores the host/guest checklist:

- keys handed over
- door code shared
- room shown
- sleeping place shown
- rules explained
- kitchen, bathroom, and quiet rules explained
- bedding, towel, locker
- before photo uploaded
- guest confirmed
- host confirmed

`booking_check_in_problem_reports` stores guest-reported arrival problems, including problem type, severity, description, photo paths, and resolution status.

`booking_check_in_alerts` is future-ready support/escalation infrastructure. For MVP it is stored workflow state only; it does not create a support panel.

## Statuses

Check-in statuses:

- `not_started`
- `reminder_sent`
- `instructions_available`
- `waiting_for_arrival`
- `guest_on_the_way`
- `guest_arrived`
- `host_notified`
- `host_notified_guest_arrived`
- `access_details_shown`
- `host_confirmed`
- `guest_confirmed`
- `checked_in`
- `check_in_problem`
- `problem_reported`
- `host_unresponsive`
- `waiting_for_resolution`
- `resolved`
- `failed`
- `no_show`
- `cancelled`

Problem report statuses:

- `open`
- `host_notified`
- `waiting_for_host`
- `waiting_for_guest`
- `resolved`
- `escalated`
- `closed`

## Services

Services live in `App\Services\CheckIn`.

- `BookingCheckInService` creates/loads check-in records, marks guest arrival, and syncs with booking status.
- `BookingCheckInInstructionService` returns privacy-filtered guest instructions.
- `BookingCheckInReminderService` sends due reminders and keeps guest and host reminders separate.
- `BookingCheckInChecklistService` creates and updates checklist items.
- `BookingCheckInProblemService` stores problem reports and creates alerts.
- `BookingCheckInAlertService` creates host-facing and support-attention future-ready alerts.
- `BookingCheckInConfirmationService` handles guest/host confirmation and starts the stay when allowed.
- `BookingCheckInPrivacyService` wraps privacy checks for address, codes, and host contact.
- `BookingCheckInMediaService` stores check-in media metadata and syncs before-photo checklist state.

## Guest Flow

The guest can:

- see allowed check-in instructions;
- see the exact address only after confirmation/payment rules allow it;
- see host contact when booking rules allow it;
- upload a before check-in photo;
- mark “I arrived”;
- report a check-in problem;
- confirm check-in after entering.

Exact address, access codes, and host contact are never exposed to unrelated users.

## Host Flow

The host can:

- see arriving guest, room, sleeping place, and planned arrival time;
- record actual arrival time, who met the guest, and checklist items;
- see problem reports and alerts;
- confirm check-in;
- resolve check-in problems.

The UI uses compact mobile cards, not a large table.

## Problem Flow

When the guest reports a problem:

1. A `booking_check_in_problem_reports` row is created.
2. `booking_check_ins.has_problem` is set.
3. A `booking_check_in_alerts` row is created.
4. The host alert status is set to `notified_host`.
5. When the guest asks for help or the problem is severe, a `support_attention_required` alert is stored with status `notified_support`.
6. Severe open problems block automatic stay start until resolved.

No support panel is created.

## Stay Start

The stay starts when:

- guest confirmed and host confirmed; or
- self check-in is configured and guest confirmation is enough;
- there is no open severe problem.

When the stay starts:

- `booking_check_ins.status` becomes `checked_in`;
- the booking status becomes `in_progress`;
- legacy `checkin_records` is updated for compatibility;
- host current occupant snapshot is refreshed;
- host calendar snapshot is refreshed.

## Reminders

No queues are required.

`BookingCheckInReminderService::sendDueReminders(User $user)` can be called when the guest or host opens the booking page, dashboard, host calendar, or current occupants page.

`BookingCheckInReminderService::sendDueRemindersForAll()` powers the `check-in:send-reminders` Artisan command. The command is scheduled hourly in `routes/console.php` with `withoutOverlapping(10)`. Guest and host notifications are deduplicated separately by recipient, booking, recipient type, and notification type.

`CheckInNotificationIntegrationService::scheduleCheckInReminders(Booking $booking)` creates two `notification_reminders` rows for the guest and host at the planned arrival time one day before check-in.

## Livewire Components

Class components only:

- `Bookings/CheckIn/GuestCheckInPage`
- `Bookings/CheckIn/GuestCheckInInstructions`
- `Bookings/CheckIn/GuestArrivalButton`
- `Bookings/CheckIn/GuestCheckInConfirmButton`
- `Bookings/CheckIn/CheckInProblemButton`
- `Bookings/CheckIn/CheckInProblemReportSheet`
- `Bookings/CheckIn/HostCheckInPanel`
- `Bookings/CheckIn/HostCheckInChecklist`
- `Bookings/CheckIn/HostCheckInConfirmButton`
- `Bookings/CheckIn/CheckInStatusBadge`
- `Bookings/CheckIn/CheckInProblemPanel`
- `Bookings/CheckIn/CheckInMediaUploader`
- `Bookings/CheckIn/GuestArrivalButtons`
- `Host/CheckIn/HostCheckInDetailsSheet`

Components keep public state small: only IDs, status, and compact form fields.

## Localization

Visible strings are in:

- `lang/en/check_in.php`
- `lang/ru/check_in.php`

The translation file includes titles, fields, statuses, actions, problem types, checklist items, privacy messages, empty states, validation messages, and alert messages.

## Tests

Covered by `tests/Feature/BookingCheckInFeatureTest.php`:

- schema, indexes, models, relationships, factories;
- default checklist creation;
- privacy-filtered instructions;
- guest arrival;
- problem report and alert creation;
- host resolution permissions;
- guest and host confirmation;
- severe problem blocking;
- current occupants snapshot update;
- host calendar snapshot update;
- no-cron reminders;
- scheduled guest and host reminders;
- check-in reminder command;
- before-photo upload without exposing final storage paths in Livewire state;
- support-attention alert creation;
- host arrival checklist persistence;
- Livewire rendering in English and Russian.

Run:

```bash
php artisan test tests/Feature/BookingCheckInFeatureTest.php tests/Feature/BookingCheckInFlowPointTenTest.php
```
