# Host Bulk Management

## Purpose

Host bulk management lets a host safely apply the same change to many owned objects: properties, rooms, sleeping places, bookings, calendars, publication states, cleaning tasks, and booking-related guest messages.

The flow is always:

1. Choose an action.
2. Choose targets.
3. Preview affected, skipped, and risky changes.
4. Confirm.
5. Process and log the result.

There are no admin, staff, cleaner, moderator, queue, cron, or background-job surfaces in this module.

## Available actions

- Clone a room.
- Clone a sleeping place.
- Create several identical sleeping places.
- Change prices, weekly discounts, monthly discounts, and cleaning fees.
- Open, close, or mark calendar dates as occupied.
- Change check-in/check-out rules and rule sets.
- Send booking-related bulk messages to valid guests.
- Create host cleaning tasks or block dates for cleaning.
- Hide, pause, archive, activate, or publish listings.

## Safety rules

- A host can only target objects they own.
- Calendar opening never overrides confirmed booking conflicts.
- Bulk messages can only go to guests connected to the host's bookings.
- Duplicate booking messages in the same batch are skipped.
- Hidden listings stop new bookings but do not cancel existing bookings.
- Activation and publishing rely on readiness/price checks.
- Dangerous actions require preview and confirmation.
- Every processed batch writes item snapshots and logs.

## Database structure

`host_bulk_action_batches` stores the bulk action header: host, action type, target type, status, counts, payload, preview, result, and lifecycle timestamps.

`host_bulk_action_items` stores one selected target per row with before/after snapshots, status, errors, and processed timestamp.

`host_bulk_action_logs` stores audit messages for critical action milestones and target changes.

`host_cleaning_tasks` stores host-owned cleaning tasks without introducing cleaner/staff roles.

Indexes are added for host/status dashboards, action filtering, item processing, target lookup, and cleaning task schedules.

## Services

- `HostBulkActionService` creates, previews, confirms, processes, cancels, and returns results for batches.
- `HostBulkPreviewService` counts selected, affected, skipped, dangerous, and conflicting targets.
- `HostBulkPermissionService` verifies ownership and booking-message permissions.
- `HostBulkCloneService` clones safe room/place data without bookings, guests, reviews, complaints, or calendar unless explicitly requested.
- `HostBulkPricingService` updates base/date prices and calendar discounts.
- `HostBulkCalendarService` opens/closes/marks dates through existing calendar services and skips booking conflicts.
- `HostBulkRulesService` updates property/room rules and warns about existing bookings.
- `HostBulkMessageService` previews and sends only booking-related guest messages.
- `HostBulkCleaningService` creates cleaning tasks and can block calendar dates for cleaning.
- `HostBulkPublicationService` changes listing publication state safely.
- `HostBulkActionLogger` records batch activity.

## Livewire components

All UI is Livewire class-based, mobile-first, Flux-based, and translated:

- `Host/Bulk/HostBulkActionsPanel`
- `Host/Bulk/HostBulkTargetSelector`
- `Host/Bulk/HostBulkActionPicker`
- `Host/Bulk/HostBulkPreview`
- `Host/Bulk/HostBulkConfirm`
- `Host/Bulk/HostBulkResult`
- `Host/Bulk/CloneRoomAction`
- `Host/Bulk/CloneSleepingPlaceAction`
- `Host/Bulk/CreateIdenticalPlacesAction`
- `Host/Bulk/BulkPriceEditor`
- `Host/Bulk/BulkCalendarEditor`
- `Host/Bulk/BulkRulesEditor`
- `Host/Bulk/BulkMessageGuests`
- `Host/Bulk/BulkCleaningEditor`
- `Host/Bulk/BulkPublicationEditor`

The current UI shell exposes the translated action picker, short cards, preview guidance, and sticky confirmation actions. Heavier selectors/editors should stay compact and avoid large hidden DOM trees.

## Mobile UX

- Step 1: choose action.
- Step 2: choose target scope.
- Step 3: configure action.
- Step 4: preview changes.
- Step 5: confirm and apply.

The preview must show affected count, skipped count, danger warnings, and reasons for skips. Details can use bottom sheets or compact cards.

## Translation keys

Translations live in:

- `lang/en/host_bulk.php`
- `lang/ru/host_bulk.php`

Every visible string in Blade and Livewire uses translation keys.

## Tests

`tests/Feature/HostBulkManagementFeatureTest.php` covers:

- Schema, indexes, factories, and relationships.
- Batch create, preview, confirm, process, and logs.
- Ownership permissions and booking conflicts.
- Safe room/place cloning and identical place creation.
- Price, calendar, rules, messaging, cleaning, and publication services.
- Activation readiness checks.
- Livewire component rendering in English and Russian.
