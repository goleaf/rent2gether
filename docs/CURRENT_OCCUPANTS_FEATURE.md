# Host Current Occupants Feature

## Purpose

The current occupants module gives a host a live, mobile-first view of guests who are staying now or still require stay follow-up.

A current occupant is a guest with a host-owned booking that overlaps today, or a booking currently marked as checked in / active stay, and not completed checkout.

The source of truth is always `bookings`. Snapshot tables are display caches only.

## Database Structure

`host_current_stay_snapshots`

- One row per booking.
- Stores compact card data: guest label, room label, sleeping place label, dates, nights left, payment status, stay status, complaint flags, extension need, checkout need, cleaning need, and last host note.
- Indexed by host and common filters: stay status, checkout date, payment status, complaints, extension need, checkout need, property, room, sleeping place, booking, and guest.
- The mobile list sort uses `current_stays_user_checkout_sort_index` on `user_id`, `check_out_date`, `room_label`, `sleeping_place_label`, and `id`.

`host_guest_stay_notes`

- Private host notes tied to a guest stay.
- Notes belong to host, guest, booking, property, optional room, and optional sleeping place.
- Supports importance and pinned notes.

`host_guest_stay_flags`

- Open/resolved attention flags tied to a booking.
- Flag keys include payment, checkout, extension, complaint, cleaning, inspection, repair, special request, and deposit issues.
- Each flag stores a translation `message_key`.

## Services

- `HostCurrentOccupantsService` returns filtered current occupants and occupant details.
- `HostCurrentStaySnapshotService` refreshes display snapshots from bookings, payments, complaints, extensions, cleaning tasks, and notes.
- `HostGuestStayNoteService` creates, updates, pins, deletes, and lists private host notes.
- `HostGuestStayFlagService` refreshes and resolves attention flags.
- `HostOccupantFilterService` applies indexed filters.
- `HostOccupantActionService` handles safe quick actions.
- `HostOccupantPrivacyService` protects guest contact data.
- `HostOccupantSummaryService` builds top summary counts.

## Snapshot Refresh

Snapshots are refreshed synchronously when module services change a stay and lazily when the host opens the current occupants page.

No required jobs, cron, or queues are needed.

Refresh sources:

- booking confirmation or status change
- payment status / payment records
- guest checked in
- guest checked out
- extension request
- complaint
- host note
- cleaning task

## Privacy Rules

- A host can see only occupants from their own bookings.
- Contact defaults to internal chat.
- Phone and email are not exposed by this module.
- Guest documents and unrelated private data are never shown.
- Host notes are private to the host.

## Filters

Supported filters:

- all current occupants
- check-ins today
- check-outs today
- leaving soon
- checkout overdue
- payment pending
- complaints
- needs extension
- needs checkout
- needs cleaning
- by property
- by room
- by sleeping place

## Quick Actions

Supported actions:

- open booking
- message guest
- mark checked in
- mark checked out
- offer extension
- create cleaning task
- create inspection flag
- add note
- view complaints

Dangerous actions require confirmation in the Livewire component:

- mark checked out
- mark no-show
- start checkout review
- resolve deposit issue
- create repair / inspection after complaint
- external contact

The system does not auto-evict guests. “Needs checkout” means the host should manually review the situation.

## Mobile UX

- Summary cards are shown first.
- Filters are compact horizontal chips with URL-backed scope state, attention-only state, and reset.
- Occupants are represented as paginated cards, not tables.
- Each card shows guest photo, guest name, room, sleeping place, check-in date, check-out date, nights count, nights left, payment status, stay status, guest contact option, special requests, guest rating, complaint state, extension need, checkout need, and host comment.
- Details live in a sheet-style component.
- Public Livewire state is limited to section names, filter state, and compact pagination state.
- All visible strings use `current_occupants.*` translation keys.

## Translation Keys

Files:

- `lang/en/current_occupants.php`
- `lang/ru/current_occupants.php`

Key groups:

- `title`
- `sections`
- `helpers`
- `summary`
- `summary_labels`
- `fields`
- `filters`
- `payment_statuses`
- `stay_statuses`
- `flags`
- `actions`
- `actions_results`
- `cards`
- `empty`
- `confirmations`
- `validation`

## Tests

Feature test:

- `tests/Feature/HostCurrentOccupantsFeatureTest.php`

Coverage:

- tables, indexes, models, factories, relationships
- snapshot refresh from booking, payment, check-in, and checkout
- extension, complaint, payment, checkout, cleaning, and special request flags
- host scoping
- private contact protection
- note creation, update, and pinning
- internal message creation
- filters and summary counts
- quick actions and confirmation state
- complete card rendering with guest photo, dates, room, place, statuses, contact, requests, rating, complaints, extension, checkout, host comment, and attention flags
- pagination and filter reset behavior
- English and Russian Livewire rendering
