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
- Pre-booking intake: `booking_guest_intakes`
- Stay lifecycle: `checkin_records`, `checkout_records`
- Money records: `payment_records`, `deposit_records`, `refund_requests`
- Social and trust: `favorites`, `saved_searches`, `waitlist_items`, `message_threads`, `messages`, `reviews`, `complaints`, `notifications`
- Compatibility: `guest_compatibility_profiles`, `guest_compatibility_visibility_settings`, `room_compatibility_profiles`, `sleeping_place_compatibility_profiles`, `compatibility_results`
- Legacy bridge tables still present during transition: `beds`, `bed_availabilities`, `conversations`, `waitlist_entries`, `payouts`

Laravel infrastructure tables provide cache, sessions, queues, failed jobs, password resets, and migrations.

`SleepingPlace` is the canonical rental unit. The legacy `Bed` model remains only to keep existing feature code and tests working during the transition; do not add new product behavior to `Bed` unless it is explicitly part of a bridge or migration step.

## Seed data contract

`DatabaseSeeder` is the default local dataset entry point. It runs the lightweight geo/catalog/demo foundations and then `BulkMarketplaceSeeder`.

After `DatabaseSeeder` runs, every concrete Eloquent model in `app/Models/*.php` must have at least 1000 rows unless a future ADR explicitly excludes it. One-to-one tables seed one row for each of the first 1000 parent rows; translation tables may seed more because each owner can have `en` and `ru` rows.

`BulkMarketplaceSeeder` must reuse existing parent IDs for users, properties, rooms, sleeping places, bookings, and lookup rows. Do not bulk-create each factory in isolation because many factories have belongs-to defaults that recursively create parent graphs and inflate unrelated counts.

`GeoNamesFullSeeder` is manual only. It can import millions of `cities` rows, so keep it out of `DatabaseSeeder` and run it explicitly only when the local SQLite catalog needs the full offline GeoNames dataset. A database that has run the full import may have far more than 1000 city rows; that is expected.

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

## Guest compatibility

The newer guest compatibility module is separate from legacy `guest_preferences`.

`guest_compatibility_profiles` stores practical co-living preferences: smoking, pets, quiet/night schedule, work/study needs, home presence, kitchen and washing needs, social style, cleanliness, shared/private room preference, room people limit, sleeping-place needs, locker/storage, bedding/towel, self check-in, and late or 24/7 entry.

`guest_compatibility_visibility_settings` stores whether the profile can be used for matching and whether safe hints may be shown to hosts or future roommates. Defaults should allow matching but keep detailed preference display private.

`room_compatibility_profiles` and `sleeping_place_compatibility_profiles` store compact synchronized matching data so search cards and detail sections do not inspect full room/property/sleeping-place graphs on first render.

`compatibility_results` caches date-aware score results by user, room, sleeping place, and selected range. It stores translated reason DTO payloads for positive, warning, and blocking reasons. Blocking reasons force `not_suitable`; warning reasons are practical comfort notes and should not block booking unless a booking action explicitly requires confirmation.

Public UI must show only score, fit status, translated safe reasons, and warning counts. It must not expose raw private profile answers, complaint details, messages, private notes, exact workplace/school, phone, email, documents, or full personal profiles.

## Guest intake before booking

`booking_guest_intakes` stores the short pre-booking questionnaire a guest fills before a booking request or instant booking. It belongs to `users`, optional `bookings`, `properties`, `rooms`, and `sleeping_places`.

The table stores trip purpose, safe purpose visibility, arrival/departure timing, early check-in and late checkout requests, baggage, pet and smoking answers, quiet/work/Wi-Fi/socket/late-entry/self-check-in needs, document request flags, special requests, host message, generated host message, rules acceptance, compatibility status, warnings, and blocking reasons.

The short pre-booking questionnaire exposes compact fields to the product flow: `needs_early_check_in`, `needs_late_check_out`, `luggage_amount`, `needs_desk`, and `message_to_host`. `BookingGuestIntakeService` mirrors them to the older internal columns used by existing warnings and host summaries so query and display code can migrate gradually without breaking previous booking behavior.

Medical or treatment purpose is sensitive. Host-facing summaries must show a safe label such as `private trip` by default unless the guest explicitly chooses exact purpose visibility. Document details and private notes should not be rendered in host-facing Blade; use `BookingGuestIntakePrivacyService` and `BookingGuestIntakeSummaryService`.

Draft lookup uses `user_id + status`; booking and host screens use `booking_id`, `property_id`, `room_id`, and `sleeping_place_id`. Need flags such as pets, smoking, quiet, workspace, fast Wi-Fi, registration, work documents, and compatibility status are indexed for host/request workflows.

`host_profiles` stores the public host card and host onboarding defaults:

- public display name, avatar, about text, languages, response time/rate, rating, reviews, and verification status
- host style flags such as response style, lives in property, lives nearby, check-in help, and emergency contact availability
- hosting start year with derived experience years, plus default check-in time, check-out time, cancellation policy, deposit setting, and house rules

Payout settings are intentionally only a readiness-check placeholder for now. Do not add finance staff workflows or admin payout screens until explicitly requested.

## Property drafts

The host property wizard saves each completed step to `properties` as a draft. `properties.rental_unit_type` stores whether the host intends to rent a whole property, a room, one sleeping place, or several sleeping places. `properties.type` remains the physical property type such as apartment, house, dormitory, hostel, guest house, cottage, or room in a property.

`properties.region_name` preserves the host-entered/displayed region label while `region_id` remains available for imported open-data regions. Country and city selections must come from imported SQLite geo data. The wizard must not call live geo APIs.

Translated listing content is saved in `property_translations` for `en` and `ru` from the first version of the listing. Amenities and rules are stored through `property_amenity` and `property_rule`; the legacy JSON columns are only compatibility data and should not be used for new wizard behavior.

Room setup stores host-managed room details in `rooms` and localized public text in `room_translations`. Room status supports `draft`, `active`, `hidden`, and `unavailable` for host workflows. Draft sleeping places generated from `rooms.beds_count` are written to `sleeping_places` with `status = draft`; pricing and availability are still configured in later sleeping-place flows.

Sleeping place setup stores the exact rental unit in `sleeping_places`. Host-facing identifiers live in `place_number` and `display_name`; public localized title, description, and special conditions live in `sleeping_place_translations`. Exact-place comfort, position, privacy, noise, price, deposit, min/max nights, booking approval settings, and `extensions_allowed` are stored on the sleeping place. Photos use `media_items` with sleeping-place morph metadata; the physical files remain in storage.

`media_items` stores image metadata for property, room, sleeping-place, avatar, complaint, check-in, checkout, and review photos. Each item keeps the legacy morph target plus `owner_type` and `owner_id` for compact owner lookups, `collection` for gallery grouping, original filename, mime, size, dimensions, localized captions, sort order, and primary/cover flags. Uploads generate `thumb_path`, `mobile_path`, and `full_path` variants on the public disk. Cards should use the mobile variant from the primary item; full galleries should load only after the user opens a detail/gallery surface.

The public disk must be web-readable through the configured `public/storage` link. Demo and bulk seeders use `App\Services\Media\DemoMediaFileService` so seeded `demo-media/*` and `bulk-demo/*` rows always have physical files for original, thumb, mobile, and full paths. Seeded media rows must not point to missing files.

## Extended property profile

Extended property data is split across one-to-one detail tables instead of adding every descriptive field to `properties`.

`properties` keeps owner/status, address privacy flags, searchable physical structure, resident counts, sleeping-place counts, booking-scope booleans, and compact indexed filters such as `property_type`, `district`, `has_elevator`, and free/occupied sleeping-place counts.

`property_location_details` stores transport, nearby places, district feel, center distance, and parking fields. It is keyed by a unique `property_id` and indexes center distance, transport minutes, district noise/safety, and parking booleans.

`property_condition_details` stores repair, cleanliness, smells, climate, heating/hot-water flags, noise, light, insects, mold, furniture/surface conditions, and host check timestamps. It is keyed by a unique `property_id` and indexes repair state, cleanliness, indoor noise, insects, mold, and last check date.

`property_access_details` stores entrance type, intercom/door/key/key-safe flags, self check-in, host or host-representative meeting requirements, 24/7 and night entry, visitor rules, courier rules, delivery, and private access notes. It is keyed by a unique `property_id` and indexes self check-in, 24/7 access, intercom, electronic lock, key safe, and delivery.

`property_translations` stores guest-facing property text such as title, short/full description, location, transport, neighborhood, parking, condition, access, self check-in, night entry, delivery, visitor rules, courier rules, and important notes.

Public listing detail must use `PropertyGuestSummaryService` and `PropertyPrivacyService` rather than rendering raw address/access columns. Before booking privacy rules allow it, guests must not see exact address, apartment number, door/intercom/gate codes, key safe location, private contacts, or internal notes. If another person handles keys, call them a host representative; do not introduce a property manager role.

## Extended room profile

Extended room data is split across one-to-one detail tables instead of turning `rooms` into a very wide table.

`rooms` keeps searchable/status fields such as `room_type`, `living_format`, `gender_policy`, private/shared/pass-through flags, occupancy counters, guest limits, booking-scope booleans, and `sort_order`.

`room_translations` stores localized guest-facing content: title, short/full description, room rules, pros/cons, who lives nearby, storage instructions, work/study instructions, quiet-hours text, food rules, conflict instructions, and special notes.

`room_layout_details` stores area, dimensions, ceiling height, windows, view, cardinal direction, balcony, and passage-space flags.

`room_comfort_details` stores heating, cooling, ventilation, window, smell, light, curtains, night light, noise, soundproofing, and quiet-hours fields.

`room_access_details` stores door, lock, key, privacy, wardrobe, personal lockers, luggage/shoe/coat space, desk, chairs, mirror, hooks, drying rack, and food-storage rules.

`room_condition_details` stores condition, repair, cleanliness, surface/furniture states, dust, smells, damp, mold, insects, damage, repair flags, check dates, and host condition notes.

Public listing detail must use `RoomGuestSummaryService` and `RoomPrivacyService`. Guests should see helpful room facts and warnings, but not private occupant data or withheld room numbers before the booking context allows it.

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

Advanced location filters extend this query state with `country_id`, `city_id`, `district`, `street`, `landmark`, proximity flags, and area-type flags. Country/city filters use imported geo IDs. District, street, and landmark filters should use normalized local columns such as `properties.district_normalized`, `properties.street_normalized`, or a future compact `location_points`/`property_location_features` table rather than external API calls. Proximity filters such as center, metro, bus stop, train station, airport, university, work, hospital, sea, park, shopping center, gym, coworking, and nightlife must use stored property coordinates, precomputed distance columns, or offline/imported points of interest. Area-type filters such as quiet, safe, residential, city center, suburb, industrial, tourist, student, worker, and long-stay should be stored as indexed property/location tags with translated labels.

`2026_06_19_210000_add_guest_search_indexes.php` adds the first search-specific SQLite indexes for status/type/price filters, host trust sorting, property distance/parking/elevator filters, room gender/max-guest filters, and sleeping-place booking/deposit/comfort filters. Add new composite indexes before introducing new public search filters.

## Saved searches

Saved searches are stored in `saved_searches` and remain guest-owned through `user_id`. The table keeps legacy aliases (`name`, `check_in`, `check_out`, `price_min`, `price_max`, `notify_*`, `is_active`) while adding canonical fields for the new module:

- `title`, `description`, `status`
- location context: `city_id`, `district`, `location_text`, `radius_meters`
- date and guest context: `check_in_date`, `check_out_date`, `nights_count`, `calendar_days_count`, `guests_count`, flexible date fields
- budget context: `budget_min`, `budget_max`, `total_budget_max`, `currency`
- filter JSON: property types, room types, sleeping-place types, required/preferred amenity IDs, excluded rule IDs, excluded conditions
- scalar filters: verified hosts/places, instant booking, reviews, free cancellation, no deposit, max deposit, ratings, max people in room, bunk/sofa/mattress exclusions, locker/workspace/Wi-Fi/kitchen/washing-machine requirements, smoking/pet/mixed-room avoidance
- notification state: signal toggles, frequency, quiet hours, last/next check timestamps, counters, and `last_results_hash`

`saved_search_results` stores which sleeping places were found for each saved search. It keeps `saved_search_id`, `sleeping_place_id`, `property_id`, `room_id`, first/last seen timestamps, `status`, `match_score`, initial and current price/deposit snapshots, price-change amount/percent, availability transition flags, and notification flags. The unique `saved_search_id + sleeping_place_id` index prevents duplicate rows across repeated checks.

Saved-search checks use `SavedSearchMatcherService` to build indexed Eloquent queries, `PricingService` for current totals, and `AvailabilityService`/`SleepingPlace::availableBetween()` for date availability. The app works without cron by checking due searches when the guest opens saved-search pages; `php artisan saved-searches:check` is only an optional future scheduler entry.

Important indexes:

- `saved_searches`: user/status, user/created, city/status, check-in/check-out, next check, last checked, frequency, and notification booleans
- `saved_search_results`: unique search/place, search/status, search/new, search/price-changed, search/available-again, sleeping place, property, room, and last matched

## Waitlist

Waitlist records are stored in `waitlist_items` and `waitlist_offers`. `waitlist_items` remains compatible with the earlier guest-decision-tool columns (`desired_check_in`, `desired_check_out`, `max_price`, `ready_to_book`, and `auto_request`) while adding the canonical queue fields:

- ownership and target: `user_id`, `property_id`, `room_id`, `sleeping_place_id`
- desired stay: `desired_check_in_date`, `desired_check_out_date`, `nights_count`, `calendar_days_count`, `guests_count`, flexible date fields, and optional min/max nights
- budget limits: `max_price_per_night`, `max_total_price`, `max_deposit`, `currency`
- readiness: `ready_to_book_immediately`, `ready_to_pay_immediately`, `auto_send_request`, and `auto_create_booking_draft`
- notifications: available, price drop, similar available, offer expiring, quiet hours, and last notification/check timestamps
- queue state: `status`, `position`, `priority_score`, offer/skip counters, expiry, added/cancelled/completed timestamps, and optional guest message

`waitlist_offers` stores each concrete time-limited offer when a place becomes available. It keeps `waitlist_item_id`, guest/place foreign keys, optional `booking_id`, status, offered/expires/accepted/declined/expired/skipped timestamps, current price/deposit snapshot, hold timestamps, notification timestamp, and response/system notes.

Queue eligibility uses `WaitlistQueueService`, which delegates current availability to `AvailabilityService` and current prices to `PricingService`. Accepting an offer uses `BookingSubmit`, creates a normal booking request/payment state, and never charges automatically. Declining or expiring an offer asks the queue for the next eligible guest.

The app does not require cron for waitlist behavior. Booking cancellation, booking expiry, host-opened dates, guest waitlist visits, and host sleeping-place views can trigger checks. `php artisan waitlist:check` exists only as an optional future scheduler entry.

Important indexes:

- `waitlist_items`: user/status, sleeping-place/status, sleeping-place/dates, property/status, room/status, status/expiry, status/last-checked, status/priority, desired dates, and notify-available
- `waitlist_offers`: waitlist-item/status, user/status, sleeping-place/status, status/offer-expiry, and booking

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

`favorite_collections` stores guest-owned folders for saved sleeping places. Rows belong to one `user_id`, may optionally carry trip context such as `city_id`, `check_in_date`, `check_out_date`, `nights_count`, `guests_count`, budget range, currency, icon/color, pinned/archive flags, and default collection type. Default collections are created per user by `FavoriteCollectionService`; guests may rename, archive, delete, and reorder them.

Expanded `favorites` rows keep both legacy compatibility fields and the canonical collection/snapshot fields. New favorites should write `favorite_collection_id`, `property_id`, `room_id`, `sleeping_place_id`, source, personal note, priority, decision status, selected dates, nights, guests, currency, price-per-night snapshot, total-price snapshot, deposit snapshot, current price values, price change amount/percent, availability flags, nearest available dates, reminder fields, and notification preferences. Existing `collection`, `note`, `price_at_save`, `check_in`, and `check_out` remain compatibility aliases while old UI/tests are migrated. The unique `user_id + sleeping_place_id` contract means one guest has one saved record per sleeping place; moving a favorite changes its collection instead of duplicating the same place into multiple folders.

Favorites query indexes are planned around the mobile UI: `user_id + created_at`, `user_id + favorite_collection_id`, `favorite_collection_id + priority`, `favorite_collection_id + decision_status`, `user_id + remind_at`, `user_id + price_changed`, `user_id + is_currently_available`, leading `property_id`, leading `room_id`, and the existing sleeping-place lookup. Favorite cards should eager-load only translated sleeping-place title, compact room/property location, one primary media row, and count/avg review aggregates.

`availability_days` stores a separate per-day calendar for each sleeping place, keyed by `sleeping_place_id + date`. The canonical statuses are `available`, `occupied`, `pending_payment`, `pending_host_confirmation`, `booked`, `guest_checked_in`, `guest_checked_out`, `closed_by_host`, `closed_by_service`, `cleaning`, `repair`, `broken`, `complaint_blocked`, `hidden`, and `request_only`; legacy/current bridge values such as `blocked_by_host`, `pending_approval`, `unavailable`, `blocked`, and `maintenance` may still appear while old data is bridged. `booking_id` is nullable and is used only for booking-generated holds so `releaseForBooking()` can remove a payment/approval/booking hold without opening dates that a host closed manually. Date overlap checks use the half-open range `[check_in, check_out)`, so checkout on the same day as the next check-in is allowed when the date's check-in/check-out flags allow it. Keep a unique index on `sleeping_place_id + date` and plan `sleeping_place_id + status + date` for status-filtered host calendar views.

Double booking protection rejects overlapping booking/hold ranges for the same sleeping place. A requested range overlaps an existing range when `requested_check_in < existing_check_out` and `requested_check_out > existing_check_in`. Example: an existing July 10 to July 15 stay blocks July 9 to July 11, July 10 to July 12, July 14 to July 16, and July 11 to July 15. Ranges ending on or before July 10 are allowed; ranges starting after July 15 are allowed; ranges starting on July 15 are allowed only when same-day turnover is allowed by check-in/check-out flags and cleaning-gap rules. Booking/request actions must recheck availability in the same transaction that writes booking rows and availability holds, then create one `availability_days` hold row per date in `[check_in, check_out)` so the unique `sleeping_place_id + date` index helps reject duplicate day holds.

Turnover settings should be available at the most specific applicable level, normally sleeping place first, then room/property/host defaults. Required fields are `minimum_turnover_minutes`, `cleaning_required_between_guests`, `cleaning_duration_minutes`, `inspection_required_after_checkout`, `same_day_check_in_allowed`, `morning_checkout_evening_checkin_allowed`, `earliest_next_check_in_time`, and `latest_previous_check_out_time`. Same-day turnover validation compares previous checkout datetime plus required turnover/cleaning/inspection time against the requested next check-in datetime. If the required gap does not fit, the boundary date is unavailable or request-only with translated reason keys.

Guest booking-date selection reads `sleeping_places` pricing and limit columns plus `availability_days` rows in `[check_in, check_out)`. Date-specific price rows use `availability_days.price_override`; selected date ranges also honor `min_nights_override` and `max_nights_override` when present. Automatic quote output is transient DTO data from `App\Services\Pricing\PricingService`; it should be persisted later as `booking_price_lines` only when a booking/request is created. The quote output must include stay days, nights, calendar days, base/subtotal price, discount, deposit, cleaning fee, service fee, total due, free cancellation date, cancellation penalty start date, host payout date, check-in reminder date, and check-out reminder date. Overnight sleeping-place billing uses `nights_count = check_out_date - check_in_date`; `stay_days_count` equals `nights_count` in the current non-hourly mode, while `calendar_days_count` is the inclusive presence display. Example: July 10 to July 13 is 3 nights / 3 payable stay days and 4 calendar presence days. Date selection state must support `check_in_date`, `check_in_time`, `check_out_date`, `check_out_time`, `nights_count`, `stay_days_count`, `calendar_days_count`, `early_check_in_requested`, `late_check_out_requested`, `check_in_time_flexible`, `check_out_time_flexible`, `host_time_approval_required`, `check_in_time_comment`, and `check_out_time_comment`. Views must render those values from the DTO rather than recalculating them inline.

Date validation is a server-side service/action concern before payable quote display, booking-request creation, or booking confirmation. It must reject checkout before check-in, same-day checkout unless a future daily/hourly rental mode allows it, occupied or held sleeping-place dates, host-closed property, room, or sleeping-place dates, unavailable or repair room dates, required cleaning gaps, min/max-stay violations, disabled check-in/check-out weekdays, missing required guest verification, room age-rule conflicts, room gender-policy conflicts, and guest-count over-limit. Validation failures should be returned as translated reason keys.

Guest calendar behavior is derived from `availability_days`, property/room/sleeping-place status, booking holds, min/max stay rules, weekday check-in/check-out rules, and the selected guest profile constraints. After check-in changes, the service response must include available checkout dates, hidden/disabled unavailable checkout dates, earliest and latest checkout dates, selected-range warning keys, nearest available ranges, and lightweight suggestion IDs/card DTOs for similar sleeping places, neighboring rooms, and other places from the same host. Date changes must trigger a fresh `PricingService` quote; calendar responses must stay compact and must not load maps, full galleries, or large result lists.

Automatic price calculation is transient until booking/request creation. When dates, guest count, timing options, promo code, or other booking conditions change, `PricingService` must recalculate check-in date, check-out date, stay days count, base price per stay day, weekday price, weekend price, holiday price, weekly price, monthly price, long-stay discount, weekly discount, monthly discount, early-booking discount, last-minute discount, new-guest discount, personal discount, early check-in fee, late checkout fee, extra guest fee, cleaning fee, deposit, service fee, taxes or city fees when configured, promo code, total discount amount, amount before discounts, amount after discounts, total due, host payout amount, refundable amount, and non-refundable amount. Quote DTOs should be compact and line-item based with translation keys; persisted `booking_price_lines` are written only when a booking or booking request is created.

Pricing line logic uses the half-open date range `[check_in, check_out)`. Stays shorter than 7 days use per-stay-day pricing; stays from 7 days may apply a weekly discount; stays from 30 days may apply a monthly discount. Nightly price precedence is host date-specific override, then holiday price, then weekend price, then weekday/base price. Promo codes, checkout-date changes, guest-count changes, and timing option changes trigger a full recalculation. A second guest on a two-person sleeping place can add an extra guest fee. Early check-in and late checkout either add configured fees or return host-approval request keys, depending on host rules.

Guest-facing quote DTOs and persisted `booking_price_lines` must be able to represent nightly lines before totals. Example display structure: July 10 EUR 20, July 11 EUR 20, July 12 EUR 25, stay days 3, accommodation amount EUR 65, discount EUR 5, cleaning fee EUR 10, deposit EUR 50, service fee EUR 6, total due now EUR 126, and refundable deposit after checkout EUR 50. Store line types and translation keys so labels remain localized and the deposit refund explanation is separate from the amount due.

Mobile booking submission is handled by `App\Actions\Bookings\BookingSubmit` and writes canonical sleeping-place bookings without requiring a legacy `bed_id`. The `bookings` table keeps `arrival_time`, `planned_arrival_time`, `travel_purpose`, `guest_message`, `host_response`, `decline_reason`, `request_expires_at`, luggage/timing/document request flags, `rules_accepted_at`, and `availability_hold_expires_at` for the review flow. Instant bookings start as `awaiting_payment` unless the payment mode confirms later payment immediately; host-request bookings start as `awaiting_host_approval`. Both pending states create `availability_days` holds with `pending_payment` or `pending_approval`, persist the calculated quote into `booking_price_lines`, and write the initial row in `booking_status_histories`. Non-instant request fields must cover guest, host, sleeping place, check-in/check-out dates, stay days count, guests count, travel purpose, planned arrival time, guest message, has luggage, needs luggage space, needs early check-in, needs late check-out, needs residence registration, needs reporting documents, request status, host response, decline reason, and request expiration time.

Advanced booking classification should separate primary flow, payment mode, deposit mode, and optional modifiers instead of collapsing everything into one status. Primary flows are `instant_booking`, `host_confirmation_booking`, `stay_request`, `preliminary_inquiry`, `long_term_request`, and `urgent_today_booking`. Payment/deposit modes are `awaiting_payment`, `with_deposit`, `without_deposit`, `partial_payment`, and `full_payment`. Modifiers/scenarios are `extension`, `relocation`, `group_booking`, and `two_guest_sleeping_place`. Two-guest sleeping-place bookings require `sleeping_places.max_guests >= 2`, matching room rules, and recalculated extra guest fees. Group bookings still reserve availability per sleeping place. Extension and relocation bookings must link back to the original booking and keep status history/audit trail.

Canonical booking fields include `booking_number`, `status`, `guest_user_id`, `host_user_id`, `property_id`, `room_id`, `sleeping_place_id`, `check_in_date`, `check_in_time`, `check_out_date`, `check_out_time`, `stay_days_count`, `calendar_days_count`, `guests_count`, `price_per_stay_day`, `period_price_amount`, `discount_amount`, `deposit_amount`, `cleaning_fee_amount`, `service_fee_amount`, `total_amount`, `currency`, `payment_status`, `payment_method`, `paid_at`, `payment_deadline_at`, `guest_message`, `host_response`, `requires_document_verification`, `requires_phone_verification`, `requires_identity_verification`, `decline_reason`, `cancellation_reason`, `cancelled_by`, `cancellation_policy`, `refund_amount`, `refund_status`, `check_in_instructions`, `guest_checked_in_at`, `host_confirmed_check_in_at`, `guest_checked_out_at`, `host_confirmed_check_out_at`, `has_dispute`, `has_complaint`, `guest_review_submitted_at`, and `host_review_submitted_at`. Prefer timestamps for confirmations and review submissions while exposing derived booleans in mobile DTOs. `booking_number` should be unique and indexed; status/payment/date lookups should keep composite indexes for host and guest lists.

Booking lifecycle statuses are `draft`, `created`, `awaiting_host_approval`, `awaiting_guest_response`, `awaiting_payment`, `awaiting_identity_verification`, `awaiting_document_verification`, `confirmed`, `paid`, `ready_for_check_in`, `guest_checked_in`, `in_progress`, `check_out_soon`, `guest_checked_out`, `awaiting_room_inspection`, `awaiting_deposit_return`, `completed`, `awaiting_review`, `closed`, `declined_by_host`, `cancelled_by_guest`, `cancelled_by_host`, `cancelled_by_service`, `unpaid`, `guest_no_show`, `host_unresponsive`, `dispute_opened`, `frozen_pending_dispute_resolution`, and `requires_support_intervention`. Labels belong in `statuses.booking.*`. `requires_support_intervention` is only a state and must not create a support/staff/admin panel. Keep `payment_status` separate; payment records remain the source of truth for money movement. Every lifecycle transition should append a `booking_status_histories` row with previous status, new status, actor, reason key, and timestamp where practical.

Host request management is handled by `App\Actions\Bookings\AcceptBookingRequest`, `DeclineBookingRequest`, and `SetBookingRequestExpiry`. Accepting a request rechecks availability while ignoring the booking's own `pending_approval` hold, moves the booking to `awaiting_payment`, sets `payment_deadline_at`, converts the hold to `pending_payment`, writes status history, and creates a guest notification. Declining a request requires a predefined translated reason, moves the booking to `declined_by_host`, releases only the booking-generated hold rows, writes status history, and notifies the guest. Host request detail must load a compact privacy-safe guest profile summary: display name, avatar, age or age range, city, languages, rating, previous stays count, reviews count, identity/phone/email verification flags, complaint count summary, dates, stay days count, total amount, travel purpose, guest message, rule compatibility, and translated warning keys. Request assessment warning keys may include night arrival, very early checkout, missing identity verification, no reviews, prior cancellations, last-minute booking, stay longer than max duration, cleaning-gap conflict, complaints, smoking conflict, pet conflict, and too many guests for the sleeping place.

Payment provider integration is intentionally a placeholder for now. `App\Actions\Payments\ConfirmDemoPayment` is the local/testing-only manual driver; production must not expose the demo confirmation action. Every successful or failed attempt writes a `payment_records` row with provider metadata. A successful demo payment moves the booking to `confirmed`, sets `payment_status = paid`, records `payment_paid_at`, writes status history, blocks the booked sleeping-place dates through `availability_days.status = booked`, exposes permitted guest access instructions, and creates a host notification. A failed attempt keeps the booking in `awaiting_payment` with `payment_status = failed` so the guest can retry later.

Cancellation and refund calculation are logical records only until a real provider is integrated. `bookings.cancellation_policy` maps to `CancellationPolicy`, while `bookings.refund_amount`, `refund_status`, `cancel_reason`, `cancellation_reason`, `cancelled_by`, and `cancelled_at` capture the user-visible cancellation state. `RefundCalculator` calculates the estimate from booking totals and policy metadata. `CancellationService` releases only booking-generated availability holds, writes `booking_status_histories`, creates a `refund_requests` row when a refund is due, and writes a `payment_records` row with `provider = manual_refund_placeholder` for the refund ledger. No finance/admin resolution UI exists yet.

Guest trip management reads the same `bookings` ledger through `App\Livewire\Trips`. Upcoming, current, past, and cancelled screens classify rows by `bookings.status` and `guest_user_id`. Booking detail loads one compact graph: booking, host profile/contact, property/room/sleeping-place translations, catalog rules/amenities, price lines, and deposit records. Exact address and check-in instructions are not shown until `show_exact_address_before_booking` is true or the booking is paid/confirmed enough for `show_exact_address_after_payment`.

Completed-stay reviews are stored in `reviews`. Guest reviews about places use `type = guest_to_place`; host reviews about guests use `type = host_to_guest`. The table keeps legacy `reviewer_id`, `reviewee_id`, `positive_comment`, `negative_comment`, `advice`, `bed_comfort_rating`, and `communication_rating` columns while adding canonical marketplace fields such as `guest_user_id`, `host_user_id`, `sleeping_place_comfort_rating`, `host_communication_rating`, `rule_following_rating`, `respect_rating`, `liked_text`, `improvement_text`, `advice_text`, `comment`, `photos_json`, `recommend`, `recommend_guest`, and `visible_at`.

`reviews` has a unique constraint on `booking_id + type`, so each booking can receive one guest review and one host review. New rows start as `pending` unless the review window already expired. `ReviewService` publishes both rows when both sides submit or the booking's `review_deadline_at` has passed. Public listing and profile queries must use `Review::visible()` rather than reading every review row.

Complaints and problem reports are stored in `complaints`. The table keeps legacy aliases (`reference`, `reporter_id`, `urgency`, `photos`, `respondent_reply`, `resolution_notes`, `deposit_withheld`) while adding canonical fields such as `complaint_number`, `reporter_user_id`, `priority`, `refund_requested`, `deposit_hold_requested`, `media`, `other_side_response`, `resolution_text`, and `deposit_hold_amount`. Guest complaint types and host complaint types are separated in `ComplaintType`, and only booking participants may create/respond to a complaint.

Complaint timelines are stored in `complaint_status_histories` with `complaint_id`, optional `actor_user_id`, `status`, `note_key`, optional `note`, and compact metadata. `ComplaintService` writes timeline rows for creation, waiting for the other side, and the other-side response. No staff/admin resolution tables or UI exist yet; unresolved complaints remain in user-visible status states.

Booking extension requests are stored in `booking_extensions`. Canonical fields include `booking_id`, `current_check_out_date`, `new_check_out_date`, `additional_stay_days_count`, `additional_price_per_stay_day`, `extension_discount_amount`, `extension_amount_due`, `currency`, `status`, `host_approval_required`, `host_response`, `decline_reason`, and `extension_paid_at`. The mobile guest component keeps only the booking ID, requested checkout date, short message, active extension ID, and compact quote preview in Livewire state. Extensions are allowed only for `confirmed`, `paid`, `ready_for_check_in`, `guest_checked_in`, or `in_progress` bookings where `sleeping_places.extensions_allowed = true`, the requested checkout is after the current checkout, the new total nights do not exceed `sleeping_places.max_nights`, and `AvailabilityService` confirms the half-open extra range `[current_check_out_date, new_check_out_date)` has no booking, hold, or calendar block from another guest. Status values are `draft`, `awaiting_host_approval`, `awaiting_payment`, `approved`, `declined`, `paid`, `applied`, `cancelled`, and `expired`; legacy `pending/rejected` rows are normalized by migration.

`ExtensionService` calculates extra nights through `PricingService` but charges only additional nights, discount, and service fee; one-time deposit and cleaning fees are not repeated. It shows the guest `extension_amount_due` before confirmation, sends host approval when required, and rechecks availability inside the approval/payment transaction before mutating the original booking. Demo payment for an extension is local/testing-only, writes a `payment_records` row with `provider = extension_demo_manual`, updates the original booking checkout date, stay days, totals, status history, and price lines, blocks added availability rows as `booked`, and notifies guest and host. Do not add finance/admin workflows around extension payments.

Booking relocation requests should be stored in `booking_relocations` when implemented. Canonical fields include `booking_id`, `current_sleeping_place_id`, `new_sleeping_place_id`, `reason`, `relocation_date`, `price_difference_amount`, `price_difference_payer`, `currency`, `guest_consent_required`, `host_consent_required`, `status`, `guest_comment`, `host_comment`, and `support_comment`. Reasons are `noisy_neighbors`, `uncomfortable_bed`, `resident_conflict`, `broken_item`, `host_offered_other_place`, `wants_more_private_room`, `wants_cheaper_place`, and `wants_more_comfort`. Statuses are `draft`, `requested_by_guest`, `offered_by_host`, `awaiting_guest_consent`, `awaiting_host_consent`, `awaiting_payment`, `approved`, `declined`, `applied`, `cancelled`, and `expired`. `support_comment` is reserved data only and must not create support/staff/admin workflows.

`RelocationService` checks availability for the new sleeping place across `[relocation_date, booking.check_out_date)`, rejects another guest's booking or hold, keeps the current sleeping place blocked before `relocation_date`, and changes current/new availability rows only after the relocation is approved or applied. Relocation pricing recalculates the remaining stay, price difference, extra amount due, refund/credit, and deposit implications through a service/action. Applying relocation updates the original booking's remaining sleeping-place context, appends booking status history, persists adjustment price lines where needed, updates availability rows, and notifies guest and host.

Guest-host messaging is stored in `message_threads` and `messages`. `message_threads.type` uses the canonical values `pre_booking`, `booking`, `current_stay`, and `complaint_related`; each thread belongs to one guest, one host, and optionally a booking, property, and sleeping place. `messages` keeps both legacy `sender_id` and canonical `sender_user_id`, plus `recipient_user_id`, optional booking/property/sleeping-place context, compact attachment metadata, `important`, `system_message`, `locale`, and `read_at`.

`MessageService` is the only place that creates guest-host thread messages. It validates participation, keeps the legacy `conversations` bridge in sync for transition safety, updates `last_message_at`, creates a `message_received` notification, and blocks host messages that contain exact address fragments until the property's address visibility rules and booking/payment state allow sharing. Message attachments are stored on the public disk as validated image/PDF metadata only; do not store full galleries or unrelated listing payload in the chat state.

User notifications are stored in `notifications`. The marketplace uses `user_id` for compact owner lookups while keeping Laravel's `notifiable_type` and `notifiable_id` columns for compatibility. Notification rows store a stable `type`, translation keys in `title_key` and `body_key`, replacement values in `data.params`, an optional localized `action_url`, `status`, and `read_at`. `App\Services\NotificationService` creates booking request, payment, check-in instruction, message, saved-place, and waitlist notifications; rows must not store already-rendered visible copy.

Check-in and check-out records are canonical stay-lifecycle tables. `checkin_records` stores planned and actual arrival, property/access confirmation, room and exact sleeping-place confirmation, rules seen, guest/host confirmation timestamps, and optional problem details/photos in `problem_media`. `checkout_records` stores planned and actual checkout, key return, belongings/locker/cleanliness confirmations, host inspection, damage description/photos, and `deposit_action`.

Status transitions are action-driven and must be logged in `booking_status_histories`: guest check-in moves `confirmed` or `ready_for_checkin` bookings to `checked_in`; host confirmation moves them to `in_progress`; guest checkout moves active stays to `checked_out`; host checkout confirmation moves them to `completed`. Deposit return or hold decisions synchronize `checkout_records` with `deposit_records` without introducing finance/admin workflows.

Amenities and rules are seed-driven for now. `AmenityRuleSeeder` reads the code catalog from `App\Services\Catalog\AmenityRuleCatalog`, writes canonical slugs/categories to `amenities` and `rules`, and writes English/Russian labels to `amenity_translations` and `rule_translations`. Host UI attaches catalog entries through `property_amenity`, `room_amenity`, `sleeping_place_amenity`, `property_rule`, `room_rule`, and `sleeping_place_rule`. Do not store new amenity or rule labels in Blade or legacy JSON columns.

Extended sleeping-place details keep `sleeping_places` compact and move rich bed-level data into one-to-one tables: `sleeping_place_physical_details`, `sleeping_place_comfort_details`, `sleeping_place_storage_details`, `sleeping_place_position_details`, and `sleeping_place_condition_details`. Public content stays in `sleeping_place_translations` with locale uniqueness. Guest-facing code must use `SleepingPlaceGuestSummaryService` so internal host names, private condition notes, and private guest data stay hidden.

Current occupant information is privacy-filtered through `co_living_profiles`, `co_living_visibility_settings`, and `room_occupant_snapshots`. Bookings remain the source of truth, while snapshots store only allowed co-living fields for fast room-atmosphere summaries. Occupant overlap uses `[check_in_date, check_out_date)`, so a checkout on the new guest's check-in date is not counted as a roommate. Public pre-booking views show aggregate data only; confirmed guests and hosts still receive privacy-filtered DTOs rather than raw user records.

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

Countries use ISO-compatible identifiers: `iso2`, `iso3`, GeoNames ID, canonical fallback `name`, default timezone, currency, phone code, `status`, and `name_normalized`. User-facing country names are stored in `country_translations` by `locale`; legacy name columns are source compatibility only and must not become the pattern for new languages.

Cities use GeoNames identifiers and compact search fields: `geoname_id`, `country_id`, optional `region_id`, canonical fallback `name`, `ascii_name`, `alternate_names`, coordinates, population, timezone, feature class/code, `status`, and `name_normalized`. User-facing city names are stored in `city_translations` by `locale`; `source_id` is kept in sync with `geoname_id`.

Geo imports run through:

- `php artisan geo:import-countries --source=storage/app/geo/countries.csv`
- `php artisan geo:import-geonames-cities --source=storage/app/geo/cities1000.txt`
- `php artisan geo:seed-geonames`
- `php artisan geo:seed-geonames --download-only`
- `php artisan geo:rebuild-search-index`

Full GeoNames imports run through `GeoNamesFullSeeder` explicitly. Do not wire `GEONAMES_SEED_ENABLED` back into `DatabaseSeeder`; the flag may still support dedicated geo commands, but default project seeding must stay predictable.

Geo autocomplete uses `app()->getLocale()` as the search/display locale. It queries `country_translations.locale` and `city_translations.locale` first, then falls back to canonical names when translated rows are unavailable.

Do not load cities from external APIs during search. Public Nominatim is only for occasional geocoding and must never be used for bulk imports.

## Index contracts

Plan indexes around actual UI access patterns, including:

- country ISO lookup and normalized name search
- country translation lookup by `locale + name_normalized`
- city GeoNames lookup, active normalized prefix search, and country/status filters
- city translation lookup by `locale + name_normalized`
- property owner and status
- city and visible status
- preferred guest city and currency
- distance to nearest transport when guest walking-distance filters are introduced
- room parent and status
- sleeping place parent and status
- sleeping place and availability date
- sleeping place detail filters such as room/sort order, sleeping-place type/status, bunk/status, locker, curtain, socket, near-door/window, condition, repair, and last check date
- availability booking hold and status
- sleeping place and booking date range
- room occupant snapshots by room/status and room/date range
- co-living profiles by user and safe lifestyle filters
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
- Keep default seeders bounded and predictable; run full geographic imports through dedicated Artisan commands.
