# Project Rules

## Product boundary

rent2gether is a mobile-first marketplace for renting an individual sleeping place inside a room and property. A user may act as a guest, a host, or both.

The product hierarchy is:

`User -> HostProfile -> Property -> Room -> SleepingPlace -> Availability -> Booking`

The core marketplace loop is:

- Guest chooses city, dates, and sleeping place.
- System calculates availability, stay days, nights, calendar days, price, discount, deposit, cleaning fee, service fee, total due, cancellation deadlines, host payout timing, check-in/check-out reminders, rules, and compatibility.
- Host controls property, rooms, sleeping places, calendar, price, rules, and booking requests.

Everything must stay mobile-first, multilingual, fast, friendly, and Livewire-native. This loop is the product frame for architecture, prompts, routes, UI, tests, and documentation.

Do not add administrator, moderator, support, finance, cleaner, helper, or property-manager workflows.

## Required stack

The audited local stack on 2026-06-18 is:

- PHP 8.5.7, with project support declared as PHP 8.3+
- Laravel 13.16.1
- Livewire 4.3.1 class components
- Flux Pro 2.14.1
- Tailwind CSS 4.3.1
- SQLite
- PHPUnit 12

Keep Blade server-rendered. Do not introduce Volt, Filament, Inertia, React, Vue, or another SPA layer.

## Demo data

The default seed path is intentionally small and fast. It creates:

- 2 countries and 5 cities from local demo geo data
- 3 guest users and 3 host users
- 3 properties, 6 rooms, and 18 sleeping places
- translated amenities and rules
- placeholder media metadata
- 90 days of sleeping-place availability
- price rules, discount rules, 10 bookings, messages, reviews, favorites, saved searches, waitlist items, notifications, and complaints

Use this command to rebuild a local demo database:

```bash
php artisan app:demo-reset
```

Use this command when the schema is already current and only demo rows should be refreshed:

```bash
php artisan app:demo-reset --seed-only
```

Large GeoNames imports are not part of the default seeder. Run `geo:import-countries`, `geo:import-geonames-cities`, and `geo:rebuild-search-index` explicitly when a larger offline geo dataset is needed.

## Integration Coverage

`tests/Feature/FullIntegrationPassTest.php` protects the main user journeys:

- Guest registration, Russian locale preference, profile setup, city/date search, nights and price calculation, listing detail, favorite, booking request, host acceptance, local/testing demo payment, trip detail, check-in, problem report, extension, checkout, and guest review.
- Host registration, guest-to-host mode switch, host onboarding, property/room/sleeping-place creation, media upload, amenities/rules, pricing, calendar range update, incoming request handling, host messaging, check-in/checkout confirmation, host review, and income summary.
- Guardrails for no admin routes, no Livewire Volt, no Filament, no Inertia, and no map package in the first-load stack.

## Architecture

- Keep Livewire public state compact; store IDs and filters rather than full models or large arrays.
- Put booking, pricing, availability, cancellation, and compatibility logic in testable actions or services.
- Keep database access out of Blade views.
- Use Eloquent relationships, scopes, selected columns, eager loading, and pagination.
- Keep translated public content in indexed translation tables instead of adding one column per language.
- Keep media metadata in the database and physical files in configured storage.
- Use policies and server-side validation for every state-changing action.
- Generate mobile media variants server-side and render primary mobile images on cards; do not load full galleries in search results.

## Account flows

- Authentication, profile setup, account settings, security settings, and mode switching are Livewire class components.
- `user_settings.account_role` stores account capability: `guest`, `host`, or `both`.
- `user_settings.active_mode` stores the current shell mode: `guest` or `host`.
- Switching to host mode may create a `host_profiles` row, but it must not create any admin or staff surface.
- Host onboarding and host profile editing are Livewire class components. Completing host onboarding enables host mode and preserves guest capability as `both` when a guest becomes a host.
- Host profile defaults are listing starting points only: check-in/out time, cancellation policy, deposit setting, and house rules. Payout settings remain a placeholder until payments are ready.
- Avatar uploads are image-only, size-limited, and stored as mobile-friendly variants on the configured public disk.
- Seeded demo media must create real files on the public disk for every stored media path; a `media_items` row without a readable seeded file is a broken demo state.
- Legacy guest search preferences are stored in `guest_preferences`; date-aware co-living compatibility is stored in `guest_compatibility_profiles` and scored by testable services under `App\Services\Compatibility`, not in Blade.
- Compatibility output must include a 0-100 score, a `great/good/attention/uncomfortable/not_suitable` fit status, positive reasons, warning reasons, and blocking reasons. Reason text must use translation keys.

## Mobile UI

- Design at 320px first and verify through 430px before adding wider layouts.
- Use one-column content, large tap targets, compact forms, and sticky primary actions.
- Prefer Flux components, progressive disclosure, drawers, and accordions.
- Do not load maps, large galleries, or long lookup lists on the initial page.
- Do not render full city/country lists in guest preference forms; use compact text input or autocomplete.
- Add `wire:loading` or Livewire 4 `data-loading` styling to every network action.
- Use translation keys for all visible copy, accessible labels, validation, notifications, and emails.

## Livewire Mobile Performance Contract

- Use `wire:model.blur` for normal text fields.
- Use `wire:model.change` for selects, checkboxes, and radios.
- Use `wire:model.live.debounce.500ms` or `wire:model.live.debounce.750ms` only for search and autocomplete.
- Never use live model binding for long textareas.
- Never keep huge arrays in public Livewire properties; store IDs, filters, short form fields, and compact state only.
- Never load full countries or cities into a select.
- Never render hidden huge filters; use bottom sheets, drawers, and lazy components.
- Use cursor pagination or load-more behavior for search.
- Use compact DTO arrays for listing cards and dashboard cards.
- Use selected columns and eager-loaded compact relations for every list/card query.
- Use cached lookup values for amenities, rules, countries, and common cities.
- Use `data-loading`, `wire:loading`, and skeletons for every network action.
- Use optimistic UI only where the action is reversible or the rollback path is safe and obvious.

## Application shell

The mobile shell uses a compact top bar with brand text, locale switcher, guest/host mode switcher, and a persistent navigation loading indicator. Mobile bottom navigation is mode-aware:

- Guest mode: Search, Trips, Favorites, Messages, Profile.
- Host mode: Listings, Calendar, Requests, Messages, Profile.

Top-level shell pages must stay lightweight: translated title, translated helper text, a visible primary action, and a friendly empty state. Do not load maps, charts, large images, or large lists in the shell page itself. Feature/detail pages can expand later behind explicit navigation.

## Guest search

- `/search` renders `App\Livewire\Search\SleepingPlaceSearch`; the canonical result unit is `SleepingPlace`, not the legacy `Bed` bridge.
- Search state that should be shareable must be synchronized to URL query parameters: city, district, dates, guests, price, currency, place types, amenity/rule flags, host trust flags, and sort.
- City autocomplete must query imported SQLite geo tables only, require at least two characters, debounce at 500 ms, return at most 10 compact results, and never load full city lists into the DOM.
- Filters on mobile belong in a bottom sheet/drawer. Keep the first render focused on city, dates, guests, sort, and compact results.
- Result cards must use selected sleeping-place columns, active property/room/place status, localized title fallback, one primary mobile image, key amenity labels, pricing summaries, and compatibility hints.
- Search must not load maps, full galleries, charts, or large JavaScript bundles by default.
- Availability filters use the half-open date range `[check_in, check_out)` through the `SleepingPlace::availableBetween()` contract.
- Total price shown on cards must come from `App\Services\PricingService`, not from Blade arithmetic.

### Advanced location search

- Location search must support country, city, district, street, landmark, proximity filters, and area-type filters without loading full lookup lists into the DOM.
- Place criteria map to compact URL/query keys: `country_id`, `city_id`, `district`, `street`, `landmark`, `near_center`, `near_metro`, `near_bus_stop`, `near_train_station`, `near_airport`, `near_university`, `near_work`, `near_hospital`, `near_sea`, `near_park`, `near_shopping_center`, `near_gym`, `near_coworking`, `near_nightlife`, `area_quiet`, `area_safe`, `area_residential`, `area_city_center`, `area_suburb`, `area_industrial`, `area_tourist`, `area_students`, `area_workers`, and `area_long_stay`.
- Country and city are autocomplete controls backed by imported SQLite geo tables. District, street, and landmark are short text/autocomplete filters using normalized local fields or offline/imported point data.
- Proximity filters must use stored coordinates, precomputed distance columns, or offline/imported points of interest. Do not call external geo APIs during search, and do not bulk-geocode through public Nominatim.
- `near_work` can use a user-saved work point or local landmark/area text. It must not send the user's private work address to a public geocoding API during search.
- Area-type filters such as quiet, safe, residential, city center, suburb, industrial, tourist, student, worker, and long-stay areas should use host-provided/property metadata, curated local tags, or imported offline area data with translated labels.
- Mobile UI should show only the most common location fields first, then reveal proximity and area-type filters in a bottom sheet with lazy sections and compact chips.

## Public sleeping-place detail

- `/places/{sleepingPlace}` renders `App\Livewire\Places\ShowSleepingPlace`; it is the canonical public detail page for a sleeping place.
- The first render may show the primary mobile image, compact thumbnails, summary, booking mini-card, exact sleeping-place facts, room/property summaries, rules, host card, and privacy-safe nearby guest count.
- Reviews and similar places must stay lazy-loaded Livewire components so the first mobile render remains light.
- Booking mini-card date changes recalculate nights, deposit, total, and unavailable-date warnings through `PricingService` and `AvailabilityService`.
- Favorite toggling uses `favorites.sleeping_place_id`; it must not require a legacy `Bed` record.
- Contact-host messaging may open a lightweight inline message form. Do not load the full chat UI on the first listing-detail render.
- Occupant information before booking must be privacy-safe: show counts and allowed summaries only, never guest names or private profile details.

## Host property wizard

- Property creation is a step-by-step Livewire class component, not one large form.
- `host.properties.create` and `host.properties.edit` use the wizard and save draft data after each completed step.
- The wizard stores only compact public state: scalar fields, selected IDs, and upload fields. Lookup results for countries, cities, amenities, and rules are computed from indexed database queries.
- Country and city autocomplete use imported SQLite geo data only; no external geo API calls are allowed during search or listing creation.
- Listing text must be saved in `property_translations` for English and Russian from day one.
- Amenities and rules must use translated lookup tables and pivot tables. Do not add hard-coded amenity/rule labels to Blade.
- Publishing from the review step changes `properties.status` from `draft` to `active`. Rooms, sleeping places, prices, calendars, and availability are added in later host flows.

## Host room wizard

- Room creation and editing use a step-by-step Livewire class component.
- Room drafts stay in `rooms.status = draft` until the host explicitly changes status.
- Room descriptions and notes must be saved in `room_translations` for English and Russian.
- Room rules use `room_rule` plus an optional room-specific note, never hard-coded labels in Blade.
- If `beds_count` is set, the host may generate matching `sleeping_places` records as drafts.
- Only draft rooms can be deleted from the host room list.

## Host sleeping place wizard

- Sleeping place creation and editing use a step-by-step Livewire class component.
- The sleeping place is the main rental unit; guest booking, pricing, calendar, and availability attach to `sleeping_places`.
- Use `sleeping_place_translations` for English and Russian title, description, and special conditions.
- Quick create may generate several similar draft sleeping places, but each draft must remain editable independently.
- Readiness checks should show title, exact photo, price, calendar, and rules before hosts publish a place.
- Photos must show the exact sleeping place, not only the room.

## Availability calendar

- Availability logic lives in `App\Services\AvailabilityService`; Blade and Livewire views must not calculate overlap rules inline.
- Every sleeping place has its own separate calendar keyed by `sleeping_place_id + date`.
- Availability belongs to a sleeping place, but inactive/closed property, inactive/closed room, or inactive/closed sleeping place status makes the place unavailable.
- Booking overlap uses `[check_in, check_out)`, allowing same-day checkout and next check-in when boundary rules allow it.
- Host calendar actions may open, close, mark cleaning, mark repair, set price overrides, set min/max nights, and set check-in/check-out flags.
- Host actions must not overwrite booking-linked rows such as `booked`, `pending_payment`, `pending_host_confirmation`, `guest_checked_in`, or `guest_checked_out`.
- Guest availability checks should return available/unavailable, specific unavailable dates, and nearby available ranges.
- Sleeping place date statuses are:
  - available / Свободно
  - occupied / Занято
  - pending_payment / Ожидает оплаты
  - pending_host_confirmation / Ожидает подтверждения хозяина
  - booked / Забронировано
  - guest_checked_in / Гость заселился
  - guest_checked_out / Гость выехал
  - closed_by_host / Закрыто хозяином
  - closed_by_service / Закрыто сервисом
  - cleaning / На уборке
  - repair / На ремонте
  - broken / Недоступно по причине поломки
  - complaint_blocked / Недоступно из-за жалобы
  - hidden / Временно скрыто
  - request_only / Доступно только по запросу
- `available` is directly selectable when all other rules pass; `request_only` can be selected only as a host-approval request. All other statuses block instant booking unless an explicit service rule says otherwise.
- Availability status labels and blocking explanations must use translation keys, not hard-coded UI strings.
- Availability queries must use indexed `sleeping_place_id + date` lookups, and status-filtered host calendar views should plan `sleeping_place_id + status + date` indexes.
- Double booking protection uses the half-open range `[check_in_date, check_out_date)`.
- A requested range overlaps an existing booking or hold when `requested_check_in < existing_check_out` and `requested_check_out > existing_check_in`.
- If one guest has a sleeping place from July 10 to July 15, blocked examples include July 9 to July 11, July 10 to July 12, July 14 to July 16, and July 11 to July 15.
- Allowed examples include ranges ending on or before July 10, ranges starting after July 15, and ranges starting on July 15 only when the first guest checks out on July 15 and same-day turnover is allowed by check-in/check-out flags and cleaning-gap rules.
- Booking/request actions must recheck availability inside the same transaction that writes booking rows, status history, price lines, and availability holds.
- Creating holds should write one `availability_days` row per blocked calendar day in `[check_in, check_out)` so the unique `sleeping_place_id + date` index helps reject duplicate day holds.
- Double-booking failures must return translated friendly messages and nearest available alternatives.
- Turnover rules between checkout and next check-in are part of availability validation.
- Turnover fields are:
  - minimum turnover time between checkout and check-in / `minimum_turnover_minutes`
  - cleaning required between guests / `cleaning_required_between_guests`
  - cleaning duration / `cleaning_duration_minutes`
  - inspection required after checkout / `inspection_required_after_checkout`
  - same-day check-in after previous checkout allowed / `same_day_check_in_allowed`
  - morning checkout and evening check-in allowed / `morning_checkout_evening_checkin_allowed`
  - earliest time for the next check-in / `earliest_next_check_in_time`
  - latest time for the previous checkout / `latest_previous_check_out_time`
- Same-day turnover is allowed only when host rules allow it, checkout/check-in flags allow the boundary date, the previous checkout time plus required turnover/cleaning/inspection time is before or equal to the requested next check-in time, and cleaning gap rules pass.
- If turnover time is insufficient, the date must be blocked, hidden from available checkout/check-in choices, or shown as `request_only` with a translated explanation depending on host rules.
- Availability tests must cover same-day checkout/check-in, insufficient cleaning time, required inspection, and morning-checkout/evening-check-in scenarios.

## Booking date selection

- Guest date selection uses `App\Livewire\Booking\BookingDateSelector` and `App\Services\PricingService`.
- `BookingDateSelector` stores only `sleepingPlaceId`, check-in/check-out dates, guest count, compact quote data, unavailable dates, and nearby ranges.
- Date selection fields are:
  - check-in date / `check_in_date`
  - check-in time / `check_in_time`
  - check-out date / `check_out_date`
  - check-out time / `check_out_time`
  - nights count / `nights_count`
  - stay days count / `stay_days_count`
  - calendar days count / `calendar_days_count`
  - early check-in requested / `early_check_in_requested`
  - late check-out requested / `late_check_out_requested`
  - flexible check-in time / `check_in_time_flexible`
  - flexible check-out time / `check_out_time_flexible`
  - host time approval required / `host_time_approval_required`
  - check-in time comment / `check_in_time_comment`
  - check-out time comment / `check_out_time_comment`
- `nights_count`, `stay_days_count`, and `calendar_days_count` are read-only derived values from the pricing/date service.
- Overnight sleeping-place rentals use the half-open date range `[check_in_date, check_out_date)` for billing.
- `nights_count` is the day difference between checkout and check-in. In the current non-hourly mode, `stay_days_count` equals `nights_count` and is the payable rental quantity.
- `calendar_days_count` is the inclusive human-facing presence count. Example: July 10 check-in to July 13 checkout is 3 nights / 3 payable stay days, but 4 calendar presence days because the guest is present on July 10, 11, 12, and part of July 13.
- Calendar presence days are explanatory and must not become the default billing multiplier for sleeping-place rentals.
- Time fields, booleans, radios, and selects use `wire:model.change`; short comments use `wire:model.blur`.
- `PricingService::calculate()` is the only place that calculates stay days, nights, calendar days, weekday/weekend counts, date-specific prices, weekly/monthly discounts, cleaning fee, deposit, service fee placeholder, refundable/non-refundable amounts, and payment/cancellation deadlines.
- When a guest chooses check-in and check-out dates, the quote must automatically include:
  - stay days / `stay_days_count`
  - nights / `nights_count`
  - calendar days of stay / `calendar_days_count`
  - subtotal/base price
  - discount amount
  - deposit amount
  - cleaning fee
  - service fee
  - total due
  - free cancellation until date
  - cancellation penalty starts at date
  - host payout date
  - check-in reminder date
  - check-out reminder date
- Automatic date checks run before showing a payable quote, creating a booking request, or confirming a booking.
- Date checks must reject:
  - checkout before check-in
  - same-day checkout unless a future daily/hourly rental mode explicitly allows it
  - dates when the sleeping place is occupied, booked, or held
  - dates when the property, room, or sleeping place is closed by the host
  - dates when the room is unavailable or under repair
  - dates that violate a required cleaning gap after checkout
  - stays shorter than the minimum stay
  - stays longer than the maximum stay
  - check-in dates where the host has disabled check-in for that weekday
  - check-out dates where the host has disabled check-out for that weekday
  - bookings by guests who have not completed required verification
  - bookings where the guest age does not match room rules
  - bookings by male guests for female-only rooms when the host configured that format
  - bookings by female guests for male-only rooms when the host configured that format
  - bookings where guest count exceeds the sleeping-place limit
- Date check failures must be returned as translation keys with friendly messages in the active locale.
- When a guest selects a check-in date, calendar behavior must:
  - highlight available check-out dates
  - hide or disable unavailable check-out dates without rendering a large hidden DOM
  - show the earliest possible check-out date
  - show the latest possible check-out date
  - warn if the selected range contains an occupied, held, blocked, repair, cleaning, or otherwise unavailable date
  - suggest the nearest available date ranges
  - suggest similar sleeping places
  - suggest a neighboring room where appropriate
  - suggest another sleeping place from the same host where appropriate
  - automatically recalculate price, discounts, deposit, cleaning fee, service fee, total due, cancellation deadlines, payout timing, and reminders when dates change
- Calendar availability responses must be compact DTO arrays with date keys, availability flags, min/max checkout bounds, warning reason keys, nearest ranges, and lightweight suggestion IDs/card DTOs.
- Calendar UI must not load maps, full galleries, or large result lists after a date change.
- Automatic price recalculation runs when the guest changes dates, guest count, timing options, promo code, or other booking conditions.
- Price calculation fields are:
  - check-in date / `check_in_date`
  - check-out date / `check_out_date`
  - stay days count / `stay_days_count`
  - base price per stay day / `base_price_per_stay_day`
  - weekday price / `weekday_price`
  - weekend price / `weekend_price`
  - holiday price / `holiday_price`
  - weekly price / `weekly_price`
  - monthly price / `monthly_price`
  - long-stay discount / `long_stay_discount`
  - weekly discount / `weekly_discount`
  - monthly discount / `monthly_discount`
  - early-booking discount / `early_booking_discount`
  - last-minute discount / `last_minute_discount`
  - new-guest discount / `new_guest_discount`
  - personal discount / `personal_discount`
  - early check-in fee / `early_check_in_fee`
  - late check-out fee / `late_check_out_fee`
  - extra guest fee / `extra_guest_fee`
  - cleaning fee / `cleaning_fee`
  - deposit / `deposit_amount`
  - service fee / `service_fee`
  - taxes or city fees / `tax_or_city_fee`
  - promo code / `promo_code`
  - total discount amount / `discount_amount`
  - amount before discounts / `amount_before_discount`
  - amount after discounts / `amount_after_discount`
  - total due / `total_due`
  - host payout amount / `host_payout_amount`
  - refundable amount / `refundable_amount`
  - non-refundable amount / `non_refundable_amount`
- `PricingService` must return compact line-item DTOs with translation keys for every visible label, explanation, fee, discount, warning, and refund note.
- Money calculations must not use Blade or Livewire view math. Persist quote line items to `booking_price_lines` only when a booking or booking request is created.
- Pricing logic rules:
  - stays shorter than 7 days use the per-stay-day price
  - stays from 7 days may apply a weekly discount
  - stays from 30 days may apply a monthly discount
  - dates that fall on weekends use the weekend price when no stronger date price applies
  - dates that fall on holidays use the holiday price when no date-specific override applies
  - host date-specific prices override normal weekday, weekend, and holiday prices
  - promo codes recalculate the final quote
  - changing checkout date recalculates the full quote from scratch
  - a second guest on a two-person sleeping place can add an extra guest fee
  - early check-in adds a fee or creates a host-approval request, depending on host rules
  - late check-out adds a fee or creates a host-approval request, depending on host rules
- Date-specific nightly price precedence is: host override, then holiday price, then weekend price, then weekday/base price.
- Weekly/monthly/long-stay discounts, promo codes, personal discounts, and new-guest discounts must be represented as separate line items so the guest can see exactly what changed.
- Early check-in and late check-out must never be silently accepted when host approval is required; return translated warning/request keys in the quote DTO.
- Guest-facing price summaries must show daily price lines before totals, then explain fees, discounts, current payment, and refundable deposit.
- Price display example:
  - 10 July / EUR 20
  - 11 July / EUR 20
  - 12 July / EUR 25
  - stay days / 3
  - accommodation amount / EUR 65
  - discount / EUR 5
  - cleaning fee / EUR 10
  - deposit / EUR 50
  - service fee / EUR 6
  - total due now / EUR 126
  - refundable after checkout / EUR 50 deposit
- The price display example must be implemented through translation keys such as `booking.price.lines.nightly`, `booking.price.summary.stay_days`, `booking.price.summary.accommodation`, `booking.price.summary.discount`, `booking.price.summary.cleaning`, `booking.price.summary.deposit`, `booking.price.summary.service_fee`, `booking.price.summary.total_due_now`, and `booking.price.summary.refundable_deposit`.
- Price overrides come from `availability_days.price_override` for dates in `[check_in, check_out)`. Min/max-night overrides on availability days may tighten the sleeping-place default rules.
- Price lines and warnings must be returned as translation keys and rendered through locale files.
- Blade and Livewire views must render the quote from the service output; they must not calculate date counts, fees, deadlines, or reminders inline.

## Advanced booking logic

- Booking classification must support a primary booking flow plus payment/deposit modes and optional booking modifiers.
- Do not force all booking variants into one status string; keep `status`, `payment_status`, flow type, deposit mode, payment mode, and special modifiers separate where useful.
- Primary booking flows are:
  - instant_booking / Мгновенное бронирование
  - host_confirmation_booking / Бронирование с подтверждением хозяина
  - stay_request / Запрос на проживание
  - preliminary_inquiry / Предварительный запрос
  - long_term_request / Долгосрочная заявка
  - urgent_today_booking / Срочное бронирование на сегодня
- Payment and deposit modes are:
  - awaiting_payment / Бронирование с ожиданием оплаты
  - with_deposit / Бронирование с залогом
  - without_deposit / Бронирование без залога
  - partial_payment / Бронирование с частичной оплатой
  - full_payment / Бронирование с полной оплатой
- Booking modifiers and special scenarios are:
  - extension / Бронирование с продлением
  - relocation / Бронирование с переселением на другое место
  - group_booking / Бронирование для группы
  - two_guest_sleeping_place / Бронирование одного места для двух гостей, если место двухместное
- A two-guest booking for one sleeping place is allowed only when `sleeping_places.max_guests >= 2`, room rules allow the guests, and pricing recalculates extra guest fees.
- Group booking must still reserve availability per sleeping place and prevent double booking per `sleeping_place_id + date`.
- Extension and relocation are linked booking scenarios; they must keep status history and preserve the original booking audit trail.
- Every flow type, payment/deposit mode, modifier, status label, and explanation must use translation keys.
- Booking records, booking DTOs, and booking detail screens must support:
  - booking number / `booking_number`
  - booking status / `status`
  - guest / `guest_user_id`
  - host / `host_user_id`
  - property / `property_id`
  - room / `room_id`
  - sleeping place / `sleeping_place_id`
  - check-in date / `check_in_date`
  - check-in time / `check_in_time`
  - check-out date / `check_out_date`
  - check-out time / `check_out_time`
  - stay days count / `stay_days_count`
  - calendar days count / `calendar_days_count`
  - guests count / `guests_count`
  - price per stay day / `price_per_stay_day`
  - price for the full period / `period_price_amount`
  - discount / `discount_amount`
  - deposit / `deposit_amount`
  - cleaning fee / `cleaning_fee_amount`
  - service fee / `service_fee_amount`
  - total amount / `total_amount`
  - currency / `currency`
  - payment status / `payment_status`
  - payment method / `payment_method`
  - payment date / `paid_at`
  - payment deadline / `payment_deadline_at`
  - guest message / `guest_message`
  - host response / `host_response`
  - document verification required / `requires_document_verification`
  - phone verification required / `requires_phone_verification`
  - identity verification required / `requires_identity_verification`
  - decline reason / `decline_reason`
  - cancellation reason / `cancellation_reason`
  - cancelled by / `cancelled_by`
  - cancellation policy / `cancellation_policy`
  - refund amount / `refund_amount`
  - refund status / `refund_status`
  - check-in instructions / `check_in_instructions`
  - guest confirmed check-in / `guest_checked_in_at`
  - host confirmed check-in / `host_confirmed_check_in_at`
  - guest confirmed check-out / `guest_checked_out_at`
  - host confirmed check-out / `host_confirmed_check_out_at`
  - has dispute / `has_dispute`
  - has complaint / `has_complaint`
  - guest review submitted / `guest_review_submitted_at`
  - host review submitted / `host_review_submitted_at`
- Use timestamps for confirmations, payment, and review submission where possible; mobile DTOs can expose derived boolean flags.
- `booking_number` must be unique and user-friendly. Booking detail queries should use selected columns and eager load only the compact guest/host/property/room/sleeping-place data needed for the mobile screen.
- Booking lifecycle statuses are:
  - draft / Черновик
  - created / Создано
  - awaiting_host_approval / Ожидает подтверждения хозяина
  - awaiting_guest_response / Ожидает ответа гостя
  - awaiting_payment / Ожидает оплаты
  - awaiting_identity_verification / Ожидает проверки личности
  - awaiting_document_verification / Ожидает проверки документов
  - confirmed / Подтверждено
  - paid / Оплачено
  - ready_for_check_in / Готово к заселению
  - guest_checked_in / Гость заселился
  - in_progress / Проживание идет
  - check_out_soon / Скоро выезд
  - guest_checked_out / Гость выехал
  - awaiting_room_inspection / Ожидает проверки помещения
  - awaiting_deposit_return / Ожидает возврата залога
  - completed / Завершено
  - awaiting_review / Ожидает отзыва
  - closed / Закрыто
  - declined_by_host / Отклонено хозяином
  - cancelled_by_guest / Отменено гостем
  - cancelled_by_host / Отменено хозяином
  - cancelled_by_service / Отменено сервисом
  - unpaid / Не оплачено
  - guest_no_show / Гость не приехал
  - host_unresponsive / Хозяин не вышел на связь
  - dispute_opened / Возник спор
  - frozen_pending_dispute_resolution / Заморожено до решения спора
  - requires_support_intervention / Требует вмешательства поддержки
- Status labels must live under translation keys such as `statuses.booking.*`; do not hard-code these Russian or English labels in Blade, Livewire, notifications, emails, tests, factories, or seeders.
- `requires_support_intervention` is a lifecycle state only and must not introduce a support/staff/admin panel.
- Keep `payment_status` separate from booking lifecycle status; `paid` and `unpaid` may be guest-facing booking states, but payment records remain the source of truth for money movement.
- Every status transition must write `booking_status_histories` with actor, previous status, new status, reason key, and timestamp where practical.

## Booking request flow

- If `sleeping_places.instant_booking_enabled` is false, the guest sends a request instead of creating an immediately payable instant booking.
- Booking request creation is handled by a service/action, not by Blade. It must recheck availability and quote totals before storing the request.
- Request fields are:
  - guest / `guest_user_id`
  - host / `host_user_id`
  - sleeping place / `sleeping_place_id`
  - check-in date / `check_in_date`
  - check-out date / `check_out_date`
  - stay days count / `stay_days_count`
  - guests count / `guests_count`
  - travel purpose / `travel_purpose`
  - planned arrival time / `planned_arrival_time`
  - message to host / `guest_message`
  - has luggage / `has_luggage`
  - needs luggage space / `needs_luggage_space`
  - needs early check-in / `needs_early_check_in`
  - needs late check-out / `needs_late_check_out`
  - needs residence registration / `needs_residence_registration`
  - needs reporting documents / `needs_reporting_documents`
  - request status / `request_status`
  - host response / `host_response`
  - decline reason / `decline_reason`
  - request expiration time / `request_expires_at`
- Request statuses must be enum-like values with translated labels. At minimum support `awaiting_host_approval`, `accepted`, `declined_by_host`, `expired`, and `cancelled_by_guest`.
- Host response, decline reason, warning text, and status labels must use translation keys.

## Host booking request view

- Host request cards and detail screens must show only privacy-safe guest summary data:
  - guest display name
  - guest photo
  - guest age or age range according to privacy settings
  - guest city according to privacy settings
  - communication languages
  - guest rating
  - previous stays count
  - reviews count
  - identity verified status
  - phone verified status
  - email verified status
  - complaint count summary
  - check-in date
  - check-out date
  - stay days count
  - total amount
  - travel purpose
  - guest message
  - compatibility with room/property rules
  - warning reasons
- Host request lists use compact queries and selected columns. Detailed compatibility and warnings are calculated only for the selected request detail, not every first-render card.

## Host booking request warnings

The request assessment service may show translated warning keys when:

- Guest wants to arrive at night.
- Guest wants to leave very early.
- Guest has not passed identity verification.
- Guest has no reviews.
- Guest has prior cancellations.
- Guest is booking at the last minute.
- Guest requests a stay longer than the sleeping place maximum.
- Selected dates require a cleaning gap.
- Guest has complaints.
- Guest says they smoke and smoking is forbidden.
- Guest wants to bring a pet and pets are forbidden.
- Guest wants to bring a second person while the sleeping place allows only one guest.

Warnings are advisory for the host and must not replace authorization, availability checks, rule checks, or privacy settings.

## Payment placeholder

- Payment provider integration is not connected yet; use `App\Actions\Payments\ConfirmDemoPayment` only as a local/testing demo driver.
- Never expose the demo "mark as paid" action in production, and do not add finance/admin staff workflows.
- Every payment attempt must write `payment_records` with provider, reference, amount, currency, status, timestamps, and metadata.
- Successful payment moves the booking to `confirmed`, sets `payment_status = paid`, blocks availability dates as `booked`, exposes permitted access instructions, and notifies the host.
- Failed payment keeps the booking in `awaiting_payment` with `payment_status = failed` so the guest can retry.
- Payment UI must stay mobile-first, translated, and compact: booking summary, line items, total, deposit explanation, refund/cancellation note, and provider placeholder only.

## Cancellation and refunds

- Cancellation estimates are calculated by `App\Services\RefundCalculator`; Blade and Livewire views must not calculate refund amounts inline.
- `App\Enums\CancellationPolicy` owns policy metadata: free-cancellation hours, partial-refund threshold, fee refund flags, and explanation translation keys.
- Guest cancellation uses the mobile `App\Livewire\Booking\CancelBooking` page so the guest sees paid amount, refund estimate, deposit refund, non-refundable amount, and reason confirmation before the action runs.
- Host cancellation returns the paid amount and must not introduce finance/admin workflows.
- `CancellationService` owns state changes: booking status, refund amount/status, status history, availability release, refund request/payment placeholder record, and notifications.
- Deposits are refundable before check-in even when the stay amount is non-refundable.

## Guest trip management

- Guest trip screens live under `App\Livewire\Trips` and use the canonical sleeping-place booking hierarchy.
- `/trips` shows upcoming trips, `/trips/current` shows the active checked-in stay, `/trips/past` shows checked-out/completed trips, and `/trips/cancelled` shows cancelled/expired trips.
- `guest.bookings.show` renders the mobile booking detail screen and must keep address/instructions hidden until the booking status and payment/address rules allow access.
- Trip UI must show compact booking summaries, host contact, rules, receipt lines, deposit status, and relevant action buttons without loading maps, galleries, or full message threads.
- Keep trip presentation logic in `App\Support\Trips\TripBookingPresenter`; Blade should only render prepared labels, booleans, and line items.

## Completed-stay reviews

- Reviews are allowed only after a booking reaches `completed`.
- `App\Services\ReviewService` owns review creation, one-review-per-booking checks, review-window visibility, simple friendly-language warnings, notifications, and rating aggregate updates.
- Guest reviews about places use `guest_to_place`; host reviews about guests use `host_to_guest`.
- Store canonical user IDs on `reviews.guest_user_id` and `reviews.host_user_id`, while keeping legacy reviewer/reviewee fields for compatibility.
- New review text should use `liked_text`, `improvement_text`, `advice_text`, or `comment`; legacy `positive_comment`, `negative_comment`, and `advice` remain aliases while old UI is bridged.
- Reviews start as `pending` and become public only after both guest and host reviews exist or the booking review window expires. Public listing/profile queries must use `Review::visible()`.
- Review forms must stay mobile-first Livewire class components with compact scalar state and optional small guest review photo uploads.

## Guest-host messaging

- Guest-host chat uses `MessageThread` as the public messaging surface. Supported thread types are `pre_booking`, `booking`, `current_stay`, and `complaint_related`.
- Inbox and thread pages must be Livewire class components, mobile-first, translated, and limited to compact thread/message payloads.
- `MessageService` owns send/read behavior, participation checks, legacy conversation bridge writes, notifications, and exact-address privacy checks.
- Hosts must not expose exact address fragments before booking/payment rules allow it. Pre-booking messages may share general arrival details only.
- Composer attachments are limited to small images or PDF documents and store compact metadata, not large public arrays.
- Quick message templates must be localized for both guest and host roles.
- Do not load full chat threads inside listing cards, trip cards, booking lists, or host request lists; link to the thread page instead.

## User notifications

- User-facing notifications use the existing `notifications` table with `user_id`, `type`, `title_key`, `body_key`, `data.params`, `action_url`, `status`, and `read_at`.
- `App\Services\NotificationService` is the shared entry point for booking/payment/stay/message/product notification creation. Do not write translated notification text directly into the database.
- Notification titles and bodies must resolve through translation keys with params, so English and Russian can render the same stored row correctly.
- The mobile notification bell may run only a compact unread count query. The notifications page may show the latest 50 rows and must support mark-as-read and mark-all-as-read.
- Do not add staff/admin notification consoles, broadcast infrastructure, or external push providers until explicitly requested.

## Booking extension

- Guests request extensions through `App\Livewire\Extensions\ExtendStay`, embedded on the current-stay screen and available through `bookings.extend`.
- Extension public state must stay compact: booking ID, requested checkout date, short guest message, active extension ID, and compact quote preview only.
- Extension logic belongs in `App\Services\ExtensionService`; Blade must not calculate date overlap, extra nights, or price totals.
- Extension records, DTOs, and screens must cover `booking_id`, `current_check_out_date`, `new_check_out_date`, `additional_stay_days_count`, `additional_price_per_stay_day`, `extension_discount_amount`, `extension_amount_due`, `status`, `host_approval_required`, `host_response`, `decline_reason`, and `extension_paid_at`.
- A booking can be extended only when it is `confirmed`, `paid`, `ready_for_check_in`, `guest_checked_in`, or `in_progress`, the sleeping place allows extensions, the new checkout is after the current checkout, max nights are not exceeded, and `[current_check_out_date, new_check_out_date)` is available.
- `ExtensionService` must check that another guest has not booked or held the sleeping place in the added range before showing the quote and again inside the approval/payment transaction.
- Extension pricing charges only additional nights, discount, and service fee. Do not repeat the original cleaning fee or deposit.
- Host approval uses `App\Livewire\Extensions\ManageExtension` and must recheck availability before moving a request to payment.
- Instant extension goes straight to `awaiting_payment`; host-approval extension goes to `awaiting_host_approval`.
- Extension statuses must be enum-like translated values. At minimum support `draft`, `awaiting_host_approval`, `awaiting_payment`, `approved`, `declined`, `paid`, `applied`, `cancelled`, and `expired`.
- Local/testing demo payment writes `payment_records.provider = extension_demo_manual`, approves the extension, updates the original booking check-out date, stay days, totals, status history, and price lines, blocks availability, and notifies both sides. Production must not expose demo extension payment.
- Extension decline reasons, host responses, amount-due explanations, payment dates, and notifications must be translated. Do not add finance, support, staff, or admin workflows for extension handling.

## Booking relocation

- Guests may request relocation when there are noisy neighbors, an uncomfortable bed, conflict with another resident, a breakage/malfunction, a host offer for another place, desire for a more private room, desire for a cheaper place, or desire for a more expensive but more comfortable place.
- Relocation can also be offered by a host. The flow must preserve the original booking audit trail instead of deleting and recreating the stay.
- Relocation records, DTOs, and screens must cover `booking_id`, `current_sleeping_place_id`, `new_sleeping_place_id`, `reason`, `relocation_date`, `price_difference_amount`, `price_difference_payer`, `guest_consent_required`, `host_consent_required`, `status`, `guest_comment`, `host_comment`, and `support_comment`.
- Relocation reasons must be enum-like translated values. At minimum support `noisy_neighbors`, `uncomfortable_bed`, `resident_conflict`, `broken_item`, `host_offered_other_place`, `wants_more_private_room`, `wants_cheaper_place`, and `wants_more_comfort`.
- Relocation statuses must be enum-like translated values. At minimum support `draft`, `requested_by_guest`, `offered_by_host`, `awaiting_guest_consent`, `awaiting_host_consent`, `awaiting_payment`, `approved`, `declined`, `applied`, `cancelled`, and `expired`.
- `RelocationService` must check that the new sleeping place is available for `[relocation_date, booking.check_out_date)`, reject another guest's booking/hold on the new place, keep the old place blocked before `relocation_date`, and change old/new availability rows only after approval/application.
- Relocation pricing must recalculate the remaining stay, price difference, extra amount due, refund/credit, and deposit implications through a service/action. Blade and Livewire views must render prepared DTO values only.
- If the host offers a relocation, guest consent is required before application. If the guest requests a relocation, host consent is required unless rules allow instant self-service relocation.
- Applying relocation updates the original booking's sleeping-place context for the remaining stay, writes status history, persists price lines or adjustment records, updates availability, and notifies guest and host.
- `support_comment` is a reserved data field only. Do not add support, staff, finance, moderation, or admin workflows for relocation handling.

## Check-in and check-out

- Guest check-in uses `App\Livewire\Checkin\CheckIn` and `App\Actions\Bookings\GuestCheckIn`; the component stores only `bookingId` and checklist booleans.
- Check-in instructions are shown only for the guest's confirmed/paid booking where address rules allow access.
- Check-in problem reports use `App\Livewire\Checkin\ProblemReport` with Livewire file uploads, small image validation, and compact stored paths in `checkin_records.problem_media`.
- Host check-in confirmation uses `App\Actions\Bookings\HostConfirmCheckIn` and moves `checked_in` bookings to `in_progress`.
- Guest check-out uses `App\Livewire\Checkin\CheckOut` and `App\Actions\Bookings\GuestCheckOut`; host checkout confirmation uses `HostConfirmCheckOut`.
- The status lifecycle is `confirmed -> checked_in -> in_progress -> checked_out -> completed`, with every transition written to `booking_status_histories`.
- Deposit release/hold decisions are stored through `checkout_records.deposit_action` and synchronized to `deposit_records`; no finance/admin panel is introduced.

## Complaints and problem reports

- Full booking complaints use `App\Livewire\Complaints\CreateComplaint`, `App\Livewire\Complaints\ComplaintDetail`, and `App\Services\ComplaintService`.
- The check-in problem report remains a fast check-in-specific path; booking detail and current stay link to the broader complaint flow.
- Guests and hosts may report only bookings where they are participants. The reported side is the other booking participant when available.
- Complaint rows keep legacy fields (`reference`, `reporter_id`, `urgency`, `photos`, `respondent_reply`, `resolution_notes`, `deposit_withheld`) while syncing canonical fields (`complaint_number`, `reporter_user_id`, `priority`, `media`, `other_side_response`, `resolution_text`, `deposit_hold_amount`).
- Complaint statuses are saved in `complaints.status`; timeline entries are stored in `complaint_status_histories`.
- Creating a complaint sets `bookings.has_complaint = true` and may move active/completed booking rows to `problem_reported`.
- The other side can add one response. No staff/admin resolution UI exists yet, so unresolved reports stay stored for future review tooling.
- Complaint media uploads are limited to 6 small images and must not load or render full galleries.

## Amenities and rules

- The initial amenity/rule catalog is code-based and seeded through `AmenityRuleSeeder`; there is no admin UI for catalog management yet.
- Catalog labels must live in `amenity_translations` and `rule_translations` for `en` and `ru`.
- Host selection UI must use the reusable Livewire pickers and keep parent state to selected IDs only.
- Picker lookup lists are cached per locale and invalidated when amenities, rules, or their translations change.
- Attachments use explicit pivot tables for property, room, and sleeping place owners.

## Delivery checks

Every new feature must include, when applicable:

- Migration if data is needed.
- Model relationships.
- Factory.
- Seeder if lookup data is introduced.
- Livewire class component.
- Blade view.
- Flux UI.
- Mobile-first layout.
- English translations.
- Russian translations.
- Validation.
- Friendly empty state.
- Friendly loading state.
- Authorization or policy if needed.
- Tests.
- Indexes for queries.
- Docs update if behavior is important.

Before considering a slice complete, run:

```bash
php artisan test
./vendor/bin/pint
npm run build
```

Also inspect `php artisan route:list --except-vendor` and confirm there are no admin routes or Volt components.
