# Flux Pro Interface Migration

Reviewed: 2026-06-20
Updated: 2026-06-21

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

## Third Pass: Workflow Upload Surfaces

This pass upgraded the remaining Livewire workflow upload controls from simple file inputs to documented Flux Pro File Upload components. Existing Livewire properties, validation keys, storage actions, media replacement logic, and submit flows were preserved.

Additional saved references used:
- `docs/flux-ui-components.md` File Upload rules from `https://fluxui.dev/components/file-upload`

### What changed in the third pass

- Replaced review photo uploads, complaint evidence uploads, check-in problem photos, message attachments, guest avatar setup, host avatar setup, and host wizard photo slots with `flux:file-upload`.
- Added `flux:file-upload.dropzone` with documented `with-progress` and `inline` states for mobile-friendly upload progress on slow connections.
- Preserved existing upload bindings such as `photos`, `media`, `uploads`, `avatar`, and dynamic wizard fields like `entrancePhoto`, `roomPhoto`, and `exactPhoto`.
- Removed undocumented `accept` attributes from Flux upload components; file type and size limits remain enforced by Laravel/Livewire validation.
- Added a PHPUnit guard so known Livewire upload surfaces must use `flux:file-upload` and cannot drift back to `type="file"`.

### Additional files touched in the third pass

- `resources/views/livewire/account/profile-setup-page.blade.php`
- `resources/views/livewire/checkin/problem-report.blade.php`
- `resources/views/livewire/complaints/create-complaint.blade.php`
- `resources/views/livewire/host/partials/host-profile-section.blade.php`
- `resources/views/livewire/host/property-form.blade.php`
- `resources/views/livewire/host/room-form.blade.php`
- `resources/views/livewire/host/sleeping-place-form.blade.php`
- `resources/views/livewire/messages/chat-window.blade.php`
- `resources/views/livewire/reviews/create-review.blade.php`
- `tests/Feature/FluxProComponentUsageTest.php`

## Fourth Pass: Availability Calendar Components

This pass added Flux UI surfaces for the SleepingPlace availability calendar and host calendar editor from point 4. The new UI uses Livewire class components only and keeps business logic in availability services.

### What changed in the fourth pass

- Added guest availability components for sleeping-place calendar days, status badges, warnings, available checkout dates, nearest dates, and range summaries.
- Added host availability components for sleeping-place, room, and property calendar cards, day editing, period blocks, turnover rules, status legend, bulk actions, and occupancy summary.
- Used Flux `card`, `heading`, `text`, `badge`, `field`, `input`, `select`, `textarea`, `switch`, and `button` patterns.
- Preserved mobile-first cards instead of large host calendar tables.
- Kept visible text in English and Russian translation files.
- Added the database-backed availability foundation for point 4: turnover rules, calendar blocks, active date locks, status logs, and the SQLite partial unique index that prevents two active locks for the same sleeping place and date.
- Updated the bulk demo seeder so new availability records are populated without creating active demo blocks or partial-index conflicts.

### Additional files touched in the fourth pass

- `app/Livewire/Bookings/Availability/*`
- `app/Livewire/Host/Availability/*`
- `app/Models/SleepingPlaceAvailabilityStatusLog.php`
- `app/Models/SleepingPlaceBookingDateLock.php`
- `app/Models/SleepingPlaceCalendarBlock.php`
- `app/Models/SleepingPlaceTurnoverRule.php`
- `app/Services/Availability/*`
- `app/Services/HostCalendar/HostCalendarViewService.php`
- `app/Services/SleepingPlaces/SleepingPlaceAvailabilityService.php`
- `database/factories/SleepingPlaceAvailabilityStatusLogFactory.php`
- `database/factories/SleepingPlaceBookingDateLockFactory.php`
- `database/factories/SleepingPlaceCalendarBlockFactory.php`
- `database/factories/SleepingPlaceTurnoverRuleFactory.php`
- `database/migrations/2026_06_21_093551_add_sleeping_place_availability_lock_tables.php`
- `database/seeders/BulkMarketplaceSeeder.php`
- `docs/DATE_LOCKS_AND_DOUBLE_BOOKING.md`
- `docs/GUEST_CALENDAR.md`
- `docs/HOST_CALENDAR.md`
- `docs/SLEEPING_PLACE_AVAILABILITY.md`
- `docs/TURNOVER_RULES.md`
- `lang/en/availability.php`
- `lang/en/calendar.php`
- `lang/ru/availability.php`
- `lang/ru/calendar.php`
- `resources/views/livewire/bookings/availability/*`
- `resources/views/livewire/host/availability/*`
- `tests/Feature/SleepingPlaceAvailabilityCalendarTest.php`

### Fourth pass checks

- `php artisan test --compact tests/Feature/SleepingPlaceAvailabilityCalendarTest.php` - passed, 8 tests and 23 assertions.
- `php artisan test --compact tests/Unit/CoreMarketplaceSchemaTest.php --filter=test_core_marketplace_schema_contains_required_tables_columns_and_indexes` - passed, 1 test and 438 assertions.
- `php artisan test --compact tests/Unit/SleepingPlaceAvailabilityServiceTest.php --filter=test_checkout_same_day_as_next_checkin_is_allowed_when_boundary_rules_allow_it` - passed, 1 test and 1 assertion.
- `php artisan test --compact tests/Feature/BookingExtensionFlowTest.php` - passed, 6 tests and 33 assertions.
- `php artisan test --compact tests/Feature/DemoSeederTest.php --filter=test_database_seeder_creates_at_least_one_thousand_records_for_each_application_model` - passed, 1 test and 268 assertions.
- `php artisan test --compact tests/Feature/FluxProComponentUsageTest.php` - passed, 8 tests and 60 assertions.
- `./vendor/bin/pint --dirty --format agent` - passed and fixed formatting in `database/seeders/BulkMarketplaceSeeder.php`.
- `php artisan test --compact` - passed, 447 tests and 12055 assertions.
- `php artisan view:clear` - passed.
- `php artisan optimize:clear` - passed.
- `npm run build` - passed.

## Fifth Pass: Booking Date Selection and Quote Components

This pass added the mobile date-selection and BookingQuote preview UI for point 5. The new UI keeps date math, availability validation, pricing, timeline dates, and conversion in services while rendering the guest-facing surface with Flux components.

### What changed in the fifth pass

- Added `BookingQuote` schema, lines, validation results, timeline dates, and suggestions.
- Added date selection, stay length, quote summary, price-line breakdown, validation message, cancellation preview, timeline preview, expiration, and suggestions Livewire class components.
- Used Flux `card`, `field`, `input`, `textarea`, `switch`, `button`, `badge`, `callout`, `skeleton`, `heading`, and `text` components.
- Preserved the rule that quote preview does not lock dates; conversion rechecks availability and creates locks.
- Added English and Russian translations for all visible date and quote strings.
- Documented quote creation, stay length calculation, validation, timeline dates, and conversion.

### Additional files touched in the fifth pass

- `app/Livewire/Bookings/Dates/*`
- `app/Livewire/Bookings/Quotes/*`
- `app/Models/BookingQuote.php`
- `app/Models/BookingQuoteLine.php`
- `app/Models/BookingQuoteValidationResult.php`
- `app/Models/BookingTimelineDate.php`
- `app/Models/BookingQuoteSuggestion.php`
- `app/Services/Bookings/*Quote*`
- `app/Services/Bookings/BookingDateSelectionService.php`
- `app/Services/Bookings/BookingDateValidationService.php`
- `app/Services/Bookings/StayLengthCalculatorService.php`
- `database/factories/BookingQuote*.php`
- `database/factories/BookingTimelineDateFactory.php`
- `database/migrations/2026_06_21_102625_create_booking_quote_tables.php`
- `database/seeders/BulkMarketplaceSeeder.php`
- `docs/BOOKING_DATES_AND_QUOTES.md`
- `docs/STAY_LENGTH_CALCULATION.md`
- `docs/QUOTE_VALIDATION.md`
- `docs/QUOTE_TIMELINE_DATES.md`
- `docs/QUOTE_TO_BOOKING_CONVERSION.md`
- `lang/en/booking_dates.php`
- `lang/en/booking_quotes.php`
- `lang/ru/booking_dates.php`
- `lang/ru/booking_quotes.php`
- `resources/views/livewire/bookings/dates/*`
- `resources/views/livewire/bookings/quotes/*`
- `tests/Feature/BookingDatesAndQuotesFeatureTest.php`

### Fifth pass checks

- `php artisan test --compact tests/Feature/BookingDatesAndQuotesFeatureTest.php` - passed, 6 tests and 40 assertions.
- `php artisan test --compact tests/Feature/FluxProComponentUsageTest.php` - passed, 8 tests and 60 assertions.
- `php artisan test --compact tests/Unit/CoreMarketplaceSchemaTest.php --filter=test_core_marketplace_schema_contains_required_tables_columns_and_indexes` - passed, 1 test and 451 assertions.
- `php artisan test --compact tests/Feature/DemoSeederTest.php --filter=test_database_seeder_creates_at_least_one_thousand_records_for_each_application_model` - passed, 1 test and 278 assertions.
- `./vendor/bin/pint --dirty --format agent` - passed after formatting quote conversion, suggestion, and feature test files.
- `php artisan view:clear` - passed.
- `php artisan optimize:clear` - passed.
- `npm run build` - passed.
- `php artisan test --compact` - passed, 453 tests and 12343 assertions.

## Sixth Pass: Pricing Engine and Price Quote Components

This pass added the automatic pricing engine for point 6. Pricing is now calculated for a specific `SleepingPlace`, quote lines are rebuilt on each recalculation, and bookings receive immutable price snapshots.

### What changed in the sixth pass

- Added pricing settings, date prices, discount rules, promo codes, promo redemptions, and booking price snapshots.
- Added `BookingPriceQuoteEngine` plus focused services for date price resolution, discounts, promo codes, fees, deposit, service fees, taxes, host payout, refundability, and snapshots.
- Connected `BookingPriceQuoteService` to the new engine while preserving existing quote totals.
- Connected quote conversion to `BookingPriceSnapshotService`.
- Added mobile Flux pricing components for guest quote totals, nightly lines, discount lines, fee lines, deposit explanation, promo input, host payout preview, and host pricing editors.
- Added English and Russian `pricing.php` translations for all new visible strings.
- Updated bulk seeding so every new pricing model can satisfy the 1000-row model contract.

### Flux Pro components used in the sixth pass

- `flux:card`
- `flux:heading`
- `flux:text`
- `flux:field`
- `flux:label`
- `flux:input`
- `flux:error`
- `flux:switch`
- `flux:button`
- `flux:badge`
- `flux:callout`

### Sixth pass checks

- `php artisan test --compact tests/Feature/PricingEngineFeatureTest.php` - passed, 7 tests and 36 assertions.
- `php artisan test --compact tests/Feature/BookingDatesAndQuotesFeatureTest.php` - passed, 6 tests and 40 assertions.
- `php artisan test --compact tests/Feature/FluxProComponentUsageTest.php` - passed, 8 tests and 60 assertions.
- `php artisan test --compact tests/Unit/CoreMarketplaceSchemaTest.php --filter=test_core_marketplace_schema_contains_required_tables_columns_and_indexes` - passed, 1 test and 463 assertions.
- `php artisan test --compact tests/Feature/DemoSeederTest.php --filter=test_database_seeder_creates_at_least_one_thousand_records_for_each_application_model` - passed, 1 test and 290 assertions.
- `./vendor/bin/pint --dirty --format agent` - passed after formatting the pricing feature test.
- `rg -n "<button\\b|<input\\b|<select\\b|<textarea\\b|<details\\b|<summary\\b|<table\\b|<option\\b|@php" resources/views/livewire/bookings/pricing resources/views/livewire/host/pricing --glob '*.blade.php'` - no matches.
- `git diff --check` - passed.
- `php artisan view:clear` - passed.
- `php artisan optimize:clear` - passed.
- `npm run build` - passed.
- `php artisan test --compact` - passed, 460 tests and 12610 assertions.

### Remaining sixth pass notes

- Taxes and city fees are future-ready and return zero unless configured in pricing settings.
- Host service fee is calculated for payout preview and snapshot data, not exposed to guest-facing quote UI.

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

Third pass checks:
- `./vendor/bin/pint --format agent tests/Feature/FluxProComponentUsageTest.php` - passed.
- `rg -n "flux:input\\s+type=['\\\"]file['\\\"]|type=['\\\"]file['\\\"]" resources/views --glob '*.blade.php'` - no matches.
- `rg -n "<button\\b|<input\\b|<select\\b|<textarea\\b|<details\\b|<summary\\b|<table\\b|<option\\b" resources/views --glob '*.blade.php'` - no matches.
- `rg -n '^<(article|section|div) class=".*rounded-lg.*border' resources/views/livewire resources/views/components --glob '*.blade.php'` - no matches.
- `git diff --check` - passed.
- `php artisan view:clear` - passed.
- `php artisan test --compact tests/Feature/FluxProComponentUsageTest.php` - passed, 8 tests and 60 assertions.
- `php artisan test --compact tests/Feature/CompletedStayReviewFlowTest.php tests/Feature/ComplaintProblemReportFlowTest.php tests/Feature/MessageServiceTest.php tests/Feature/HostProfileFlowTest.php tests/Feature/HostPropertyWizardTest.php tests/Feature/HostRoomFlowTest.php tests/Feature/HostSleepingPlaceFlowTest.php tests/Feature/UsersProfilesPrivacyFoundationTest.php` - passed, 44 tests and 313 assertions.
- `php artisan test --compact` - passed, 438 tests and 11730 assertions.
- `php artisan optimize:clear` - passed.
- `npm run build` - passed.

## Remaining Unresolved

- Custom bottom-sheet containers still use the existing fixed-position sheet markup. Their backdrop dismiss controls now use `flux:button`, but converting the entire sheet lifecycle to `flux:modal` or Flux flyouts should be done in a separate acceptance-tested pass because several sheets are rendered by parent state instead of owning a local `wire:model` open property.
- No simple upload field is intentionally left as `flux:input type="file"` in the audited Livewire workflow views. Future file uploads should use the File Upload reference first and only fall back to simple `flux:input type="file"` when the project explicitly accepts a native upload surface.
- Some nested statistic tiles, sticky action regions, image placeholders, and legacy detail-page surfaces intentionally remain custom Tailwind markup to avoid Flux cards inside Flux cards or changing page structure without visual acceptance tests.
- No real-browser mobile visual regression pass was run in this migration. The static Flux guard, focused Livewire/feature tests, and Vite build passed.

Booking request pass:
- Added class-based Livewire request components under `resources/views/livewire/bookings/requests` and `resources/views/livewire/host/booking-requests`.
- The generated `resources/views/components/**/⚡*.blade.php` files from the default Livewire generator were removed immediately because this project allows class components only.
- New request views use Flux form controls and cards instead of native form controls or tables.
