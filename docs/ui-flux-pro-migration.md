# Flux Pro Interface Migration

Reviewed: 2026-06-20

## Scope

This pass migrated the remaining native Blade action and form controls in `resources/views` to documented Flux UI / Flux Pro components while preserving existing Livewire properties, actions, validation, routes, permissions, and data flow.

Official saved references used:
- `docs/flux-ui-components.md` Button rules from `https://fluxui.dev/components/button`
- `docs/flux-ui-components.md` Input rules from `https://fluxui.dev/components/input`
- `docs/flux-ui-components.md` Select rules from `https://fluxui.dev/components/select`
- `docs/flux-ui-components.md` Field rules from `https://fluxui.dev/components/field`
- `docs/flux-ui-components.md` Modal rules from `https://fluxui.dev/components/modal`
- `docs/flux-ui-components.md` File Upload rules from `https://fluxui.dev/components/file-upload`

## What Changed

- Replaced raw toggle/action buttons with `flux:button` in amenity and rule pickers, favorite toggles, saved-search city results, host wizard step controls, property type choices, host calendar date cells, and custom sheet backdrops.
- Replaced simple native file inputs with documented `flux:input type="file"` in complaint media and check-in problem report photo uploads.
- Replaced native `<option>` children inside Flux selects with documented `flux:select.option` in complaint, review, co-living profile, profile edit, guest intake, and bed form views.
- Added a focused PHPUnit guard in `tests/Feature/FluxProComponentUsageTest.php` so raw native action/form/table controls do not drift back into Blade views.

## Components Used

- `flux:button` for actions, icon-only favorite toggles, pressed state controls, date selection cells, search suggestions, and backdrop dismiss actions.
- `flux:input type="file"` for simple Livewire file uploads where drag/drop, previews, and upload item lists are not required.
- `flux:select.option` for Flux Select option children.
- Existing `flux:autocomplete`, `flux:field`, `flux:label`, `flux:error`, `flux:select`, `flux:checkbox`, `flux:textarea`, `flux:card`, `flux:badge`, `flux:heading`, `flux:text`, `flux:callout`, and `flux:accordion` usage was preserved.

## Second Pass: Shared Surfaces and Rich Uploads

This follow-up pass replaced additional custom bordered panels, chips, skeletons, avatar markup, status boxes, and the rich media uploader with documented Flux Pro components.

Additional saved references used:
- `docs/flux-ui-components.md` Avatar rules from `https://fluxui.dev/components/avatar`
- `docs/flux-ui-components.md` Badge rules from `https://fluxui.dev/components/badge`
- `docs/flux-ui-components.md` Card rules from `https://fluxui.dev/components/card`
- `docs/flux-ui-components.md` Callout rules from `https://fluxui.dev/components/callout`
- `docs/flux-ui-components.md` File Upload rules from `https://fluxui.dev/components/file-upload`
- `docs/flux-ui-components.md` Skeleton rules from `https://fluxui.dev/components/skeleton`

### What changed in the second pass

- Replaced listing amenity/rule pill spans with `flux:badge`.
- Replaced listing loading placeholder blocks with `flux:skeleton.group`, `flux:skeleton`, and `flux:skeleton.line`.
- Replaced the custom host avatar shell with documented `flux:avatar` using `name`, `src`, `initials`, `color="auto"`, `color:seed`, `circle`, and `size`.
- Replaced the application offline banner and media manager status/warning/empty boxes with `flux:callout`.
- Replaced the media manager upload surface with `flux:file-upload`, `flux:file-upload.dropzone with-progress inline`, `flux:file-item`, and `flux:file-item.remove`.
- Added localized `media.manager.remove_file` labels for accessible file removal in English and Russian.
- Replaced standalone root-level custom bordered panels in Livewire/shared component roots with `flux:card` or `flux:callout`.
- Added test coverage to prevent raw native controls and root-level custom bordered Livewire/shared panels from drifting back into the interface.

### Additional files touched in the second pass

- `app/Livewire/Media/ManageMedia.php`
- `lang/en/media.php`
- `lang/ru/media.php`
- `resources/views/components/host/public-card.blade.php`
- `resources/views/components/layouts/app.blade.php`
- `resources/views/components/listings/card-amenities.blade.php`
- `resources/views/components/listings/card-rules.blade.php`
- `resources/views/components/listings/card-skeleton.blade.php`
- `resources/views/livewire/bookings/compatibility-check-before-booking.blade.php`
- `resources/views/livewire/host/hints/host-hint-card.blade.php`
- `resources/views/livewire/host/hints/host-wizard-hints.blade.php`
- `resources/views/livewire/host/profile/host-public-profile-card.blade.php`
- `resources/views/livewire/host/properties/property-card.blade.php`
- `resources/views/livewire/host/rooms/room-card.blade.php`
- `resources/views/livewire/host/sleeping-places/sleeping-place-card.blade.php`
- `resources/views/livewire/listings/detail/compatibility-summary-section.blade.php`
- `resources/views/livewire/media/manage-media.blade.php`
- `resources/views/livewire/saved-searches/saved-search-card.blade.php`
- `resources/views/livewire/search/compatibility-filter.blade.php`
- `resources/views/livewire/users/public-guest-profile-card.blade.php`
- `resources/views/livewire/users/public-host-profile-card.blade.php`
- `resources/views/livewire/users/public-user-card.blade.php`
- `resources/views/livewire/waitlist/host-waiting-guest-card.blade.php`
- `tests/Feature/FluxProComponentUsageTest.php`

## Files Touched

- `resources/views/livewire/catalog/amenity-picker.blade.php`
- `resources/views/livewire/catalog/rule-picker.blade.php`
- `resources/views/livewire/favorites/toggle-favorite.blade.php`
- `resources/views/livewire/favorites/*-sheet.blade.php`
- `resources/views/livewire/saved-searches/*sheet*.blade.php`
- `resources/views/livewire/saved-searches/saved-searches-list.blade.php`
- `resources/views/livewire/search/sleeping-place-search.blade.php`
- `resources/views/livewire/checkin/problem-report.blade.php`
- `resources/views/livewire/complaints/create-complaint.blade.php`
- `resources/views/livewire/host/property-form.blade.php`
- `resources/views/livewire/host/room-form.blade.php`
- `resources/views/livewire/host/sleeping-place-form.blade.php`
- `resources/views/livewire/host/bed-form.blade.php`
- `resources/views/livewire/shell/host-calendar-page.blade.php`
- `resources/views/livewire/reviews/create-review.blade.php`
- `resources/views/livewire/profile/co-living-profile-form.blade.php`
- `resources/views/livewire/profile/edit-profile.blade.php`
- `resources/views/livewire/bookings/guest-intake/guest-intake-wizard.blade.php`
- `tests/Feature/FluxProComponentUsageTest.php`

## Checks Run

- `rg -n "<button\\b|<input\\b|<select\\b|<textarea\\b|<details\\b|<summary\\b|<table\\b|<option\\b" resources/views --glob '*.blade.php'` - no matches.
- `git diff --check -- resources/views tests/Feature/FluxProComponentUsageTest.php docs/ui-flux-pro-migration.md` - passed.
- `./vendor/bin/pint --format agent tests/Feature/FluxProComponentUsageTest.php` - passed.
- `php artisan test --compact tests/Feature/FluxProComponentUsageTest.php` - passed, 3 tests and 8 assertions.
- `php artisan test --compact tests/Feature/ComplaintProblemReportFlowTest.php tests/Feature/HostPropertyWizardTest.php tests/Feature/HostListingWizardFeatureTest.php tests/Feature/CompletedStayReviewFlowTest.php` - passed, 26 tests and 160 assertions.
- `php artisan test --compact tests/Feature/SavedSearchesFeatureTest.php tests/Feature/FavoritesCollectionsFeatureTest.php tests/Feature/SearchPageTest.php tests/Feature/BookingGuestIntakeFeatureTest.php tests/Feature/CurrentOccupantsFeatureTest.php tests/Feature/AccountFlowsTest.php tests/Feature/HostCalendarFeatureTest.php` - passed, 59 tests and 379 assertions.
- `npm run build` - passed.

Second pass checks:
- `./vendor/bin/pint --format agent app/Livewire/Media/ManageMedia.php lang/en/media.php lang/ru/media.php tests/Feature/FluxProComponentUsageTest.php` - passed.
- `php artisan view:clear` - passed.
- `rg -n "<button\\b|<input\\b|<select\\b|<textarea\\b|<details\\b|<summary\\b|<table\\b|<option\\b" resources/views --glob '*.blade.php'` - no matches.
- `rg -n '^<(article|section|div) class=".*rounded-lg.*border' resources/views/livewire resources/views/components` - no matches.
- `php artisan test --compact tests/Feature/FluxProComponentUsageTest.php` - passed, 6 tests and 19 assertions.
- `php artisan test --compact tests/Feature/MediaUploadSystemTest.php tests/Feature/ListingCardFeatureTest.php tests/Feature/SavedSearchesFeatureTest.php tests/Feature/GuestCompatibilityFeatureTest.php tests/Feature/WaitlistFeatureTest.php tests/Feature/UsersProfilesPrivacyFoundationTest.php` - passed, 39 tests and 330 assertions.
- `php artisan test --compact tests/Feature/HostPropertyWizardTest.php tests/Feature/HostRoomFlowTest.php tests/Feature/HostSleepingPlaceFlowTest.php tests/Feature/HostListingsDashboardTest.php tests/Feature/HostListingWizardFeatureTest.php tests/Feature/AutomaticHostHintsFeatureTest.php` - passed, 37 tests and 243 assertions.
- `php artisan test --compact tests/Feature/SearchPageTest.php tests/Feature/PublicSleepingPlaceDetailTest.php tests/Feature/HostProfileFlowTest.php` - passed, 25 tests and 132 assertions.
- `php artisan optimize:clear` - passed.
- `git diff --check -- <second-pass touched files>` - passed.
- `php artisan test --compact` - passed, 432 tests and 11656 assertions.
- `npm run build` - passed.

## Remaining Unresolved

- Custom bottom-sheet containers still use the existing fixed-position sheet markup. Their backdrop dismiss controls now use `flux:button`, but converting the entire sheet lifecycle to `flux:modal` or Flux flyouts should be done in a separate acceptance-tested pass because several sheets are rendered by parent state instead of owning a local `wire:model` open property.
- Simple file inputs remain as `flux:input type="file"` in flows where the current UI is still a simple attachment field. The media manager now uses `flux:file-upload`; richer upload dropzones for complaint evidence, messages, reviews, avatar setup, and wizard photo staging should be handled in smaller workflow-specific passes because each has different preview/removal state.
- Some nested statistic tiles, sticky action regions, image placeholders, and legacy detail-page surfaces intentionally remain custom Tailwind markup to avoid Flux cards inside Flux cards or changing page structure without visual acceptance tests.
- No real-browser mobile visual regression pass was run in this migration. The static Flux guard, focused Livewire/feature tests, and Vite build passed.
