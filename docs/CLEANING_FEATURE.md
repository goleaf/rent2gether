# Host Cleaning Feature

## Purpose

Cleaning is the host-side readiness layer between check-out, check-in, complaints, repairs, calendar availability, and the next guest. It is not just a `done` checkbox: the task can require checklist items, before/after photos, findings, inspection, repair, forgotten item handling, and calendar blocking.

There is no cleaner, staff, or manager role. The module stores a responsible person on the task:

- `host`
- `host_representative`
- `external_person`
- `future_user`

## Cleaning Types

- `before_check_in`
- `after_check_out`
- `between_guests`
- `daily`
- `weekly`
- `deep`
- `urgent`
- `after_complaint`
- `after_repair`
- `inspection`
- `after_damage`
- `after_long_stay`
- `before_listing_photos`
- `after_cancelled_stay`
- `before_republish`

## Database Structure

`host_cleaning_tasks` remains the central table and is expanded from the earlier bulk-management version. It links to host, property, room, sleeping place, booking, and booking check-out. It stores type, reason, status, priority, schedule, responsible person, photo requirements, findings flags, and readiness.

Child tables:

- `host_cleaning_task_items`: checklist rows with required/completed state.
- `host_cleaning_task_photos`: before/after/damage/forgotten-item photos.
- `host_cleaning_findings`: damage, forgotten items, extra dirt, repair and deposit-review signals.
- `host_cleaning_templates`: reusable host checklist templates.

Indexes cover host status/date lists, property/room/place status filters, checkout lookup, task item status/key lookup, photo type lookup, finding status/type/severity, and default template lookup.

## Statuses

- `draft`
- `planned`
- `needed`
- `assigned`
- `in_progress`
- `waiting_for_photos`
- `waiting_for_inspection`
- `done`
- `done_with_issues`
- `needs_repeat`
- `cancelled`
- `skipped`
- `overdue`

## Services

- `HostCleaningService`: indexed task list and summary.
- `HostCleaningTaskService`: create/start/complete/cancel tasks, create after checkout, before check-in, after complaint, after repair, assign responsible person, mark overdue.
- `HostCleaningChecklistService`: default checklist, template application, item completion.
- `HostCleaningPhotoService`: mobile photo upload and task photo flags.
- `HostCleaningFindingService`: findings, forgotten item flow, repair inspection task, deposit review hook.
- `HostCleaningTemplateService`: reusable templates.
- `HostCleaningCalendarService`: calendar block/release and host calendar snapshot sync.
- `HostCleaningReadinessService`: blocking and recommended readiness issues.
- `HostCleaningInspectionService`: simple inspection signal helper.

## Checklist Logic

Default checklist items include bedding, towel, pillow, blanket, dust, trash, vacuum, floor, ventilation, shared-area checks, locker, bed, mattress, socket, lamp, curtain, forgotten items, and after photos.

Required checklist items must be completed before `HostCleaningTaskService::complete()` can finish the task. If required items or required photos are missing, completion raises validation.

## Photo Logic

Tasks can require before and after photos independently.

Photo types:

- `before`
- `after`
- `damage`
- `forgotten_item`
- `mattress`
- `locker`
- `room`
- `kitchen`
- `bathroom`
- `toilet`

Uploading before/after photos updates `has_before_photos` and `has_after_photos` on the task.

## Findings Logic

Finding types:

- `damage`
- `forgotten_items`
- `extra_dirty`
- `bad_smell`
- `stains`
- `broken_item`
- `missing_item`
- `mold`
- `insects`
- `unsafe`
- `other`

Open findings update task flags:

- damage keeps the place from being automatically ready;
- forgotten items can create `booking_forgotten_items`;
- repair findings create an inspection task and keep the calendar blocked;
- deposit-review findings can create a pending deposit decision.

## Calendar Integration

When cleaning is required, the sleeping-place day is blocked as `cleaning` unless it is already `booked`, `repair`, `blocked`, or `unavailable`.

After cleaning, the calendar is released only when:

- required checklist items are done;
- required photos exist;
- no open repair/damage/deposit-review finding blocks readiness;
- the place is not marked for repeat cleaning;
- no existing booking, repair, manual block, or unavailable row would be overwritten.

Host calendar events are refreshed through `HostCalendarSnapshotService::refreshForCleaningTask()`.

## Checkout Integration

`BookingCheckOutCalendarService::blockForCleaning()` now delegates to `HostCleaningTaskService::createAfterCheckout()`, so check-out creates a full cleaning task with checklist, calendar block, and host calendar event.

## Check-In Integration

`HostCleaningTaskService::createBeforeCheckIn()` can create a pre-arrival task for a booking when a place needs a final check before the next guest.

## Mobile UX

Livewire class components under `App\Livewire\Host\Cleaning` render mobile-first card sections:

- summary cards at the top;
- compact task card;
- checklist cards;
- important flags;
- sticky bottom actions.

The UI uses translated strings only and keeps Livewire public state small (`taskId`, `section`).

## Translation Keys

Translations live in:

- `lang/en/cleaning.php`
- `lang/ru/cleaning.php`

Keys cover title, sections, helpers, types, fields, statuses, item statuses, actions, findings, checklist items, filters, summary, flags, empty states, and validation messages.

## Tests

`tests/Feature/HostCleaningFeatureTest.php` covers:

- schema, indexes, relationships, and factories;
- creation after checkout with checklist and calendar block;
- responsible person without cleaner role;
- checklist/photo readiness blocking;
- completion and host calendar sync;
- findings for forgotten items, damage, repair, and calendar blocking;
- template creation/application;
- host permission boundaries;
- Livewire rendering in English and Russian.
