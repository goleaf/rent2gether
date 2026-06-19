# Database Schema

## Current implementation

The SQLite schema audited on 2026-06-18 currently contains these product tables:

- Identity: `users`
- Inventory: `properties`, `rooms`, `beds`, `bed_availabilities`
- Booking: `bookings`, `booking_extensions`, `checkin_records`, `checkout_records`
- Communication: `conversations`, `messages`, `notifications`
- Trust and retention: `reviews`, `complaints`, `favorites`, `saved_searches`, `waitlist_entries`
- Host settlement: `payouts`

Laravel infrastructure tables provide cache, sessions, queues, failed jobs, password resets, and migrations.

The current `Bed` model and `beds` table represent the product's sleeping-place concept. Do not create a parallel `SleepingPlace` implementation without an explicit rename/migration plan.

## Target domain

Future schema work should converge on these boundaries:

- Profiles: `user_profiles`, `host_profiles`, `guest_preferences`, `user_settings`, `locale_settings`
- Geography: `countries`, `regions`, `cities`
- Inventory: properties, rooms, sleeping places, media, amenities, and rules
- Availability and price: availability days, price rules, and discount rules
- Booking ledger: booking guests, price lines, status history, payments, deposits, and refunds
- Communication and trust: message threads, messages, favorites, saved searches, waitlist items, reviews, and complaints

## Translation tables

Public user-generated content must use separate tables such as:

- `property_translations`
- `room_translations`
- `sleeping_place_translations`
- `amenity_translations`
- `rule_translations`

Each translation table needs a locale index and a unique constraint covering its parent key and locale. Adding a language must add rows, not columns.

## Index contracts

Plan indexes around actual UI access patterns, including:

- property owner and status
- city and visible status
- room parent and status
- sleeping place parent and status
- sleeping place and availability date
- sleeping place and booking date range
- booking participant, status, and date
- translation parent and locale
- conversation and message creation time
- notification owner, read state, and creation time

Every foreign key requires an index unless the leading columns of a composite index already cover it.

## SQLite operations

- Keep foreign keys enabled.
- Use WAL mode for local and deployed single-host SQLite after verifying host support and backup behavior.
- Keep write transactions short and configure a busy timeout before concurrent traffic is introduced.
- Run critical query plans against representative data, not only tiny seed sets.
- Keep default seeders small; run geographic imports through dedicated Artisan commands.
