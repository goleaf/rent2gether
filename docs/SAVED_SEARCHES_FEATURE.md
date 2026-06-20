# Saved Searches

## Purpose

Saved searches are a guest-owned watchlist for search filters. A guest can save a sleeping-place search once, then let the app check for new matches, price drops, and places that became available again.

The feature is mobile-first and in-app first. It does not require cron to work, and it does not introduce admin, staff, Filament, Volt, Inertia, or SPA surfaces.

## User Flow

1. Guest sets search filters on `/search`.
2. `SaveSearchButton` opens a bottom sheet with a compact summary and notification settings.
3. `SavedSearchService` stores canonical saved-search fields and legacy aliases for existing screens.
4. The guest can open `/saved-searches`, see cards, pause/resume/archive, or run a check now.
5. Opening a saved search runs the same service check, updates result rows, and creates translated database notifications when changes are found.

## Database Structure

`saved_searches` stores the saved filter set:

- owner and label: `user_id`, `title`, `name`, `description`, `status`
- location and dates: `city_id`, `district`, `location_text`, radius, check-in/check-out, nights, calendar days, guests
- budget: nightly and total budget, currency, legacy `price_min` and `price_max`
- filters: property/room/place types, gender policy, required/preferred amenities, excluded rules and conditions
- trust and comfort flags: verified hosts, instant booking, reviews, no deposit, max deposit, ratings, room size, bunk/sofa/mattress exclusions, Wi-Fi, kitchen, locker, workspace, washing machine, late check-in, smoking/pets/mixed-room avoidance
- notification settings: enabled signals, frequency, quiet hours, last/next check, counts, and results hash

`saved_search_results` stores per-place history:

- `saved_search_id`, `sleeping_place_id`, `property_id`, `room_id`
- first/last seen timestamps and status
- match score
- initial and current price/deposit snapshots
- price-change amount/percent
- availability transitions
- new/notified flags

The unique `saved_search_id + sleeping_place_id` index prevents duplicate result rows for repeat checks.

## Services

- `SavedSearchService`: create, update, pause, resume, archive, delete, run now, and check due searches for a user.
- `SavedSearchMatcherService`: builds an indexed Eloquent query from saved filters and limits each run.
- `SavedSearchSnapshotService`: stores and refreshes price/availability snapshots using `PricingService` and `AvailabilityService`.
- `SavedSearchResultService`: creates first-seen results and updates current result state without overwriting the original snapshot.
- `SavedSearchNotificationService`: creates translated in-app notifications for new matches, price drops, available-again, better-match, and expired-date signals.
- `SavedSearchFrequencyService`: evaluates frequency, quiet hours, and next check timestamps.

## Livewire Components

- `SaveSearchButton`: search-page action and bottom sheet.
- `SavedSearchesPage`: main saved-search list and summary cards.
- `SavedSearchCard`: compact mobile card with quick actions.
- `SavedSearchPage`: parameters, actions, settings, and result sections.
- `SavedSearchResultsList`: compact result cards with favorite, compare, open, and book actions.
- `CreateSavedSearchSheet`, `EditSavedSearchSheet`, `SavedSearchNotificationSettings`, `SavedSearchRunButton`: small action components.

Public properties store IDs, booleans, short strings, and compact arrays only.

## Notification Logic

No scheduler is required for core behavior:

- `SavedSearchesPage` checks a small number of due searches for the current user.
- `SavedSearchPage` runs the opened search immediately.
- Manual `run now` uses the same service path.

The optional command is available for future scheduling:

```bash
php artisan saved-searches:check
```

Quiet hours do not block in-app records. They mark the notification data as non-urgent so future push/email channels can stay quiet.

## Mobile UX

- Cards, not tables.
- Bottom sheets for create/edit/settings.
- Thumbnail only; no full galleries.
- No map on first load.
- Results are sectioned into new matches, price drops, available again, and all results.
- Every action has loading states through Livewire/Flux.

## Performance Rules

- Query selected columns only.
- Eager-load compact city/property/room/sleeping-place/media/translation graphs.
- Limit matches per run to 50.
- Do not load full galleries, full reviews, maps, or full city/country lists.
- Use `SleepingPlace::availableBetween()` for date filtering.
- Use `PricingService` for current totals and deposits.
- Keep result rows unique and update current fields in place.

## Translation Keys

Visible saved-search copy lives in:

- `lang/en/saved_searches.php`
- `lang/ru/saved_searches.php`

Notification copy lives in:

- `lang/en/notifications.php`
- `lang/ru/notifications.php`

Do not add hard-coded visible strings to Blade or Livewire components.

## Tests

Covered by `tests/Feature/SavedSearchesFeatureTest.php`:

- service create/update/pause/resume/archive/delete
- matching by city, budget, amenities, locker, instant booking, and verified host
- duplicate result prevention
- price drop detection
- unavailable and available-again detection
- notification creation
- frequency behavior
- Livewire pages and save button
- policy denial for another user
- English and Russian route rendering
