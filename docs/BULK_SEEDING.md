# Bulk Seeding

## Purpose

`Database\Seeders\BulkMarketplaceSeeder` creates a large local dataset for development, QA, and agent verification.

It is separate from the small hand-written marketplace demo data. The demo data gives readable scenarios; the bulk seeder proves that the whole model graph can hold at least 1000 rows per application model without factory recursion, missing foreign keys, or missing physical demo media files.

## Current Plan

1. Run all migrations before seeding.
2. Seed the small reusable foundations first:
   - `GeoSeeder`
   - `AmenityRuleSeeder`
   - `MarketplaceDemoSeeder`
3. Run `BulkMarketplaceSeeder` after the geo/catalog/demo foundation.
4. Build parent rows before child rows:
   - geo and catalog
   - users, profiles, privacy, preferences, verification
   - properties, rooms, sleeping places, detail tables, calendars, translations
   - bookings and booking lifecycle records
   - waitlist, favorites, saved searches, hints
   - host calendar, cleaning, bulk actions, current stays, listing workflow
   - media and notifications
5. Verify with `tests/Feature/DemoSeederTest.php`.

## Count Contract

Every Eloquent model in `app/Models/*.php` must have at least 1000 rows after `DatabaseSeeder` runs, unless a future ADR explicitly excludes that model.

For one-to-one tables, "1000 rows" means one row for each of the first 1000 parent owners, for example:

- `user_privacy_settings.user_id`
- `property_location_details.property_id`
- `room_layout_details.room_id`
- `sleeping_place_calendar_settings.sleeping_place_id`
- `booking_check_ins.booking_id`

For translation tables, the seeder may create more than 1000 rows because each owner can have both `en` and `ru` rows.

For unique pair tables, the seeder reuses existing pairs and creates missing pairs only:

- `sleeping_place_calendar_days`: `sleeping_place_id + date`
- `saved_search_results`: `saved_search_id + sleeping_place_id`
- `waitlist_items`: `user_id + sleeping_place_id`

## Why This Seeder Reuses Parents

Many factories define belongs-to defaults like `User::factory()`, `Property::factory()`, `Room::factory()`, `SleepingPlace::factory()`, or `Booking::factory()`.

Do not seed this project by looping over every model with `Model::factory()->count(1000)->create()`. That creates recursive parent graphs, inflates unrelated row counts, and can break unique constraints.

Bulk seed blocks must explicitly set foreign keys to already-created parent IDs.

## Commands

Use this when you want the full local dataset:

```bash
php artisan migrate --no-interaction
php artisan db:seed --class=Database\\Seeders\\DatabaseSeeder --no-interaction
```

Full GeoNames import is intentionally manual because it can create millions of city rows:

```bash
php artisan db:seed --class=Database\\Seeders\\GeoNamesFullSeeder --no-interaction
```

Use this for focused verification:

```bash
php artisan test --compact tests/Feature/DemoSeederTest.php
./vendor/bin/pint --dirty --format agent
```

## Rules For Future Models

When adding a new model:

1. Add its migration, model, factory, and indexes.
2. Add it to the correct block in `BulkMarketplaceSeeder`.
3. Override recursive factory foreign keys with existing parent IDs.
4. Use `seedMissingOwnedRows()` for unique owner tables.
5. Use a specialized loop for unique pair tables.
6. Run `DemoSeederTest`.
7. Update this document only when the seed contract changes.

Do not create new root-level seeder folders or a parallel bulk-data path.
