# Booking Check-Out Feature

## Purpose

The check-out module closes a stay carefully instead of only changing a booking status.

Flow:

1. A stay approaches its check-out date.
2. Guest and host can receive due reminders through service calls without required cron.
3. The guest confirms leaving and the system creates inspection and cleaning follow-up.
4. The host checks keys, storage, personal items, bedding, towel, room, and sleeping place condition.
5. Deposit return or deduction is recorded as internal status.
6. Forgotten items, issues, cleaning, inspection, review requests, current occupants, and host calendar snapshots are updated.
7. Future availability is released only when it is safe.

There is no admin, support, staff, cleaner, finance, moderator, or property-manager panel. Cleaning and inspection tasks belong to the host.

## Database

Tables:

- `booking_check_outs`
- `booking_check_out_checklist_items`
- `booking_check_out_issue_reports`
- `booking_forgotten_items`
- `booking_deposit_decisions`
- `host_inspection_tasks`
- `booking_review_requests`

`booking_check_outs` is the process record for one booking. It stores planned and actual check-out time, returned access, storage and item checks, inspection flags, photos, guest/host confirmations, deposit deduction data, status, and reminder state.

`booking_check_out_checklist_items` stores the guest/host checklist:

- keys returned
- locker emptied
- personal items taken
- bedding returned
- towel returned
- sleeping place free
- room checked
- after photo uploaded
- guest confirmed
- host confirmed
- deposit reviewed
- cleaning created
- review requested

`booking_check_out_issue_reports` stores host-reported check-out issues such as damage, extra dirt, missing key, late check-out, or unpaid charges.

`booking_forgotten_items` stores found items, where they are kept, guest notification state, pickup/disposal state, and keep-until date.

`booking_deposit_decisions` stores internal MVP deposit decisions. It does not integrate with a payment provider yet.

`host_inspection_tasks` stores host-owned inspection work after check-out.

`booking_review_requests` creates lightweight review prompts for guest and host.

## Statuses

Check-out statuses:

- `not_started`
- `reminder_sent`
- `extension_offered`
- `waiting_for_checkout`
- `guest_ready_to_leave`
- `guest_checked_out`
- `host_inspection_pending`
- `host_confirmed`
- `inspection_completed`
- `cleaning_needed`
- `cleaning_created`
- `cleaning_done`
- `deposit_review_pending`
- `deposit_return_pending`
- `deposit_returned`
- `deposit_deduction_requested`
- `deposit_disputed`
- `problem_reported`
- `checkout_overdue`
- `completed`
- `cancelled`

Deposit decisions:

- `return_full`
- `return_partial`
- `withhold_full`
- `no_deposit`

Deposit statuses:

- `pending_review`
- `return_pending`
- `returned`
- `deduction_requested`
- `guest_disputed`
- `resolved`
- `failed`

## Services

Services live in `App\Services\CheckOut`.

- `BookingCheckOutService` creates/loads check-out records, checks extension availability, and marks the guest checked out.
- `BookingCheckOutReminderService` sends due reminders without required cron/jobs.
- `BookingCheckOutChecklistService` creates and updates checklist items.
- `BookingCheckOutInspectionService` creates and completes host inspection tasks.
- `BookingCheckOutIssueService` stores issue reports and resolves them.
- `BookingForgottenItemService` stores forgotten items and pickup/disposal state.
- `BookingDepositDecisionService` records deposit return, partial deduction, full withholding, guest dispute, and resolution.
- `BookingCheckOutConfirmationService` completes checkout when guest confirmation, host confirmation, inspection, and issue rules allow it.
- `BookingCheckOutCalendarService` blocks cleaning/repair/inspection dates and releases future availability only when safe.
- `BookingReviewRequestService` creates guest and host review requests.
- `BookingCheckOutPrivacyService` filters checkout data for guest and host.

## Guest Flow

The guest can:

- see check-out date and time;
- see the check-out checklist;
- confirm “I checked out”;
- see an extension hint when the place is free after check-out;
- see deposit status;
- see review request status after checkout.

The module does not auto-evict a guest. If check-out is overdue or disputed, services create status/flags for host review.

## Host Flow

The host can:

- see the leaving guest, room, sleeping place, planned time, and actual time;
- complete checklist items;
- create and complete inspection;
- report issues with photos;
- record forgotten items;
- decide deposit return or deduction;
- create cleaning;
- confirm final checkout;
- request reviews.

Host confirmation does not complete the booking while required inspection or unresolved issues are still open.

## Reminders

No required cron, queues, or jobs are needed.

`BookingCheckOutReminderService::sendDueReminders(User $user)` can be called when the guest or host opens the booking page, dashboard, host calendar, or current occupants page. A future scheduler can call the same service without changing business logic.

## Extension Logic

`BookingCheckOutService::canOfferExtension()` checks the day after the planned check-out date.

Extension is not offered when another booking or a sleeping-place calendar block already covers that next date.

## Inspection Logic

Guest checkout creates a host inspection task. Host inspection records:

- room checked;
- sleeping place checked;
- sleeping place free;
- damage;
- extra dirt.

If damage or extra dirt exists, check-out moves to `problem_reported` until the issue path is resolved.

## Cleaning Logic

Guest checkout creates or reuses a host cleaning task with reason `after_checkout`.

Calendar blocks for cleaning are written through `BookingCheckOutCalendarService`, and existing booking history, cleaning, repair, manual blocks, or unavailable rows are not overwritten.

## Deposit Logic

For MVP, deposit actions are internal records:

- full return creates `return_full` / `return_pending`;
- partial deduction creates `return_partial` / `deduction_requested`;
- full withholding uses the same deduction flow with the full deposit amount;
- guest dispute changes status to `guest_disputed`;
- resolution changes status to `resolved`.

No real payment provider is required.

## Forgotten Items

Forgotten items store item name, description, photos, storage location, keep-until date, notification status, pickup state, shipping/disposal state, and close state.

Only the host who owns the booking can create or update forgotten items.

## Review Requests

After successful checkout, `BookingReviewRequestService` creates one guest-to-host and one host-to-guest request. Requests are idempotent for the same booking/reviewer/reviewee pair.

## Calendar Release Rules

The system never deletes booking history from the calendar.

Future dates after checkout can become available only when:

- there is no next booking;
- there is no cleaning gap;
- there is no repair row;
- there is no manual host block;
- inspection is completed.

Existing `booked`, `cleaning`, `repair`, `blocked`, and `unavailable` calendar rows are preserved.

## Current Occupants Integration

When checkout completes, `HostCurrentStaySnapshotService::refreshForBooking()` updates the current occupant snapshot to checked-out state. The guest then leaves the active living list according to occupant filters.

## Host Calendar Integration

When checkout completes, `HostCalendarSnapshotService::refreshForBooking()` refreshes host calendar check-out events. Cleaning and inspection rows remain separate host operations.

## Livewire Components

Class components only:

- `Bookings/CheckOut/GuestCheckOutPage`
- `Bookings/CheckOut/GuestCheckOutChecklist`
- `Bookings/CheckOut/GuestCheckOutConfirmButton`
- `Bookings/CheckOut/HostCheckOutPanel`
- `Bookings/CheckOut/HostCheckOutChecklist`
- `Bookings/CheckOut/HostInspectionPanel`
- `Bookings/CheckOut/HostCheckOutConfirmButton`
- `Bookings/CheckOut/CheckOutStatusBadge`
- `Bookings/CheckOut/CheckOutIssueReportSheet`
- `Bookings/CheckOut/ForgottenItemsPanel`
- `Bookings/CheckOut/DepositDecisionPanel`
- `Bookings/CheckOut/ReviewRequestPanel`

Components keep public state small: IDs, status, and compact action form fields only.

The guest route is:

- `guest.bookings.check-out`

## Localization

Visible strings are in:

- `lang/en/check_out.php`
- `lang/ru/check_out.php`

The translation files include titles, components, fields, sections, statuses, actions, issue types, checklist items, deposit decisions, forgotten item statuses, reminder messages, validation messages, and empty states.

## Tests

Covered by `tests/Feature/BookingCheckOutFeatureTest.php`:

- schema, indexes, models, relationships, and factories;
- default checklist creation;
- guest/host access and no-cron reminders;
- extension offer safety;
- guest checkout confirmation;
- host inspection and confirmation;
- cleaning and inspection task creation;
- deposit return, deduction, guest dispute, and resolution;
- issue reports and issue blocking;
- forgotten items and host permissions;
- current occupants snapshot refresh;
- host calendar snapshot refresh;
- calendar history/cleaning/repair preservation;
- English and Russian Livewire rendering.

Run:

```bash
php artisan test tests/Feature/BookingCheckOutFeatureTest.php
```
