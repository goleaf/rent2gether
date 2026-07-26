# Query Optimization And Booking Flow Continuation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use `executing-plans` or `project-architect` before continuing this plan. Keep each task small, tested, and compatible with the existing Livewire class-component architecture.

**Goal:** Снизить лишние запросы и пересчеты в поиске, календаре дат, availability, pricing и booking flows, не меняя стек и не создавая новые панели.

**Architecture:** Оптимизировать существующие сервисы, scopes, DTO и Livewire components. UI остается mobile-first, SSR Blade + Livewire 4. Business logic stays in services/actions. Blade only displays prepared DTO arrays and translated labels.

**Stack Boundary:** Laravel 13, PHP 8.5, Livewire 4, Flux Pro 2, SQLite. No Filament, Volt, Inertia, React/Vue, controllers, admin/staff/support/finance surfaces.

---

## Current Execution Status - 2026-07-26

- Task 1 is implemented in the current tree: closed search results can use a lower-bound total while the filter sheet keeps the exact CTA count.
- Task 2 is implemented in the current tree for the new search/access/property criteria migrations and index assertions.
- Task 3 is implemented in the current tree for repeated filter option DTOs, including the `compatibilityFitOptions()` computed access fix.
- Task 4 is implemented in the current tree for the guest checkout-calendar batch path; focused availability/date tests passed.
- Task 5 is implemented in the current tree for date-price range prefetching and quote total calculation; pricing-focused tests were added by the active branch work.
- Task 6 is implemented in the current tree for quote/request conversion: booking creation paths recheck availability inside transactions, date locks are backed by the active-lock unique index, and approved request conversion now has a regression test for dates becoming locked before booking creation.
- Task 7 is partially implemented across search/date/booking/property/compatibility UI surfaces; `DateSelectionPanel` no longer hydrates available checkout dates as public Livewire state and now passes them as render-only view data. 2026-07-26 continuation: legacy `CreateBooking` now keeps only locked `bedId` in public state, reloads a selected/eager-loaded `Bed` through `#[Computed]`, passes booking display data as render-only Blade variables, fixes the guest-count binding to `guestsCount`, and gives Blade render-ready price/compatibility summary DTOs instead of formatting money or score classes inline. Legacy `BedCard` now keeps only locked `bedId`/`nights` public, reloads selected bed/card media data through `#[Computed]`, and passes render-ready price strings to Blade. Legacy `BookingShow` now keeps only locked `bookingId` public, reloads selected/eager-loaded booking detail data through `#[Computed]`, and passes `booking` plus cancellation preview as render-only view data. `BookingReview` now keeps quote, availability warning, and unavailable-date results out of public Livewire state and passes them as render-only view data from a computed evaluation. `BookingDateSelector` now keeps quote, unavailable dates, and nearest ranges out of public Livewire state, recomputes them through `#[Computed]` so they survive a normal Livewire refresh, and reuses prefetched `availabilityDays` for stay-limit, availability-boundary, and pricing preview checks. `AvailabilityChecker` now keeps availability results out of public state and passes render-ready date/range labels to Blade. Check-in/check-out problem sheets no longer hydrate raw `photoPaths` storage paths as public Livewire state. Host Bulk Livewire target option lists are capped at the mobile budget of 30 records.
- Task 7 latest slices: canonical `ShowSleepingPlace` now keeps quote, availability warning, and unavailable-date results out of Livewire public state, passes booking preview data as render-only view variables, reuses `#[Computed]` place data, and prefetches the 14-day availability preview window so quote pricing and calendar preview use one indexed `availability_days` select on deep-linked date ranges. Legacy host `BedForm` now keeps only locked `roomId`/`bedId` identifiers in public state, reloads selected `Room`/`Bed` data through `#[Computed]`, rejects rooms owned by another host, and rejects nested bed edits when the bed does not belong to the routed room. Routed `WaitlistOfferPage` now keeps only locked `waitlistOfferId` in public state, reloads selected offer/item/place data through `#[Computed]`, and rechecks guest ownership before rendering or accepting/declining an offer. `DateSelectionPanel` now memoizes the selected `SleepingPlace` through `#[Computed]`, so initial quote creation and checkout-calendar rendering reuse one selected lookup during the same Livewire request instead of issuing two identical `sleeping_places` lookups. `BookingDateSelectionService` now passes the already-loaded `sleeping_places.user_id` directly into same-host alternative lookup, while `SleepingPlaceAvailabilitySuggestionService` accepts either a `User` or host id, removing a redundant `users` select from checkout-calendar rendering. Public `ShowProfile` now keeps only locked `userId` in public state and reloads selected user/profile/privacy data through `#[Computed]` so private user attributes do not hydrate into the browser snapshot. Host `HostTodayCheckOutCard` now keeps only nullable locked `checkOutId` in public state and reloads selected checkout/guest/room/place/step data through `#[Computed]`. Legacy `WaitlistManager` now keeps only locked `bedId` in public state and reloads selected `Bed` data through `#[Computed]`; the current Livewire public typed-model audit is clean. `ExtendStay` now keeps calculated extension preview data out of public Livewire state, serves booking/active extension/preview through selected `#[Computed]` loaders, and passes the preview to Blade as render-only view data. Host `HostCancellationDetailsSheet` now keeps booking/cancellation ids locked, authorizes host-owned booking/cancellation context before rendering, scopes every selected loader by `host_user_id`, and memoizes booking/cancellation/list data through `#[Computed]`. Guest `GuestCancellationPage` now keeps booking/preview/cancellation ids locked, authorizes guest-owned booking/preview/cancellation context before rendering, scopes selected loaders by `guest_user_id`, memoizes booking/preview/cancellation/list data through `#[Computed]`, keeps the new ownership regression test in the cancellation feature suite, and has current `php -l`, focused Pint, `npm run build`, `git diff --check`, Boost schema, `EXPLAIN QUERY PLAN`, focused owner-test, and full cancellation-flow evidence. `CheckInMediaUploader` now uses a bounded temporary `photo` upload, validates/stores the final file path server-side, and no longer exposes a public `path` property or path-bound input. Sleeping-place detail section components now keep only locked `sleepingPlaceId` in public state and build the guest summary profile through `#[Computed]`, keeping the large profile DTO render-only. Host hint card/details components now keep only locked `hintId` plus small UI state in public Livewire state, render the display DTO from private mount data or a selected owner-scoped `#[Computed]` fallback, and no longer hydrate host hint `message_key`/translated text payloads.
- Task 7 guest hint continuation: legacy `GuestHintsList` and `HintDetailsSheet` now store only locked compact hint references and render translated text server-side through `GuestHintPayloadFormatter`; `message_key` and translated hint text no longer hydrate into Livewire public snapshots.
- Task 7 message/search/favorite/availability payload continuation: stateless `MessageBubble` now keeps the incoming message DTO private and passes it as render-only view data, so message body text no longer hydrates into Livewire public snapshots. `SavedSearchCard` now keeps the display card DTO private, passes it as render-only view data, and exposes only locked `searchId` for actions. `FavoriteCard` now keeps only locked `favoriteId`/`placeId` plus small compare state public, keeps the incoming card DTO private/render-only, reloads a selected owner-scoped favorite card through `#[Computed]` and `FavoriteCardPresenter` after hydration, and no longer hydrates favorite notes or card display payloads into Livewire snapshots. Favorite card query selection/eager-loading now lives in reusable `FavoriteCardQuery`, so `FavoritesPage`, `FavoriteCollectionPage`, and hydrated `FavoriteCard` share one selected owner-scoped query graph instead of three duplicated copies. `Bookings\\Availability\\AvailabilityWarnings` now keeps warning reason keys private/render-only, so translated availability warnings render without hydrating reason keys into Livewire public snapshots. `SystemEventMessage` now keeps the translation key and params private/render-only, so translated system events render without hydrating event keys or parameter payloads into Livewire public snapshots. `Host\\SleepingPlaces\\SleepingPlaceCompletionPanel` now follows sibling completion panels by keeping only the locked sleeping-place id in public state, evaluating checklist/progress through `SleepingPlaceCompletionService::evaluate()`, and passing the translated completion DTO as render-only view data.
- Task 8 is partially complete: focused affected suites passed, `npm run build` passed, `git diff --check` passed, and touched PHP files pass Pint. A fresh full `php -d opcache.enable_cli=0 artisan test --compact` attempt on 2026-07-26 ended with SIGTERM (`signal 15`) before PHPUnit produced a pass/fail summary. Full `./vendor/bin/pint --test` still reports baseline formatting issues outside this focused slice.

Most recent focused evidence:

```bash
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/FluxProComponentUsageTest.php tests/Feature/LocalizationCatalogueTest.php tests/Feature/LocalizationFoundationTest.php tests/Feature/UserNotificationsTest.php tests/Feature/SearchPageTest.php tests/Feature/AmenityRuleSystemTest.php tests/Feature/BookingDateSelectorTest.php tests/Feature/BookingDatesAndQuotesFeatureTest.php tests/Feature/ExtendedPropertyFieldsTest.php
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/BookingRequestsFeatureTest.php --filter=approved_request_conversion_aborts
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/BookingDateSelectorTest.php --filter=date_selection_panel_exposes_smart_checkout_calendar_to_view
```

Result: 86 focused tests passed, 7171 assertions across the listed focused runs.

Task 7 focused evidence from the 2026-07-26 continuation:

```bash
php artisan test --compact tests/Feature/LegacyBedBookingPayloadTest.php
php artisan test --compact tests/Feature/LegacyBedBookingPayloadTest.php --filter=legacy_bed_card_keeps
php artisan test --compact tests/Feature/LegacyBedBookingPayloadTest.php tests/Feature/MobilePerformanceBudgetTest.php
php artisan test --compact tests/Feature/LegacyBookingShowPayloadTest.php --filter=keeps_the_booking_model_out
php artisan test --compact tests/Feature/LegacyBookingShowPayloadTest.php
php artisan test --compact tests/Feature/BookingReviewPayloadTest.php
php artisan test --compact tests/Feature/BookingReviewPayloadTest.php tests/Feature/MobileBookingFlowTest.php tests/Feature/FullIntegrationPassTest.php
php artisan test --compact tests/Feature/HostBulkManagementFeatureTest.php --filter=bulk_livewire_components_render
php artisan test --compact tests/Feature/BookingDateSelectorTest.php --filter=keeps_quote_out_of_livewire_public_state
php artisan test --compact tests/Feature/BookingDateSelectorTest.php --filter=reuses_prefetched_availability_days
php artisan test --compact tests/Feature/BookingDateSelectorTest.php
php artisan test --compact tests/Feature/BookingDateSelectorTest.php tests/Feature/BookingDatesAndQuotesFeatureTest.php tests/Feature/MobilePerformanceBudgetTest.php
php artisan test --compact tests/Feature/AvailabilityCalendarFlowTest.php --filter=guest_availability_checker_keeps_results_out
php artisan test --compact tests/Feature/AvailabilityCalendarFlowTest.php
php artisan test --compact tests/Feature/MobilePerformanceBudgetTest.php
php artisan test --compact tests/Feature/BookingCheckInFeatureTest.php --filter=keeps_photo_paths_out
php artisan test --compact tests/Feature/BookingCheckOutFeatureTest.php --filter=keeps_photo_paths_out
php artisan test --compact tests/Feature/BookingCheckInFeatureTest.php tests/Feature/BookingCheckOutFeatureTest.php
php artisan test --compact tests/Feature/PublicSleepingPlaceDetailTest.php --filter=selected_dates_update_price
php artisan test --compact tests/Feature/PublicSleepingPlaceDetailTest.php --filter=reuses_prefetched_availability_days
php artisan test --compact tests/Feature/PublicSleepingPlaceDetailTest.php
php artisan test --compact tests/Feature/FullIntegrationPassTest.php tests/Feature/PublicSleepingPlaceDetailTest.php
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/LegacyHostBedFormPayloadTest.php
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/LegacyHostBedFormPayloadTest.php tests/Feature/LegacyBookingShowPayloadTest.php tests/Feature/LegacyBedBookingPayloadTest.php
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/WaitlistFeatureTest.php --filter=keeps_offer_model_out
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/WaitlistFeatureTest.php
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/BookingDateSelectorTest.php --filter=reuses_sleeping_place_lookup
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/BookingDateSelectorTest.php
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/BookingDatesAndQuotesFeatureTest.php --filter='checkout_candidate_availability_prefetches_date_range_once|quote_livewire_components_render'
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/BookingDatesAndQuotesFeatureTest.php --filter=uses_place_host_id_without_loading_host_model
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/BookingDatesAndQuotesFeatureTest.php --filter='check_in_calendar_returns_bounds|uses_place_host_id_without_loading_host_model|checkout_candidate_availability_prefetches_date_range_once|quote_livewire_components_render'
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/ProfileShowPayloadTest.php
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/CompletedStayReviewFlowTest.php --filter=public_listing_reviews_and_profile_summary_show_visible_reviews
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/HostTodayCheckOutCardPayloadTest.php
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/LegacyWaitlistManagerPayloadTest.php
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/LegacyHostBedFormPayloadTest.php tests/Feature/WaitlistFeatureTest.php tests/Feature/ProfileShowPayloadTest.php tests/Feature/HostTodayCheckOutCardPayloadTest.php tests/Feature/LegacyWaitlistManagerPayloadTest.php
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/BookingExtensionFlowTest.php --filter=keeps_preview_out_of_livewire_public_state
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/BookingExtensionFlowTest.php
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/FullIntegrationPassTest.php --filter=guest_can_complete_the_core_booking_stay_and_review_flow
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/BookingCancellationFlowPointFifteenTest.php --filter=host_cancellation_sheet_rejects_cancellation_owned_by_another_host
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/BookingCancellationFlowPointFifteenTest.php
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/BookingCancellationFlowPointFifteenTest.php --filter=guest_cancellation_page_rejects_context_owned_by_another_guest
php -d opcache.enable_cli=0 artisan test --compact tests/Feature/BookingCancellationFlowPointFifteenTest.php
php -d opcache.enable_cli=0 artisan test tests/Feature/BookingCheckInFlowPointTenTest.php --filter=check_in_media_uploader_stores_photo_without_exposing_final_path_in_public_state --compact --stop-on-failure
php -d opcache.enable_cli=0 artisan test tests/Feature/BookingCheckInFlowPointTenTest.php --compact
php -d opcache.enable_cli=0 artisan test tests/Feature/ExtendedSleepingPlaceFieldsTest.php --filter=detail_sections_keep_profile_summary_out_of_public_state --compact --stop-on-failure
php -d opcache.enable_cli=0 artisan test tests/Feature/ExtendedSleepingPlaceFieldsTest.php --compact
php -d opcache.enable_cli=0 artisan test tests/Feature/AutomaticHostHintsFeatureTest.php --filter=host_hint_card_keeps_display_payload_out_of_public_state --compact --stop-on-failure
php -d opcache.enable_cli=0 artisan test tests/Feature/AutomaticHostHintsFeatureTest.php --filter=host_hint_details_sheet_keeps_display_payload_out_of_public_state --compact --stop-on-failure
php -d opcache.enable_cli=0 artisan test tests/Feature/AutomaticHostHintsFeatureTest.php --filter='host_hint_(card|details_sheet)_keeps_display_payload_out_of_public_state' --compact --stop-on-failure
php -d opcache.enable_cli=0 artisan test tests/Feature/AutomaticHostHintsFeatureTest.php --compact
php -d opcache.enable_cli=0 artisan test tests/Feature/GuestHintPayloadComponentsTest.php --filter=guest_hints_list_keeps_display_payload_out_of_public_state --compact --stop-on-failure
php -d opcache.enable_cli=0 artisan test tests/Feature/GuestHintPayloadComponentsTest.php --filter=hint_details_sheet_keeps_display_payload_out_of_public_state --compact --stop-on-failure
php -d opcache.enable_cli=0 artisan test tests/Feature/GuestHintPayloadComponentsTest.php --compact
php -d opcache.enable_cli=0 artisan test tests/Feature/MessageBubblePayloadTest.php --filter=message_bubble_keeps_message_body_out_of_public_state --compact --stop-on-failure
php -d opcache.enable_cli=0 artisan test tests/Feature/MessageBubblePayloadTest.php --compact
php artisan test --compact tests/Feature/SavedSearchCardPayloadTest.php
php artisan test --compact tests/Feature/MessageBubblePayloadTest.php tests/Feature/SavedSearchCardPayloadTest.php
php -d opcache.enable_cli=0 artisan test tests/Feature/FavoriteCardPayloadTest.php --filter=favorite_card_keeps_display_payload_out_of_public_state --compact --stop-on-failure
php -d opcache.enable_cli=0 artisan test tests/Feature/FavoriteCardPayloadTest.php --compact --stop-on-failure
php artisan test --compact tests/Feature/FavoriteCardPayloadTest.php
php artisan test --compact tests/Feature/MessageBubblePayloadTest.php tests/Feature/SavedSearchCardPayloadTest.php tests/Feature/FavoriteCardPayloadTest.php
php -d opcache.enable_cli=0 artisan test tests/Feature/FavoritesCollectionsFeatureTest.php --filter=collection_page_and_toggle_render --compact --stop-on-failure
php -d opcache.enable_cli=0 artisan test tests/Feature/AvailabilityWarningsPayloadTest.php --filter=availability_warnings_keep_reason_keys_out_of_public_state --compact --stop-on-failure
php -d opcache.enable_cli=0 artisan test tests/Feature/AvailabilityWarningsPayloadTest.php --compact --stop-on-failure
php -d opcache.enable_cli=0 artisan test tests/Feature/SystemEventMessagePayloadTest.php --filter=system_event_message_keeps_translation_params_out_of_public_state --compact --stop-on-failure
php -d opcache.enable_cli=0 artisan test tests/Feature/SystemEventMessagePayloadTest.php --compact --stop-on-failure
php -d opcache.enable_cli=0 artisan test tests/Feature/SleepingPlaceCompletionPanelPayloadTest.php --filter=sleeping_place_completion_panel_keeps_items_out_of_public_state --compact --stop-on-failure
php -d opcache.enable_cli=0 artisan test tests/Feature/SleepingPlaceCompletionPanelPayloadTest.php --compact --stop-on-failure
php -d opcache.enable_cli=0 artisan test tests/Feature/ExtendedSleepingPlaceFieldsTest.php --filter=test_host_extended_sleeping_place_steps_update_data_and_block_other_hosts --compact --stop-on-failure
./vendor/bin/pint app/Livewire/Booking/BookingReview.php tests/Feature/BookingReviewPayloadTest.php
./vendor/bin/pint app/Livewire/Booking/BookingDateSelector.php app/Services/Availability/AvailabilityService.php tests/Feature/BookingDateSelectorTest.php
./vendor/bin/pint app/Livewire/Places/ShowSleepingPlace.php tests/Feature/PublicSleepingPlaceDetailTest.php
./vendor/bin/pint --test app/Livewire/Booking/BookingShow.php tests/Feature/LegacyBookingShowPayloadTest.php
./vendor/bin/pint --test app/Livewire/Search/BedCard.php tests/Feature/LegacyBedBookingPayloadTest.php
./vendor/bin/pint --test app/Livewire/Booking/CreateBooking.php app/Livewire/Host/Bulk/BaseHostBulkComponent.php tests/Feature/LegacyBedBookingPayloadTest.php
./vendor/bin/pint --test app/Livewire/Booking/AvailabilityChecker.php app/Livewire/Bookings/CheckIn/CheckInProblemReportSheet.php app/Livewire/Bookings/CheckOut/CheckOutIssueReportSheet.php tests/Feature/AvailabilityCalendarFlowTest.php tests/Feature/BookingCheckInFeatureTest.php tests/Feature/BookingCheckOutFeatureTest.php
./vendor/bin/pint --test app/Livewire/Host/BedForm.php tests/Feature/LegacyHostBedFormPayloadTest.php
./vendor/bin/pint --test app/Livewire/Waitlist/WaitlistOfferPage.php tests/Feature/WaitlistFeatureTest.php
./vendor/bin/pint --test app/Livewire/Bookings/Dates/DateSelectionPanel.php tests/Feature/BookingDateSelectorTest.php
./vendor/bin/pint --test app/Livewire/Bookings/Dates/DateSelectionPanel.php app/Services/Bookings/BookingDateSelectionService.php app/Services/Availability/SleepingPlaceAvailabilitySuggestionService.php tests/Feature/BookingDateSelectorTest.php tests/Feature/BookingDatesAndQuotesFeatureTest.php
./vendor/bin/pint --test app/Livewire/Profile/ShowProfile.php tests/Feature/ProfileShowPayloadTest.php
./vendor/bin/pint --test app/Livewire/Host/CheckOut/HostTodayCheckOutCard.php tests/Feature/HostTodayCheckOutCardPayloadTest.php
./vendor/bin/pint --test app/Livewire/Waitlist/WaitlistManager.php tests/Feature/LegacyWaitlistManagerPayloadTest.php
./vendor/bin/pint --test app/Livewire/Extensions/ExtendStay.php tests/Feature/BookingExtensionFlowTest.php
./vendor/bin/pint --test app/Livewire/Host/Cancellations/HostCancellationDetailsSheet.php tests/Feature/BookingCancellationFlowPointFifteenTest.php
./vendor/bin/pint --test app/Livewire/Bookings/Cancellations/GuestCancellationPage.php tests/Feature/BookingCancellationFlowPointFifteenTest.php
php -l app/Livewire/Bookings/Cancellations/GuestCancellationPage.php
git diff --check -- app/Livewire/Bookings/Cancellations/GuestCancellationPage.php tests/Feature/BookingCancellationFlowPointFifteenTest.php
./vendor/bin/pint --test app/Livewire/Bookings/CheckIn/CheckInMediaUploader.php tests/Feature/BookingCheckInFlowPointTenTest.php lang/en/check_in.php lang/ru/check_in.php
./vendor/bin/pint --test app/Livewire/Listings/Detail/Concerns/LoadsSleepingPlaceProfileSection.php tests/Feature/ExtendedSleepingPlaceFieldsTest.php
./vendor/bin/pint --test app/Livewire/Host/Hints/HostHintCard.php app/Livewire/Host/Hints/HostHintDetailsSheet.php tests/Feature/AutomaticHostHintsFeatureTest.php
./vendor/bin/pint --test app/Livewire/Hints/GuestHintsList.php app/Livewire/Hints/HintDetailsSheet.php app/Services/Hints/GuestHintPayloadFormatter.php tests/Feature/GuestHintPayloadComponentsTest.php tests/Feature/AutomaticGuestHintsFeatureTest.php
./vendor/bin/pint --test app/Livewire/Messages/MessageBubble.php tests/Feature/MessageBubblePayloadTest.php
./vendor/bin/pint --test app/Livewire/SavedSearches/SavedSearchCard.php tests/Feature/SavedSearchCardPayloadTest.php
./vendor/bin/pint --test app/Livewire/Favorites/FavoriteCard.php tests/Feature/FavoriteCardPayloadTest.php
./vendor/bin/pint --test app/Services/Favorites/FavoriteCardQuery.php app/Livewire/Favorites/FavoriteCard.php app/Livewire/Favorites/FavoritesPage.php app/Livewire/Favorites/FavoriteCollectionPage.php tests/Feature/FavoriteCardPayloadTest.php
./vendor/bin/pint --test app/Livewire/Bookings/Availability/AvailabilityWarnings.php tests/Feature/AvailabilityWarningsPayloadTest.php
./vendor/bin/pint --test app/Livewire/Messages/SystemEventMessage.php tests/Feature/SystemEventMessagePayloadTest.php
./vendor/bin/pint --test app/Livewire/Host/SleepingPlaces/SleepingPlaceCompletionPanel.php app/Services/SleepingPlaces/SleepingPlaceCompletionService.php resources/views/livewire/host/sleeping-places/sleeping-place-completion-panel.blade.php tests/Feature/SleepingPlaceCompletionPanelPayloadTest.php
./vendor/bin/pint --test app/Livewire/Bookings/Cancellations/GuestCancellationPage.php app/Livewire/SavedSearches/SavedSearchCard.php app/Livewire/Messages/MessageBubble.php tests/Feature/BookingCancellationFlowPointFifteenTest.php tests/Feature/SavedSearchCardPayloadTest.php tests/Feature/MessageBubblePayloadTest.php
npm run build
git diff --check
rg -n 'wire:model(\.blur|\.change)?="path"|public string \$path|public \?string \$path|public array \$profile' app/Livewire resources/views/livewire
rg -n 'public array \$hint|public array \$hints' app/Livewire/Host/Hints app/Livewire/Hints
rg -n 'public array \$message' app/Livewire/Messages
rg -n 'public array \$card|public array \$message' app/Livewire/Favorites app/Livewire/Messages
rg -n 'public array \$reasons' app/Livewire/Bookings/Availability app/Livewire/Bookings/Dates app/Livewire/Bookings/Extensions
rg -n 'public array \$params|public string \$translationKey' app/Livewire/Messages
rg -n 'public array \$items|public int \$percentage' app/Livewire/Host/SleepingPlaces/SleepingPlaceCompletionPanel.php
```

Result: 221 focused/related test-case executions passed across the listed Task 7 continuation runs. `LegacyBedBookingPayloadTest` red/green coverage passed with 3 tests / 17 assertions, including the legacy `BedCard` payload regression. `LegacyBookingShowPayloadTest` passed with 1 test / 8 assertions after first exposing a missing render-only `cancellationPreview` variable in the legacy view. `BookingDateSelectorTest` red/green payload coverage passed with 1 test / 9 assertions after initially proving private quote data disappeared on refresh; the query-budget test then proved `availability_days` selects dropped from 5 to the budget of at most 2. The full `BookingDateSelectorTest` passed with 10 tests / 50 assertions, and the related booking-date/quote/performance run passed with 24 tests / 125 assertions. `AvailabilityCalendarFlowTest` red/green payload coverage passed with 12 tests / 46 assertions, and `MobilePerformanceBudgetTest` passed with 6 tests / 19 assertions after the availability slice. Check-in/check-out raw media-path payload coverage passed with 14 tests / 137 assertions. The combined legacy payload/performance run passed with 8 tests / 27 assertions after capping Host Bulk Livewire target option lists to 30. The Host Bulk render smoke passed with 1 test / 17 assertions. Latest focused `BookingReview` regression passed with 1 test / 8 assertions, and the related booking-flow regression passed with 11 tests / 2455 assertions. `ShowSleepingPlace` red/green payload coverage passed with 1 test / 8 assertions after first proving the snapshot contained `quote`; its query-budget coverage then passed with 1 test / 3 assertions after first proving the page used 2 `availability_days` selects for a deep-linked date range. The full `PublicSleepingPlaceDetailTest` passed with 9 tests / 61 assertions, and the related integration/detail run passed with 12 tests / 2471 assertions. `LegacyHostBedFormPayloadTest` passed with 3 tests / 13 assertions after first proving the component still exposed model-backed state and skipped host/nested-room guards; the combined legacy payload run passed with 7 tests / 38 assertions. `WaitlistFeatureTest` red/green offer payload coverage passed with 1 test / 8 assertions after first proving `waitlistOfferId` was absent from public state, and the full waitlist feature suite passed with 8 tests / 59 assertions. The latest `DateSelectionPanel` query-budget regression first failed with 2 selected `sleeping_places` lookups, then passed with 1 lookup after moving the selected place loader to `#[Computed]`; the full `BookingDateSelectorTest` passed with 11 tests / 55 assertions. The same-host alternatives regression first failed with 1 redundant `users` select, then passed with 0 `users` selects while still returning same-host alternatives; four related checkout-calendar/quote checks passed with 4 tests / 22 assertions. `ProfileShowPayloadTest` passed with 1 test / 8 assertions after first proving `userId` was absent from public state, and the existing completed-stay profile route regression passed with 1 test / 5 assertions. `HostTodayCheckOutCardPayloadTest` passed with 1 test / 9 assertions after first proving `checkOutId` was absent from public state. `LegacyWaitlistManagerPayloadTest` passed with 1 test / 7 assertions after first proving `bedId` was absent from public state. `ExtendStay` red/green payload coverage passed with 1 test / 8 assertions after first proving the snapshot contained `preview`; the full extension flow passed with 7 tests / 41 assertions, and the guest integration flow passed with 1 test / 45 assertions. `HostCancellationDetailsSheet` owner coverage first failed with 200 instead of 403 for another host's cancellation, then passed with 1 test / 1 assertion; the full cancellation flow suite passed with 10 tests / 52 assertions. `GuestCancellationPage` owner coverage first failed with 200 instead of 403 for another guest's booking/preview/cancellation, then passed with 1 test / 3 assertions; the full cancellation flow suite now passes with 11 tests / 55 assertions. `CheckInMediaUploader` red/green coverage first failed because the component lacked `WithFileUploads`, then passed with 1 test / 10 assertions; the related `BookingCheckInFlowPointTenTest` passed with 7 tests / 76 assertions. Sleeping-place detail section profile payload coverage first failed with the full `profile` DTO in the snapshot, then passed with 1 test / 4 assertions; the full `ExtendedSleepingPlaceFieldsTest` passed with 9 tests / 111 assertions. `HostHintCard` payload coverage first failed with the full `hint` display array in the snapshot, then passed with 1 test / 4 assertions. `HostHintDetailsSheet` payload coverage first failed with the full `hint` display array in the snapshot, then passed with 1 test / 5 assertions; the combined host-hint payload run passed with 2 tests / 9 assertions and the full `AutomaticHostHintsFeatureTest` passed with 8 tests / 65 assertions. `GuestHintPayloadComponentsTest` first failed with `GuestHintsList` exposing full `hints` display data in the snapshot, then passed with 2 tests / 10 assertions after moving guest hint list/details display payloads to locked compact references. The first attempted placement of those payload tests inside the heavy `AutomaticGuestHintsFeatureTest` suite hit SIGTERM/exit 137 before assertions because `RefreshDatabase` was unnecessary for this payload-only check; the final lightweight suite is the authoritative evidence. The combined host/waitlist/profile payload run passed with 14 tests / 96 assertions, and the Livewire public typed-model `rg` audit returned no matches. Touched PHP files passed Pint, `npm run build` passed, direct public `path`/`profile` audit returned no matches, the guest/host hint public-array audit now returns no matches, the `availability_days` index check confirmed `sleeping_place_id + date` and `sleeping_place_id + date + status` indexes, and `git diff --check` passed.

Additional latest evidence: `MessageBubblePayloadTest` first failed with the message body in the Livewire snapshot, then passed with 1 test / 3 assertions after moving the incoming message DTO to private render-only state. `SavedSearchCardPayloadTest` first failed with the saved-search display card in the Livewire snapshot, then passed with 1 test / 5 assertions after moving the card DTO to private render-only state. The combined message/saved-search payload run passed with 2 tests / 8 assertions. `FavoriteCardPayloadTest` first failed with the full favorite card payload in the Livewire snapshot, then passed with 2 tests / 10 assertions after moving the card DTO to render-only state and adding locked `favoriteId`/`placeId`; the reusable query-service continuation then first failed with missing `FavoriteCardQuery`, then passed with 3 tests / 20 assertions after centralizing selected owner-scoped favorite-card query/eager-load construction. The combined message/saved-search/favorite payload run passed with 5 tests / 28 assertions. The existing favorite collection render/toggle smoke passed with 1 test / 6 assertions, and the favorite/message public-card audit returns no matches. `AvailabilityWarningsPayloadTest` first failed with `reasons` and `range_overlaps_existing_booking` in the Livewire snapshot, then passed with 1 test / 3 assertions after moving warning reason keys to private render-only state; the bookings warning public-reasons audit returns no matches. `SystemEventMessagePayloadTest` first failed with `translationKey`, `params`, and a private parameter value in the Livewire snapshot, then passed with 1 test / 4 assertions after moving system-event translation data to private render-only state; the messages public params/translation-key audit returns no matches. `SleepingPlaceCompletionPanelPayloadTest` first failed with `items`, completion labels, and `percentage` in the Livewire snapshot, then passed with 1 test / 7 assertions after moving completion checklist/progress data to render-only view data; the full new payload file passed with 1 test / 7 assertions, the existing host extended sleeping-place flow passed with 1 test / 16 assertions, and the completion-panel public-items/percentage audit returns no matches.

Current broader verification note:

```bash
php -d opcache.enable_cli=0 artisan test --compact
npm run build
git diff --check
./vendor/bin/pint --test
```

Result: `npm run build` passed, `git diff --check` passed, and the focused completion-panel tests passed. The full `php -d opcache.enable_cli=0 artisan test --compact` attempt ended with Symfony `ProcessSignaledException` signal 15 before PHPUnit produced a pass/fail summary. Full Pint still fails on pre-existing/baseline formatting issues outside this focused slice, including old factory import ordering and unrelated service/test style fixes. Do not format the whole dirty tree unless that becomes the next explicit cleanup slice.

Next unblocked commands:

```bash
./vendor/bin/pint
git diff --check
```

---

## Task 1: Search Query Baseline And Count Optimization

**Problem:** `SleepingPlaceSearch::searchResults()` runs a full exact `count()` query on every render before fetching visible cards. This is expensive because the same query joins and eager-loads listing context, and most mobile states only need the first visible page plus whether there are more results.

**Implementation:**
- Keep exact totals only when the filter sheet is open because the CTA displays `show_results(count)`.
- For the main search list, fetch `visibleCount + 1` rows and infer `has_more`.
- Return `total_is_exact` so the view can choose exact vs lower-bound copy later.
- Preserve existing `showing`, `cards`, and `has_more` keys.

**Tests:**
- Keep the existing filter-sheet exact count test.
- Add a Livewire test that closed search results use a lower-bound total when more than the visible limit exists.
- Run `php artisan test --compact tests/Feature/SearchPageTest.php`.

## Task 2: Stabilize Search Criteria Indexes

**Problem:** New advanced filters add many boolean and enum predicates across `properties`, `rooms`, access details, condition details, and pivots. Missing or inconsistent migrations break `RefreshDatabase` and make filter queries drift.

**Implementation:**
- Ensure all search-criteria migration files exist and are non-empty.
- Keep indexes additive and named.
- Add indexes for hot filters only: `status + field`, `property_id + field`, `room_id + field`, pivot `amenity_id/rule_id + owner id`, and `sleeping_place_id + date` calendar lookups.
- Do not add broad duplicate indexes that SQLite cannot use effectively.

**Tests:**
- Keep schema/index assertions in `SearchPageTest`.
- Run migrations through the focused tests.

## Task 3: Filter Option Computed DTOs

**Problem:** Search filter option arrays are recomputed from Blade calls during render.

**Implementation:**
- Convert static option builders used repeatedly by Blade to `#[Computed]` methods or private cached arrays where appropriate.
- Keep the DTOs small and translated server-side.
- Avoid storing large option lists in public Livewire properties.

**Tests:**
- Feature test that filter labels still render in `en` and `ru`.

## Task 4: Availability Range Query Tightening

**Problem:** Availability checks must stay per `sleeping_place_id + date`, and date changes must not load whole calendars.

**Current audit finding:** `BookingDateSelectionService::checkoutCalendar()` checks up to 30 checkout candidates, and each candidate currently calls range availability services that repeat booking, active lock, calendar block, availability-day, calendar-day, check-in restriction, check-out restriction, status, and turnover queries. This is an `N x range-check` pattern on the exact mobile flow where the guest is changing dates.

**Implementation:**
- Audit `AvailabilityService`, `SleepingPlaceAvailabilitySuggestionService`, and date selector services.
- Ensure every range check uses indexed `sleeping_place_id + date` predicates and selected columns.
- Add a batch checkout-candidate analysis for a single check-in date so bookings, locks, blocks, availability days, and calendar days are prefetched once for the maximum candidate window.
- Keep strict `AvailabilityService::isAvailable()` and `getBlockingReasons()` for transactional booking guards; use the batch path for the guest checkout-calendar UI.
- Keep unavailable reason DTOs compact with translation keys.
- Use `[check_in_date, check_out_date)` everywhere.
- Record a follow-up migration candidate for `sleeping_place_calendar_days(sleeping_place_id, date, status)` if calendar-day status queries remain hot after batching.

**Tests:**
- Unit tests for overlap, same-day boundary, cleaning gap, room repair, and host closures.
- Feature test for available checkout dates.
- Add a query-budget regression test for checkout-calendar rendering so the UI path cannot silently return to per-candidate query growth.

## Task 5: Pricing Query And DTO Optimization

**Problem:** Price recalculation runs whenever dates, guests, timing options, or promo codes change. It must avoid repeated relationship queries and inline view math.

**Current audit finding:** `NightlyPriceLineService` resolved date-specific prices one night at a time through `DatePriceResolverService::getDateOverride()`, so a 30-night quote generated 30+ `sleeping_place_date_prices` selects even though the UI needed one contiguous `[check_in, check_out)` range. `BookingPriceQuoteEngine` also recalculated totals with 20 `SUM()` queries against `booking_quote_lines` that had just been created in the same request.

**Implementation:**
- Ensure `BookingPriceQuoteEngine` receives preloaded sleeping place, room, property, price rules, and scoped availability lines.
- Keep daily price lines as compact arrays.
- Persist price lines only when a booking/request is created.
- Keep quote preview transient.
- Add range-prefetch pricing resolution for date overrides using indexed `sleeping_place_id + date` predicates.
- Calculate quote-line totals from the in-memory created line collection during the same recalculation instead of rereading `booking_quote_lines` aggregates.

**Tests:**
- Unit tests for weekday/weekend/holiday/date override priority.
- Unit tests for weekly/monthly discounts, deposit, service fee, cleaning fee, refundable/non-refundable totals.
- Query-budget regression: long quote recalculation uses at most one `sleeping_place_date_prices` select and no `SUM()` selects from freshly created `booking_quote_lines`.

## Task 6: Booking Creation Transaction Audit

**Problem:** Double booking protection must recheck availability immediately before creating holds/bookings.

**Current audit finding:** `BookingQuoteConversionService` already locks the quote row, recalculates the quote, rechecks availability, and then creates booking locks inside the conversion transaction. The hot query path was lower in `SleepingPlaceDateLockService::createLockRows()`: it checked existing active locks one date at a time, so long booking/request ranges generated one `sleeping_place_booking_date_locks` select per night before inserting holds.

**Implementation:**
- Audit booking request and booking creation actions for transaction boundaries.
- Re-run date validation and availability lock checks inside the transaction.
- Keep status, payment status, flow, payment/deposit mode, and modifiers separate.
- Batch-prefetch existing active date locks for the full `[check_in, check_out)` range using indexed `sleeping_place_id + date` predicates.
- Preserve the unique `sleeping_place_id + date` active-lock constraint as the final race protection during inserts.

**Tests:**
- Race-style feature tests for overlapping booking and hold ranges.
- Boundary test for checkout on July 15 and next check-in on July 15 when turnover rules allow it.
- Query-budget regression: creating 30 booking date locks performs at most one select against `sleeping_place_booking_date_locks` before inserts.

## Task 7: Livewire Payload Audit

**Problem:** Mobile-first Livewire pages should not hydrate large models, galleries, calendars, or hidden filter trees.

**Current audit findings:**
- Highest-priority active patch candidate was the legacy `/beds/{bed}/book` flow: `CreateBooking` hydrated a full public `Bed` model with `room.property.host`, which exposed model metadata and relationship state in the Livewire snapshot and could lose selected-query constraints across requests.
- Booking request host/guest components already mostly keep scalar request IDs publicly and load Eloquent data in render-only paths.
- `SleepingPlaceSearch` already uses scalar URL/filter state and computed result/option DTOs after the earlier search payload work.
- Remaining candidates for later passes: internal-note and exact-address form state need a deliberate UX/security design because they are user-entered form bodies but still browser-readable Livewire state; any still-routed legacy Livewire bridge components that hydrate full Eloquent models remain high priority.

**Implementation:**
- Audit public properties in search, date selector, booking create, host request screens.
- Keep only scalar IDs, dates, booleans, small strings, and bounded arrays public.
- Move derived display values to computed properties or services.
- Use lazy/defer/islands only for below-the-fold expensive sections.
- Convert legacy `CreateBooking` from public `Bed` model hydration to locked scalar `bedId`.
- Reload the selected `Bed` through a computed property with explicit `select()` and constrained eager loads for `room` and `room.property`.
- Pass `$bed`, `$priceBreakdown`, and `$compatibility` as render-only Blade data instead of relying on public model state or implicit computed variables.
- Fix the Blade guest count binding from `guestCount` to `guestsCount`.
- Convert legacy `BedCard` from public `Bed` model hydration to locked scalar `bedId`, selected/eager-loaded computed bed data, and render-ready price strings.
- Convert legacy `BookingShow` from public `Booking` model hydration to locked scalar `bookingId`, selected/eager-loaded computed booking data, and explicit render-only view variables.
- Convert `BookingReview` quote and availability preview from public hydrated arrays to `#[Computed]` render-only evaluation data.
- Keep `BookingReview` public state to scalar IDs, dates, times, booleans, and short strings.
- Convert `BookingDateSelector` quote, unavailable dates, and nearest ranges from public hydrated arrays to render-only request data passed from `render()`.
- Convert `AvailabilityChecker` from public `result` hydration to a computed render-only availability result and render-ready date/range labels.
- Remove raw `photoPaths` storage path arrays from check-in/check-out problem report sheet public state; media attachment should stay behind dedicated upload/media services instead of browser-visible path arrays.
- Convert legacy host `BedForm` from public `Room`/`Bed` model hydration to locked scalar `roomId`/`bedId`, selected computed reloads, route-time host ownership authorization, and nested bed/room consistency checks before edit/update.
- Convert routed `WaitlistOfferPage` from public `WaitlistOffer` model hydration to locked scalar `waitlistOfferId`, selected computed reloads for offer/item/place display, and ownership checks before rendering or mutating offer state.
- Convert public `ShowProfile` from public `User` model hydration to locked scalar `userId`, selected computed reloads for user/profile/privacy data, and render-only `$user` view data.
- Convert host `HostTodayCheckOutCard` from nullable public `BookingCheckOut` model hydration to nullable locked scalar `checkOutId`, selected computed reloads, and render-only checkout view data.
- Convert legacy `WaitlistManager` from public `Bed` model hydration to locked scalar `bedId`, selected computed reloads, and ID-based waitlist entry actions.
- Convert `ExtendStay` from public preview-array hydration to computed render-only preview data, with selected computed booking/active-extension loaders and cache invalidation after submit, payment, and cancellation actions.

**Tests:**
- Livewire feature tests for major pages.
- Build check with `npm run build`.
- `LegacyBedBookingPayloadTest` asserts the legacy booking and bed-card component snapshots contain `bedId`, exclude `App\\Models\\Bed`, remain under the mobile payload budget, and still render the price breakdown/card price.
- `LegacyBookingShowPayloadTest` asserts the legacy booking-detail component snapshot contains `bookingId`, excludes `App\\Models\\Booking`, remains under the mobile payload budget, and still renders booking detail labels.
- `BookingReviewPayloadTest` asserts the booking-review snapshot contains `sleepingPlaceId`, excludes `quote`, `availabilityWarning`, and `unavailableDates`, stays below the mobile payload budget, and still renders the quote after normal form updates.
- `BookingDateSelectorTest` asserts the date selector snapshot contains `sleepingPlaceId`, excludes `quote`, `unavailableDates`, and `nearestRanges`, and still passes quote/unavailable data to Blade as view data.
- `AvailabilityCalendarFlowTest` asserts the availability-checker snapshot contains only small request state and IDs, excludes result/unavailable/range details, and still passes availability result data to Blade as view data.
- `BookingCheckInFeatureTest` and `BookingCheckOutFeatureTest` assert the problem/issue sheet snapshots do not expose `photoPaths`.
- `LegacyHostBedFormPayloadTest` asserts the host bed form snapshot contains `roomId`/`bedId`, excludes `App\\Models\\Room` and `App\\Models\\Bed`, stays below the mobile payload budget, renders the room through view data, rejects rooms owned by another host, and rejects a bed that does not belong to the routed room.
- `WaitlistFeatureTest` asserts the offer page snapshot contains `waitlistOfferId`, excludes `App\\Models\\WaitlistOffer` and `App\\Models\\WaitlistItem`, stays below the mobile payload budget, and still renders/decreases offers through the existing waitlist flow.
- `ProfileShowPayloadTest` asserts the public profile snapshot contains `userId`, excludes `App\\Models\\User` and private email values, stays below the mobile payload budget, and still passes the selected user as view data.
- `HostTodayCheckOutCardPayloadTest` asserts the host checkout card snapshot contains `checkOutId`, excludes `App\\Models\\BookingCheckOut` and private host notes, stays below the mobile payload budget, and still renders guest, room, and sleeping-place details.
- `LegacyWaitlistManagerPayloadTest` asserts the legacy waitlist manager snapshot contains `bedId`, excludes `App\\Models\\Bed`, stays below the mobile payload budget, and still creates a waiting entry.
- `BookingExtensionFlowTest` asserts the extension page snapshot contains only scalar booking/request state, excludes the calculated `preview` array, stays below the mobile payload budget, and still passes extension preview data to Blade as view data.

## Task 8: Verification Loop

After each task:

```bash
php artisan test --compact <focused test files>
./vendor/bin/pint --dirty
npm run build
git diff --stat
```

For the final pass:

```bash
php artisan test
./vendor/bin/pint
npm run build
```

Record any pre-existing failures separately and do not present the work as complete until the focused slice is green.
