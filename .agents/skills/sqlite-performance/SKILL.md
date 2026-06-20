---
name: sqlite-performance
description: Use when creating migrations, indexes, queries, search, calendar, booking availability, pagination, seeders, or performance-sensitive SQLite code.
---

SQLite is the project database.

Rules:
- Every foreign key column gets an index unless covered by a composite index.
- Every search/filter combination used in UI must have a planned index.
- Every new data-backed feature must include a migration if data is needed, model relationships, factories, lookup seeders when lookup data is introduced, and indexes for its queries.
- Use composite indexes for:
  country_id + city_id
  city_id + status
  country_id + city_id + status
  city_id + district_normalized + status
  city_id + street_normalized + status
  location points city_id + category + name_normalized
  property_id + status
  room_id + status
  sleeping_place_id + date
  sleeping_place_id + status + date
  sleeping_place_id + start_date + end_date
  booking status + date
  translations translatable_id + locale
  messages thread_id + created_at
  notifications user_id + read_at + created_at
- Use cursor pagination for large lists.
- Use cursor pagination or load-more behavior for public search.
- Avoid SELECT * in heavy queries.
- Select only columns needed for mobile cards.
- Use compact DTO arrays for cards.
- Advanced location filters must have planned indexes before release: country/city/status, normalized district/street text, proximity category, area tags, and distance/sort fields.
- Proximity filters should use stored/precomputed values where practical; avoid per-row expensive distance calculations in first-render search queries.
- Avoid N+1 by eager loading small relations with selected columns.
- Never put huge arrays into Livewire properties.
- Cache lookup tables: amenities, rules, countries, common cities.
- For city search, query by normalized name prefix with indexes.
- Use query scopes for active, visible, available, translated.
- Add tests for availability overlap logic.
- Add tests for date range edge cases.
- Add tests for turnover edge cases: same-day turnover allowed, insufficient cleaning time, required inspection, and morning checkout/evening check-in.
- For double booking protection, recheck availability and create per-day holds inside the same transaction; rely on unique `sleeping_place_id + date` availability rows plus overlap queries on booking ranges.
- Add tests for extension overlap, next-booking edge cases, and held dates from another guest.
- Extension checks must use selected columns and indexed `sleeping_place_id + date` availability rows plus booking-range indexes; recheck inside the transaction before updating the original booking and creating added holds.
- Add tests for relocation availability on the new sleeping place, old-place hold preservation before relocation date, and conflicting holds by another guest.
- Relocation checks must query only booking IDs, sleeping-place IDs, dates, statuses, and price fields needed for the remaining stay; plan indexes for `booking_relocations.booking_id + status`, `new_sleeping_place_id + relocation_date`, and availability rows by `sleeping_place_id + date`.
- Use EXPLAIN QUERY PLAN notes in docs for critical queries.
- Document WAL mode setup for local/dev and production if applicable.
- Keep seeders small by default; large geo imports should be command-driven.

SQLite официально поддерживает PRAGMA journal_mode=WAL, а документация SQLite по query planner прямо говорит, что понимание планировщика помогает создавать лучшие индексы. (SQLite)
