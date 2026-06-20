# Host Listing Creation Wizard

## Purpose

The host listing wizard is the main mobile-first flow for creating a bookable listing hierarchy:

1. Property
2. Rooms
3. Sleeping places
4. Calendar
5. Publication

Guests book `SleepingPlace` records, but hosts should not have to jump between separate forms to build the full structure. The wizard creates a draft property, lets the host add rooms and sleeping places, opens dates per sleeping place, then runs an automatic readiness checklist before publishing.

There is no moderator, staff, or admin interface. Review fields are stored as future-ready publication metadata only.

## Database

`host_listing_wizard_sessions` stores draft progress:

- `user_id`
- `property_id`
- `current_step`
- `completed_steps_json`
- `skipped_steps_json`
- `last_saved_at`
- `status`

`listing_publication_checks` stores the latest readiness checks:

- `user_id`
- `property_id`
- optional `room_id`
- optional `sleeping_place_id`
- `check_key`
- `category`
- `severity`
- `status`
- `message_key`
- `message_params_json`
- `is_required`
- `is_blocking`
- `fixed_at`

Publication columns are added to `properties`, `rooms`, and `sleeping_places`.

`sleeping_places.cleaning_gap_days` stores the simple MVP cleaning gap setting used by the calendar step.

## Statuses

Publication statuses:

- `draft`
- `incomplete`
- `ready_to_publish`
- `pending_review`
- `published`
- `rejected`
- `paused`
- `hidden`
- `archived`

For the MVP, `publishIfReady` publishes automatically when no blocking readiness checks remain. Manual review can later switch the flow to `pending_review` without changing the user-facing wizard.

## Services

Wizard services live in `App\Services\HostListings\Wizard`.

- `HostListingWizardService` starts, resumes, saves, and tracks wizard progress.
- `HostPropertyDraftService` creates or updates the property draft and stores translated copy.
- `HostRoomDraftService` creates, updates, deletes, and syncs rooms.
- `HostSleepingPlaceDraftService` creates, updates, deletes, syncs, and auto-creates sleeping places from room capacity.
- `HostCalendarDraftService` opens or closes dates, sets date prices, min/max nights, and cleaning gap.
- `HostListingReadinessService` builds and persists readiness checks.
- `HostListingPublishService` publishes, pauses, hides, archives, or automatically rejects incomplete listings.
- `HostListingStatusService` applies publication statuses consistently across the hierarchy.
- `HostListingAutoCreateService` wraps automatic sleeping-place creation.

## Readiness Checks

Blocking checks include:

- at least one room
- at least one sleeping place
- price for every sleeping place
- available dates
- photos
- house rules
- check-in time
- check-out time
- key pickup method
- deposit or explicit no-deposit state
- cancellation policy
- kitchen rules
- bathroom rules
- emergency contact

Recommended improvements include:

- more sleeping-place photos
- bathroom photos
- kitchen photos
- personal locker information
- quiet hours
- weekly discount
- monthly discount

Blocking checks stop publishing. Recommended checks remain friendly suggestions.

## Livewire UI

The route is:

`/{locale}/host/listings/wizard/{propertyId?}`

Components:

- `Host/Listings/CreateListingWizard`
- `Host/Listings/Steps/PropertyStep`
- `Host/Listings/Steps/RoomsStep`
- `Host/Listings/Steps/SleepingPlacesStep`
- `Host/Listings/Steps/CalendarStep`
- `Host/Listings/Steps/PublishStep`
- `Host/Listings/ListingWizardProgress`
- `Host/Listings/ListingReadinessChecklist`
- `Host/Listings/ListingDraftSaveIndicator`
- `Host/Listings/RoomRepeater`
- `Host/Listings/SleepingPlaceRepeater`
- `Host/Listings/CalendarBulkEditor`
- `Host/Listings/PriceByDateEditor`
- `Host/Listings/BeforePublishChecklist`

The UI uses Livewire class components only. There is no Volt, Filament, Inertia, admin, staff, or moderator surface.

## Mobile UX

The wizard keeps one primary step on screen at a time, uses compact cards, and keeps the sticky bottom action bar limited to:

- Back
- Save draft
- Next or Publish

Text inputs use `wire:model.blur`. Selects, toggles, and date choices use `wire:model.change`. Large secondary editors stay in dedicated compact components.

## Localization

Visible strings are stored in:

- `lang/en/listing_wizard.php`
- `lang/ru/listing_wizard.php`

Blade and Livewire components must call translation keys and must not hard-code visible strings.

## Tests

Coverage lives in `tests/Feature/HostListingWizardFeatureTest.php` and verifies:

- tables, indexes, models, and relationships
- wizard start and resume
- draft progress
- room creation
- sleeping-place auto-creation
- calendar opening and price settings
- cleaning gap storage
- readiness blockers
- successful publish status propagation
- authorization for another host
- Livewire rendering in English and Russian
- routed wizard rendering

Run:

```bash
php artisan test tests/Feature/HostListingWizardFeatureTest.php
php artisan test
./vendor/bin/pint
npm run build
```
