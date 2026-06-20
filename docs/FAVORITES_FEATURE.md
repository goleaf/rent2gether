# Favorites And Collections

## Purpose

Favorites are a lightweight decision helper for guests. A guest can save a sleeping place, keep the selected dates and price snapshot, add notes and priority, place it into a collection, compare shortlisted places, and return to booking without reopening the same listing repeatedly.

The feature is guest-owned only. It does not introduce admin, staff, moderation, or Filament surfaces.

## User Flow

1. Guest taps the heart on search, listing detail, or comparison.
2. `FavoriteService` creates one favorite per `user_id + sleeping_place_id`.
3. `FavoriteSnapshotService` stores selected dates, nights, guest count, current nightly price, total price, deposit, discount, availability state, and legacy compatibility fields.
4. The guest can open `/favorites`, browse collections, open a collection, filter/sort cards, compare selected favorites, edit notes, move the favorite, or schedule a reminder.
5. On visits or service refreshes, price and availability changes update the saved record and can create translated database notifications.

## Data Model

`favorite_collections` is the folder table:

- owner: `user_id`
- visible metadata: `title`, `description`, `icon`, `color`, `type`
- optional trip context: `city_id`, dates, nights, guests, budget, currency
- ordering/state: `sort_order`, `is_default`, `is_pinned`, `is_archived`

`favorites` is the saved sleeping-place table:

- owner and target: `user_id`, `favorite_collection_id`, `property_id`, `room_id`, `sleeping_place_id`
- compatibility aliases: `bed_id`, `collection`, `note`, `price_at_save`, `check_in`, `check_out`
- decision fields: source, personal note, short label, label color, priority, decision status
- snapshot fields: selected dates, nights, guests, currency, saved/current price and deposit
- change fields: price changed, amount, percent, checked timestamps
- availability fields: current state, became unavailable, became available again, partial availability, nearest available dates
- reminder and notification preferences

The unique `user_id + sleeping_place_id` index is intentional. A guest has one favorite record per sleeping place; moving changes the collection. The current `copyToCollection()` method is idempotent under that schema and does not duplicate a saved sleeping place.

## Services

- `FavoriteService`: add, remove, toggle, move, copy-compatible move, note, priority, and decision status updates.
- `FavoriteCollectionService`: create, rename, archive, restore, delete, reorder, and default collection creation.
- `FavoriteSnapshotService`: create and refresh price snapshots with `PricingService`.
- `FavoriteAvailabilityService`: check current availability with `AvailabilityService`, mark state transitions, and suggest nearest ranges.
- `FavoriteReminderService`: schedule, cancel, fetch due reminders, and mark sent.
- `FavoriteChangeNotificationService`: creates translated notification rows for reminders, price changes, and availability changes.

Pricing and availability logic stays in existing shared services. Blade and Livewire components only render DTO/card arrays and dispatch service actions.

## Livewire Components

- `FavoritesPage`: main module content inside the existing shell route.
- `FavoriteCollectionsList`: horizontal mobile collection cards.
- `FavoriteCollectionPage`: collection detail with filter, sort, compare selected, cards, and load more.
- `FavoriteCard`: compact mobile card with photo, price, deposit, rating, availability, price change, note, badges, open/book/compare/remove.
- `FavoriteToggle`: heart action for search, listing, and comparison surfaces.
- `FavoriteTray`: compact count link for shell/tray use.
- `CreateCollectionSheet`, `MoveFavoriteSheet`, `EditFavoriteNoteSheet`, `FavoriteReminderSheet`: bottom-sheet actions.
- `FavoriteFilters`, `FavoriteSort`: compact controls used by collection pages.

## Mobile UX

- First layout is one column and works at 320px width.
- Collection cards scroll horizontally on small screens.
- Favorite lists use cards, not tables.
- Sheets are bottom-aligned on mobile.
- Large secondary UI stays out of the DOM until opened.
- Cards use one thumbnail, translated title, compact location, and small scalar fields.
- Comparison is capped at four sleeping places.

## Performance Rules

- Store only IDs and small strings in public Livewire state.
- Query selected columns only.
- Eager-load compact room, property, translations, one card media row, and review count/average.
- Do not load full galleries, full reviews, maps, or city lists from favorites.
- Use `FavoriteCardPresenter` to build compact card arrays before Blade renders.
- Keep indexes aligned with collection, reminder, price-change, and availability filters.

## Translation Keys

Visible copy lives in:

- `lang/en/favorites.php`
- `lang/ru/favorites.php`
- `lang/en/notifications.php`
- `lang/ru/notifications.php`

Do not add hard-coded visible strings to Blade or Livewire classes.

## Tests

Covered by `tests/Feature/FavoritesCollectionsFeatureTest.php`:

- default collections
- custom collection creation
- favorite add and duplicate prevention
- collection ownership policies
- collection deletion without deleting sleeping places
- user deletion cascade
- note, priority, status, reminders
- price drop detection
- unavailable detection
- collection page rendering
- toggle component behavior

Existing decision/detail tests continue to cover localized favorites route rendering and detail-page favorite toggling.
