# Database Schema

## Current implementation

The SQLite schema audited on 2026-06-19 contains the canonical sleeping-place marketplace foundation alongside a temporary legacy `Bed` bridge:

- Identity: `users`
- Profiles: `user_profiles`, `guest_preferences`, `host_profiles`, `user_settings`
- Geography: `countries`, `regions`, `cities`
- Inventory: `properties`, `property_translations`, `rooms`, `room_translations`, `sleeping_places`, `sleeping_place_translations`
- Amenities and rules: `amenities`, `amenity_translations`, `rules`, `rule_translations`, and owner pivot tables
- Media: `media_items`
- Availability and price: `availability_days`, `price_rules`, `discount_rules`
- Booking ledger: `bookings`, `booking_extensions`, `booking_guests`, `booking_price_lines`, `booking_status_histories`
- Stay lifecycle: `checkin_records`, `checkout_records`
- Money records: `payment_records`, `deposit_records`, `refund_requests`
- Social and trust: `favorites`, `saved_searches`, `waitlist_items`, `message_threads`, `messages`, `reviews`, `complaints`, `notifications`
- Legacy bridge tables still present during transition: `beds`, `bed_availabilities`, `conversations`, `waitlist_entries`, `payouts`

Laravel infrastructure tables provide cache, sessions, queues, failed jobs, password resets, and migrations.

`SleepingPlace` is the canonical rental unit. The legacy `Bed` model remains only to keep existing feature code and tests working during the transition; do not add new product behavior to `Bed` unless it is explicitly part of a bridge or migration step.

## Account settings

`user_settings` is the durable place for account-level preferences:

- `locale` stores the user's preferred locale.
- `currency` stores the preferred display currency.
- `account_role` stores account capability: `guest`, `host`, or `both`.
- `active_mode` stores the current shell mode: `guest` or `host`.
- `notification_preferences_json` and `privacy_preferences_json` store small preference maps.

Profile details belong in `user_profiles`; host-specific public details belong in `host_profiles`; guest preference filters belong in `guest_preferences`. Do not duplicate those fields into Blade state or add role-specific admin tables.

Avatar metadata currently stores the selected medium variant path on `users.avatar`, `user_profiles.avatar_path`, and `host_profiles.avatar_path`; generated files live on the public disk under `avatars/{user_id}`.

`guest_preferences` stores comfort and search preferences used by compatibility scoring:

- budget range, currency, preferred city, room type, and sleeping place type
- required amenities such as Wi-Fi, kitchen, washing machine, locker, workspace, quiet hours, and accessibility
- avoidance flags for smoking, pets, mixed rooms, upper bunks, and crowded rooms
- schedule, allergy, baggage, and transport-distance constraints

`properties.distance_to_transport_meters` is nullable and supports max walking distance checks. Do not call live geo APIs while evaluating compatibility.

`host_profiles` stores the public host card and host onboarding defaults:

- public display name, avatar, about text, languages, response time/rate, rating, reviews, and verification status
- host style flags such as response style, lives in property, lives nearby, check-in help, and emergency contact availability
- hosting experience plus default check-in time, check-out time, cancellation policy, deposit setting, and house rules

Payout settings are intentionally only a readiness-check placeholder for now. Do not add finance staff workflows or admin payout screens until explicitly requested.

## Property drafts

The host property wizard saves each completed step to `properties` as a draft. `properties.rental_unit_type` stores whether the host intends to rent a whole property, a room, one sleeping place, or several sleeping places. `properties.type` remains the physical property type such as apartment, house, dormitory, hostel, guest house, cottage, or room in a property.

`properties.region_name` preserves the host-entered/displayed region label while `region_id` remains available for imported open-data regions. Country and city selections must come from imported SQLite geo data. The wizard must not call live geo APIs.

Translated listing content is saved in `property_translations` for `en` and `ru` from the first version of the listing. Amenities and rules are stored through `property_amenity` and `property_rule`; the legacy JSON columns are only compatibility data and should not be used for new wizard behavior.

Room setup stores host-managed room details in `rooms` and localized public text in `room_translations`. Room status supports `draft`, `active`, `hidden`, and `unavailable` for host workflows. Draft sleeping places generated from `rooms.beds_count` are written to `sleeping_places` with `status = draft`; pricing and availability are still configured in later sleeping-place flows.

Sleeping place setup stores the exact rental unit in `sleeping_places`. Host-facing identifiers live in `place_number` and `display_name`; public localized title, description, and special conditions live in `sleeping_place_translations`. Exact-place comfort, position, privacy, noise, price, deposit, min/max nights, booking approval settings, and `extensions_allowed` are stored on the sleeping place. Photos use `media_items` with sleeping-place morph metadata; the physical files remain in storage.

`media_items` stores image metadata for property, room, sleeping-place, avatar, complaint, check-in, checkout, and review photos. Each item keeps the legacy morph target plus `owner_type` and `owner_id` for compact owner lookups, `collection` for gallery grouping, original filename, mime, size, dimensions, localized captions, sort order, and primary/cover flags. Uploads generate `thumb_path`, `mobile_path`, and `full_path` variants on the public disk. Cards should use the mobile variant from the primary item; full galleries should load only after the user opens a detail/gallery surface.

## Guest search

Guest search reads from `sleeping_places` as the canonical rental unit. The compact card query joins active `rooms`, active `properties`, and optional `host_profiles`, then eager-loads only the relationships needed to render a mobile card:

- current/fallback `sleeping_place_translations`
- one primary `media_items` row on the sleeping place, room, or property
- compact room and property data
- compact amenity translations for visible chips
- scoped `availability_days` price overrides only when dates are selected

The URL query state maps to indexed columns and small lookup relations:

- city and district filters use `properties.city_id`, legacy `properties.city`, `properties.district`, and local `cities.name_normalized` lookup fallback
- property, room, sleeping-place type, gender policy, guests, price, currency, deposit, instant booking, host approval, host rating, host verification, and reviews use selected scalar columns
- amenity and rule filters use owner pivot tables for sleeping-place, room, and property scope
- date availability uses the `SleepingPlace::availableBetween()` half-open range `[check_in, check_out)`

`2026_06_19_210000_add_guest_search_indexes.php` adds the first search-specific SQLite indexes for status/type/price filters, host trust sorting, property distance/parking/elevator filters, room gender/max-guest filters, and sleeping-place booking/deposit/comfort filters. Add new composite indexes before introducing new public search filters.

## Public sleeping-place detail

`/places/{sleepingPlace}` reads the same canonical `sleeping_places` hierarchy and keeps first-render data compact:

- active sleeping place, room, and property only
- current/fallback sleeping-place, room, and property translations
- compact amenity and rule labels from translated lookup tables
- primary/mobile media rows from `media_items`
- host user plus `host_profiles` for the public host card
- count-only overlapping `bookings` query for privacy-safe nearby guests

Reviews are read by a lazy component from `reviews.sleeping_place_id` with `status = published`. Similar places are read by a lazy component using active sleeping places in the same city.

`favorites.sleeping_place_id` is now the canonical favorite target for sleeping-place listings. The legacy `favorites.bed_id` column must be nullable during the transition so guests can favorite a sleeping place without a legacy `Bed` row. Keep a unique index on `user_id + sleeping_place_id`.

`availability_days` stores per-day overrides for each sleeping place. The canonical statuses are `available`, `blocked_by_host`, `booked`, `pending_payment`, `pending_approval`, `cleaning`, `repair`, `unavailable`, `check_in_only`, and `check_out_only`; legacy `blocked` and `maintenance` values may still appear while old data is bridged. `booking_id` is nullable and is used only for booking-generated holds so `releaseForBooking()` can remove a payment/approval/booking hold without opening dates that a host closed manually. Date overlap checks use the half-open range `[check_in, check_out)`, so checkout on the same day as the next check-in is allowed when the date's check-in/check-out flags allow it.

Guest booking-date selection reads `sleeping_places` pricing and limit columns plus `availability_days` rows in `[check_in, check_out)`. Date-specific price rows use `availability_days.price_override`; selected date ranges also honor `min_nights_override` and `max_nights_override` when present. Automatic quote output is transient DTO data from `App\Services\PricingService`; it should be persisted later as `booking_price_lines` only when a booking/request is created.

Mobile booking submission is handled by `App\Actions\Bookings\BookingSubmit` and writes canonical sleeping-place bookings without requiring a legacy `bed_id`. The `bookings` table keeps `arrival_time`, `rules_accepted_at`, and `availability_hold_expires_at` for the review flow. Instant bookings start as `awaiting_payment` unless the payment mode confirms later payment immediately; host-request bookings start as `awaiting_host_approval`. Both pending states create `availability_days` holds with `pending_payment` or `pending_approval`, persist the calculated quote into `booking_price_lines`, and write the initial row in `booking_status_histories`.

Host request management is handled by `App\Actions\Bookings\AcceptBookingRequest`, `DeclineBookingRequest`, and `SetBookingRequestExpiry`. Accepting a request rechecks availability while ignoring the booking's own `pending_approval` hold, moves the booking to `awaiting_payment`, sets `payment_deadline_at`, converts the hold to `pending_payment`, writes status history, and creates a guest notification. Declining a request requires a predefined translated reason, moves the booking to `declined_by_host`, releases only the booking-generated hold rows, writes status history, and notifies the guest.

Payment provider integration is intentionally a placeholder for now. `App\Actions\Payments\ConfirmDemoPayment` is the local/testing-only manual driver; production must not expose the demo confirmation action. Every successful or failed attempt writes a `payment_records` row with provider metadata. A successful demo payment moves the booking to `confirmed`, sets `payment_status = paid`, records `payment_paid_at`, writes status history, blocks the booked sleeping-place dates through `availability_days.status = booked`, exposes permitted guest access instructions, and creates a host notification. A failed attempt keeps the booking in `awaiting_payment` with `payment_status = failed` so the guest can retry later.

Cancellation and refund calculation are logical records only until a real provider is integrated. `bookings.cancellation_policy` maps to `CancellationPolicy`, while `bookings.refund_amount`, `refund_status`, `cancel_reason`, `cancellation_reason`, `cancelled_by`, and `cancelled_at` capture the user-visible cancellation state. `RefundCalculator` calculates the estimate from booking totals and policy metadata. `CancellationService` releases only booking-generated availability holds, writes `booking_status_histories`, creates a `refund_requests` row when a refund is due, and writes a `payment_records` row with `provider = manual_refund_placeholder` for the refund ledger. No finance/admin resolution UI exists yet.

Guest trip management reads the same `bookings` ledger through `App\Livewire\Trips`. Upcoming, current, past, and cancelled screens classify rows by `bookings.status` and `guest_user_id`. Booking detail loads one compact graph: booking, host profile/contact, property/room/sleeping-place translations, catalog rules/amenities, price lines, and deposit records. Exact address and check-in instructions are not shown until `show_exact_address_before_booking` is true or the booking is paid/confirmed enough for `show_exact_address_after_payment`.

Completed-stay reviews are stored in `reviews`. Guest reviews about places use `type = guest_to_place`; host reviews about guests use `type = host_to_guest`. The table keeps legacy `reviewer_id`, `reviewee_id`, `positive_comment`, `negative_comment`, `advice`, `bed_comfort_rating`, and `communication_rating` columns while adding canonical marketplace fields such as `guest_user_id`, `host_user_id`, `sleeping_place_comfort_rating`, `host_communication_rating`, `rule_following_rating`, `respect_rating`, `liked_text`, `improvement_text`, `advice_text`, `comment`, `photos_json`, `recommend`, `recommend_guest`, and `visible_at`.

`reviews` has a unique constraint on `booking_id + type`, so each booking can receive one guest review and one host review. New rows start as `pending` unless the review window already expired. `ReviewService` publishes both rows when both sides submit or the booking's `review_deadline_at` has passed. Public listing and profile queries must use `Review::visible()` rather than reading every review row.

Complaints and problem reports are stored in `complaints`. The table keeps legacy aliases (`reference`, `reporter_id`, `urgency`, `photos`, `respondent_reply`, `resolution_notes`, `deposit_withheld`) while adding canonical fields such as `complaint_number`, `reporter_user_id`, `priority`, `refund_requested`, `deposit_hold_requested`, `media`, `other_side_response`, `resolution_text`, and `deposit_hold_amount`. Guest complaint types and host complaint types are separated in `ComplaintType`, and only booking participants may create/respond to a complaint.

Complaint timelines are stored in `complaint_status_histories` with `complaint_id`, optional `actor_user_id`, `status`, `note_key`, optional `note`, and compact metadata. `ComplaintService` writes timeline rows for creation, waiting for the other side, and the other-side response. No staff/admin resolution tables or UI exist yet; unresolved complaints remain in user-visible status states.

Booking extension requests are stored in `booking_extensions`. The mobile guest component keeps only the booking ID, requested checkout date, short message, and compact quote preview in Livewire state. Extensions are allowed only for `confirmed`, `paid`, `ready_for_checkin`, `checked_in`, `in_progress`, or `active_stay` bookings where `sleeping_places.extensions_allowed = true`, the requested checkout is after the current checkout, the new total nights do not exceed `sleeping_places.max_nights`, and `AvailabilityService` confirms the half-open extra range `[current_checkout, requested_new_checkout)` has no booking or calendar blocks. Status values are `draft`, `awaiting_host_approval`, `awaiting_payment`, `approved`, `declined`, and `cancelled`; legacy `pending/rejected/paid` rows are normalized by migration.

`ExtensionService` calculates extra nights through `PricingService` but charges only additional nights, discount, and service fee; one-time deposit and cleaning fees are not repeated. Host approval rechecks availability before moving the extension to payment. Demo payment for an extension is local/testing-only, writes a `payment_records` row with `provider = extension_demo_manual`, updates the original booking checkout/nights/totals, blocks availability rows as `booked`, and notifies guest and host. Do not add finance/admin workflows around extension payments.

Guest-host messaging is stored in `message_threads` and `messages`. `message_threads.type` uses the canonical values `pre_booking`, `booking`, `current_stay`, and `complaint_related`; each thread belongs to one guest, one host, and optionally a booking, property, and sleeping place. `messages` keeps both legacy `sender_id` and canonical `sender_user_id`, plus `recipient_user_id`, optional booking/property/sleeping-place context, compact attachment metadata, `important`, `system_message`, `locale`, and `read_at`.

`MessageService` is the only place that creates guest-host thread messages. It validates participation, keeps the legacy `conversations` bridge in sync for transition safety, updates `last_message_at`, creates a `message_received` notification, and blocks host messages that contain exact address fragments until the property's address visibility rules and booking/payment state allow sharing. Message attachments are stored on the public disk as validated image/PDF metadata only; do not store full galleries or unrelated listing payload in the chat state.

User notifications are stored in `notifications`. The marketplace uses `user_id` for compact owner lookups while keeping Laravel's `notifiable_type` and `notifiable_id` columns for compatibility. Notification rows store a stable `type`, translation keys in `title_key` and `body_key`, replacement values in `data.params`, an optional localized `action_url`, `status`, and `read_at`. `App\Services\NotificationService` creates booking request, payment, check-in instruction, message, saved-place, and waitlist notifications; rows must not store already-rendered visible copy.

Check-in and check-out records are canonical stay-lifecycle tables. `checkin_records` stores planned and actual arrival, property/access confirmation, room and exact sleeping-place confirmation, rules seen, guest/host confirmation timestamps, and optional problem details/photos in `problem_media`. `checkout_records` stores planned and actual checkout, key return, belongings/locker/cleanliness confirmations, host inspection, damage description/photos, and `deposit_action`.

Status transitions are action-driven and must be logged in `booking_status_histories`: guest check-in moves `confirmed` or `ready_for_checkin` bookings to `checked_in`; host confirmation moves them to `in_progress`; guest checkout moves active stays to `checked_out`; host checkout confirmation moves them to `completed`. Deposit return or hold decisions synchronize `checkout_records` with `deposit_records` without introducing finance/admin workflows.

Amenities and rules are seed-driven for now. `AmenityRuleSeeder` reads the code catalog from `App\Services\Catalog\AmenityRuleCatalog`, writes canonical slugs/categories to `amenities` and `rules`, and writes English/Russian labels to `amenity_translations` and `rule_translations`. Host UI attaches catalog entries through `property_amenity`, `room_amenity`, `sleeping_place_amenity`, `property_rule`, `room_rule`, and `sleeping_place_rule`. Do not store new amenity or rule labels in Blade or legacy JSON columns.

## Domain hierarchy

Preserve this central hierarchy:

`User -> HostProfile -> Property -> Room -> SleepingPlace -> AvailabilityDay -> Booking`

Booking, pricing, availability, refund, and compatibility calculations should live in services or actions with unit tests, not in Blade or model accessors that hide queries.

## Translation tables

Public user-generated content must use separate tables such as:

- `property_translations`
- `room_translations`
- `sleeping_place_translations`
- `amenity_translations`
- `rule_translations`

Each translation table needs a locale index and a unique constraint covering its parent key and locale. Adding a language must add rows, not columns.

## Geo tables

Countries use ISO-compatible identifiers: `iso2`, `iso3`, localized names, default timezone, currency, phone code, `status`, and `name_normalized`. The legacy `code` and `name` columns are kept as compatibility aliases while the marketplace transitions to the open-data import contract.

Cities use GeoNames identifiers and compact search fields: `geoname_id`, `country_id`, optional `region_id`, `name`, `ascii_name`, `alternate_names`, coordinates, population, timezone, feature class/code, `status`, and `name_normalized`. The legacy `source_id` column is kept in sync with `geoname_id`.

Geo imports run through:

- `php artisan geo:import-countries --source=storage/app/geo/countries.csv`
- `php artisan geo:import-geonames-cities --source=storage/app/geo/cities1000.txt`
- `php artisan geo:rebuild-search-index`

Do not load cities from external APIs during search. Public Nominatim is only for occasional geocoding and must never be used for bulk imports.

## Index contracts

Plan indexes around actual UI access patterns, including:

- country ISO lookup and normalized name search
- city GeoNames lookup, active normalized prefix search, and country/status filters
- property owner and status
- city and visible status
- preferred guest city and currency
- distance to nearest transport when guest walking-distance filters are introduced
- room parent and status
- sleeping place parent and status
- sleeping place and availability date
- availability booking hold and status
- sleeping place and booking date range
- booking extension booking/status and requested checkout date
- booking participant, status, and date
- translation parent and locale
- message thread participant/status recency, type recency, message creation time, recipient read state, and message context foreign keys
- notification owner, read state, and creation time
- review booking/type uniqueness, visible listing reviews by sleeping place/status/visible date, and profile reviews by reviewee/type/status
- complaint participant/status, booking/status, sleeping-place/status, and timeline complaint/created-at lookups

Every foreign key requires an index unless the leading columns of a composite index already cover it.

The schema contract test verifies required tables, required marketplace columns, critical composite indexes, and leading indexes for all foreign keys using Laravel schema inspection APIs.

## SQLite operations

- Keep foreign keys enabled.
- Use WAL mode for local and deployed single-host SQLite after verifying host support and backup behavior.
- Keep write transactions short and configure a busy timeout before concurrent traffic is introduced.
- Run critical query plans against representative data, not only tiny seed sets.
- Keep default seeders small; run geographic imports through dedicated Artisan commands.
