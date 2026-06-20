# Project Rules

## Product boundary

rent2gether is a mobile-first marketplace for renting an individual sleeping place inside a room and property. A user may act as a guest, a host, or both.

The product hierarchy is:

`User -> HostProfile -> Property -> Room -> SleepingPlace -> Availability -> Booking`

The core marketplace loop is:

- Guest chooses city, dates, and sleeping place.
- System calculates availability, nights, calendar days, price, discount, deposit, rules, and compatibility.
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
- Guest preferences are stored in `guest_preferences`; compatibility scoring must stay in `App\Services\CompatibilityService` or a similarly testable service, not in Blade.
- Compatibility output must include a 0-100 score, a `great/good/attention/not_suitable` fit level, positive reasons, and warning reasons. Reason text must use translation keys.

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
- Availability belongs to a sleeping place, but inactive property, inactive room, or inactive sleeping place status makes the place unavailable.
- Booking overlap uses `[check_in, check_out)`, allowing same-day checkout and next check-in when boundary rules allow it.
- Host calendar actions may open, close, mark cleaning, mark repair, set price overrides, set min/max nights, and set check-in/check-out flags.
- Host actions must not overwrite `booked`, `pending_payment`, or `pending_approval` rows linked to a booking.
- Guest availability checks should return available/unavailable, specific unavailable dates, and nearby available ranges.

## Booking date selection

- Guest date selection uses `App\Livewire\Booking\BookingDateSelector` and `App\Services\PricingService`.
- `BookingDateSelector` stores only `sleepingPlaceId`, check-in/check-out dates, guest count, compact quote data, unavailable dates, and nearby ranges.
- `PricingService::calculate()` is the only place that calculates nights, calendar days, weekday/weekend counts, date-specific prices, weekly/monthly discounts, cleaning fee, deposit, service fee placeholder, refundable/non-refundable amounts, and payment/cancellation deadlines.
- Date validation rejects past dates, same-day checkout, checkout before check-in, min/max-night violations, sleeping-place guest-limit violations, and ranges crossing booked or blocked dates.
- Price overrides come from `availability_days.price_override` for dates in `[check_in, check_out)`. Min/max-night overrides on availability days may tighten the sleeping-place default rules.
- Price lines and warnings must be returned as translation keys and rendered through locale files.

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
- A booking can be extended only when it is `confirmed`, `paid`, `ready_for_checkin`, `checked_in`, `in_progress`, or `active_stay`, the sleeping place allows extensions, the new checkout is after the current checkout, max nights are not exceeded, and `[current_checkout, requested_new_checkout)` is available.
- Extension pricing charges only additional nights, discount, and service fee. Do not repeat the original cleaning fee or deposit.
- Host approval uses `App\Livewire\Extensions\ManageExtension` and must recheck availability before moving a request to payment.
- Instant extension goes straight to `awaiting_payment`; host-approval extension goes to `awaiting_host_approval`.
- Local/testing demo payment writes `payment_records.provider = extension_demo_manual`, approves the extension, updates the original booking dates/totals, blocks availability, and notifies both sides. Production must not expose demo extension payment.

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
