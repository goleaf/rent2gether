# Expanded Sleeping Place Booking Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Привести rent2gether к полной логике аренды конкретного спального места: даты, доступность, цена, заявки, бронирования, продления и переселения работают автоматически, прозрачно и тестируемо.

**Architecture:** Не строить новую параллельную систему. Расширять существующие модели, сервисы, Livewire class components, Blade views, переводы и тесты, сохраняя центральную цепочку `User -> Property -> Room -> SleepingPlace -> Availability -> Booking -> Stay`. Вся бизнес-логика живет в `app/Services/<Domain>/` и `app/Actions/<Domain>/`; Blade только отображает подготовленные DTO/массивы и translation keys.

**Tech Stack:** Laravel 13.16.1, PHP 8.5, Livewire 4.3.1, Flux Pro 2.x, Tailwind CSS 4, SQLite, PHPUnit 12, Laravel Boost.

---

## Рабочие границы

- Не создавать `app/Http/Controllers/`, Filament, Volt, Inertia, React/Vue или admin/staff/support/finance панели.
- Новые пользовательские страницы - только Livewire class components под `app/Livewire/...` и views под `resources/views/livewire/...`.
- Новые сервисы - только внутри доменных директорий: `app/Services/Bookings/`, `app/Services/Availability/`, `app/Services/Pricing/`, `app/Services/BookingRequests/`, `app/Services/Notifications/`.
- Все видимые строки - через `lang/en/*.php` и `lang/ru/*.php`.
- Все запросы - Eloquent, scopes, eager loading, selected columns, indexes. Без raw SQL в коде.
- В текущем дереве уже есть незавершенные пользовательские изменения: `database/factories/BookingStayFactory.php`, `database/seeders/BulkMarketplaceSeeder.php`, `tests/Feature/SavedSearchesFeatureTest.php`. Не трогать их без явной необходимости.

## Существующие поверхности, которые надо расширять

- Даты и quote: `app/Services/Bookings/StayLengthCalculatorService.php`, `BookingDateValidationService.php`, `BookingDateSelectionService.php`, `BookingPriceQuoteService.php`.
- Pricing engine: `app/Services/Pricing/BookingPriceQuoteEngine.php`, `NightlyPriceLineService.php`, `DiscountCalculatorService.php`, `FeeCalculatorService.php`, `DepositCalculatorService.php`, `HostPayoutCalculatorService.php`, `RefundabilityCalculatorService.php`.
- Availability: `app/Services/Availability/AvailabilityService.php`, `SleepingPlaceTurnoverService.php`, `SleepingPlaceDateLockService.php`, `SleepingPlaceAvailabilitySuggestionService.php`.
- Booking requests: `app/Services/BookingRequests/*`, `app/Models/BookingRequest.php`, `BookingRequestWarning.php`.
- Booking core: `app/Services/Bookings/BookingCreationService.php`, `BookingQuoteConversionService.php`, `BookingCalendarIntegrationService.php`, `BookingStatusService.php`, `BookingTimelineDateService.php`.
- Extensions: `app/Services/Bookings/BookingExtension*`.
- Relocations: `app/Services/Bookings/BookingRelocation*`.
- Guest booking UI: `app/Livewire/Bookings/Create/BookingCreatePage.php`, `resources/views/livewire/bookings/create/*`, `resources/views/livewire/bookings/dates/*`, `resources/views/livewire/bookings/pricing/*`, `resources/views/livewire/bookings/quotes/*`.
- Host request UI: `app/Livewire/Host/BookingRequests/*`, `resources/views/livewire/host/booking-requests/*`.

## Task 1: Baseline Audit And Gap Matrix

**Files:**
- Read: `AGENTS.md`
- Read: `docs/PROJECT_STRUCTURE.md`
- Read: `docs/BOOKING_DATES_AND_QUOTES.md`
- Read: `docs/PRICING_ENGINE.md`
- Read: `docs/SLEEPING_PLACE_AVAILABILITY.md`
- Read: `docs/DATE_LOCKS_AND_DOUBLE_BOOKING.md`
- Read: `docs/TURNOVER_RULES.md`
- Read: `docs/BOOKING_REQUESTS.md`
- Read: `docs/BOOKING_EXTENSIONS.md`
- Read: `docs/BOOKING_RELOCATIONS.md`

- [ ] **Step 1: Capture working tree ownership**

Run:

```bash
git status --short
```

Expected: note unrelated modified files before editing and avoid staging them.

- [ ] **Step 2: Inspect versioned docs and schema**

Use Laravel Boost:

```text
application_info
database_schema(summary: true)
search_docs(["eloquent relationships transactions", "livewire validation testing", "pagination cursorPaginate"])
```

Expected: confirm Laravel 13, Livewire 4, SQLite, and existing table names.

- [ ] **Step 3: Build the gap checklist inside this plan before coding**

Map each user requirement to one of these domains:

```text
Hierarchy -> sleeping place model and listing creation
Dates -> StayLengthCalculatorService and BookingDateValidationService
Calendar -> AvailabilityService and SleepingPlaceTurnoverService
Price -> BookingPriceQuoteEngine and line calculators
Booking request -> BookingRequests services and host views
Booking lifecycle -> BookingStatus, BookingStatusService, timeline
Extension -> BookingExtension services
Relocation -> BookingRelocation services
UI -> Livewire booking/host screens
I18n -> lang/en and lang/ru files
Tests -> focused feature/unit tests plus architecture tests
```

- [ ] **Step 4: Run the current focused baseline**

Run:

```bash
php artisan test --compact tests/Unit/StayLengthCalculatorServiceTest.php tests/Feature/BookingDatesAndQuotesFeatureTest.php tests/Feature/BookingRequestsFeatureTest.php tests/Feature/BookingExtensionFlowPointThirteenTest.php tests/Feature/BookingRelocationFlowPointFourteenTest.php
```

Expected: either pass or record exact existing failures before changing code.

## Task 2: Canonical Domain Contracts

**Files:**
- Modify: `app/Enums/AvailabilityStatus.php`
- Modify: `app/Enums/BookingStatus.php`
- Inspect/modify: `app/Enums/BookingType.php`
- Create if missing: `app/Enums/BookingFlowType.php`
- Create if missing: `app/Enums/BookingPaymentMode.php`
- Create if missing: `app/Enums/BookingModifier.php`
- Modify: `lang/en/statuses.php`
- Modify: `lang/ru/statuses.php`
- Test: `tests/Unit/DomainStatusContractTest.php`

- [ ] **Step 1: Write contract tests for required availability statuses**

Test the exact canonical values:

```php
[
    'available',
    'occupied',
    'pending_payment',
    'pending_host_confirmation',
    'booked',
    'guest_checked_in',
    'guest_checked_out',
    'closed_by_host',
    'closed_by_service',
    'cleaning',
    'repair',
    'broken',
    'complaint_blocked',
    'hidden',
    'request_only',
]
```

Expected: test fails until enum and translations expose these canonical labels.

- [ ] **Step 2: Write contract tests for booking lifecycle statuses**

Test canonical values:

```php
[
    'draft',
    'created',
    'awaiting_host_approval',
    'awaiting_guest_response',
    'awaiting_payment',
    'awaiting_identity_verification',
    'awaiting_document_verification',
    'confirmed',
    'paid',
    'ready_for_check_in',
    'guest_checked_in',
    'in_progress',
    'check_out_soon',
    'guest_checked_out',
    'awaiting_room_inspection',
    'awaiting_deposit_return',
    'completed',
    'awaiting_review',
    'closed',
    'declined_by_host',
    'cancelled_by_guest',
    'cancelled_by_host',
    'cancelled_by_service',
    'unpaid',
    'guest_no_show',
    'host_unresponsive',
    'dispute_opened',
    'frozen_pending_dispute_resolution',
    'requires_support_intervention',
]
```

Expected: test documents duplicates currently present in `BookingStatus`.

- [ ] **Step 3: Normalize without destructive data loss**

Keep compatibility for older enum values that already exist in the database, but add methods that return canonical user-facing values:

```php
public function canonicalValue(): string
{
    return match ($this) {
        self::WaitingGuestResponse, self::PendingGuestResponse => 'awaiting_guest_response',
        self::WaitingPayment, self::PendingPayment => 'awaiting_payment',
        self::ReadyForCheckInCore => 'ready_for_check_in',
        self::FrozenUntilDisputeResolved => 'frozen_pending_dispute_resolution',
        self::FutureSupportRequired, self::NeedsSupportIntervention => 'requires_support_intervention',
        default => $this->value,
    };
}
```

Expected: code can display canonical labels while old rows remain readable.

- [ ] **Step 4: Add separate classification enums**

Create/extend enums for:

```text
primary flow: instant_booking, host_confirmation_booking, stay_request, preliminary_inquiry, long_term_request, urgent_today_booking
payment/deposit mode: awaiting_payment, with_deposit, without_deposit, partial_payment, full_payment
modifiers: extension, relocation, group_booking, two_guest_sleeping_place
```

Expected: booking flow, payment state, deposit mode, and modifiers are no longer collapsed into one lifecycle status.

- [ ] **Step 5: Run tests**

Run:

```bash
php artisan test --compact tests/Unit/DomainStatusContractTest.php tests/Feature/BookingCoreLifecycleFeatureTest.php
```

Expected: all pass.

## Task 3: Date Selection DTO And Validation

**Files:**
- Create: `app/Data/Bookings/BookingDateSelectionData.php`
- Modify: `app/Services/Bookings/StayLengthCalculatorService.php`
- Modify: `app/Services/Bookings/BookingDateValidationService.php`
- Modify: `app/Services/Bookings/BookingDateSelectionService.php`
- Modify: `lang/en/booking_dates.php`
- Modify: `lang/ru/booking_dates.php`
- Test: `tests/Unit/StayLengthCalculatorServiceTest.php`
- Test: `tests/Feature/BookingDateSelectorTest.php`

- [ ] **Step 1: Test half-open date math**

Assert July 10 to July 13 gives:

```php
nights_count = 3;
stay_days_count = 3;
calendar_presence_days_count = 4;
```

Expected: current service should already mostly pass; lock it as contract.

- [ ] **Step 2: Add a compact date selection data object**

Include only scalar and safe fields:

```php
public function __construct(
    public readonly string $checkInDate,
    public readonly ?string $checkInTime,
    public readonly string $checkOutDate,
    public readonly ?string $checkOutTime,
    public readonly int $nightsCount,
    public readonly int $stayDaysCount,
    public readonly int $calendarPresenceDaysCount,
    public readonly bool $earlyCheckInRequested,
    public readonly bool $lateCheckOutRequested,
    public readonly bool $flexibleCheckIn,
    public readonly bool $flexibleCheckOut,
    public readonly bool $requiresHostTimeApproval,
    public readonly ?string $checkInComment,
    public readonly ?string $checkOutComment,
) {}
```

Expected: Livewire components can consume compact DTO data instead of calculating in Blade.

- [ ] **Step 3: Expand validation keys**

Cover:

```text
checkout_before_checkin
checkout_same_day_not_allowed
sleeping_place_occupied
sleeping_place_held
property_closed_by_host
room_repair
cleaning_gap_required
below_min_nights
above_max_nights
check_in_weekday_not_allowed
check_out_weekday_not_allowed
guest_verification_required
guest_age_not_allowed
room_gender_policy_mismatch
guests_count_too_high
```

Expected: each blocking rule returns a `message_key`, not hard-coded text.

- [ ] **Step 4: Run tests**

Run:

```bash
php artisan test --compact tests/Unit/StayLengthCalculatorServiceTest.php tests/Feature/BookingDateSelectorTest.php tests/Feature/BookingDatesAndQuotesFeatureTest.php
```

Expected: all pass.

## Task 4: Turnover Rules Contract

**Files:**
- Modify: `app/Models/SleepingPlaceTurnoverRule.php`
- Modify if needed: `database/migrations/2026_06_21_020000_create_sleeping_place_calendar_tables.php` only if not production-applied locally
- Prefer new migration if adding columns: `database/migrations/YYYY_MM_DD_HHMMSS_align_sleeping_place_turnover_rule_contract.php`
- Modify: `app/Services/Availability/SleepingPlaceTurnoverService.php`
- Modify: `resources/views/livewire/host/availability/host-turnover-rules-form.blade.php`
- Modify: `lang/en/availability.php`
- Modify: `lang/ru/availability.php`
- Test: `tests/Unit/SleepingPlaceTurnoverServiceTest.php`
- Test: `tests/Feature/HostCalendarFeatureTest.php`

- [ ] **Step 1: Decide alias strategy from schema**

Current physical fields include `min_gap_minutes`, `cleaning_gap_minutes`, `same_day_turnover_allowed`, `earliest_new_check_in_time`, `latest_previous_check_out_time`. The product language asks for `minimum_turnover_minutes`, `cleaning_duration_minutes`, `same_day_check_in_allowed`, `morning_checkout_evening_checkin_allowed`, `earliest_next_check_in_time`, `latest_previous_check_out_time`.

Expected: choose one of these and document in code comments:

```text
Option A: keep physical columns and expose domain accessors.
Option B: add canonical columns, backfill from old columns, keep old aliases during transition.
```

- [ ] **Step 2: Test same-day boundary**

Given previous booking July 10-15 with checkout 11:00, required gap 240 minutes, next check-in 15:00:

```php
allowed = true
```

Given next check-in 14:00:

```php
allowed = false
reason_key = same_day_turnover_not_allowed
```

- [ ] **Step 3: Add morning checkout/evening check-in rule**

If `morning_checkout_evening_checkin_allowed` is false, same-day turnover must fail even when enough minutes exist.

Expected: hosts can explicitly disable the morning/evening pattern.

- [ ] **Step 4: Wire host form**

Use Flux inputs/switches already documented in `docs/flux-ui-components.md`; bindings:

```blade
wire:model.change="form.cleaning_required_between_guests"
wire:model.change="form.inspection_required_after_checkout"
wire:model.change="form.same_day_check_in_allowed"
wire:model.change="form.morning_checkout_evening_checkin_allowed"
wire:model.blur="form.minimum_turnover_minutes"
wire:model.blur="form.cleaning_duration_minutes"
```

Expected: no `@php`, no raw strings, translated labels.

- [ ] **Step 5: Run tests**

Run:

```bash
php artisan test --compact tests/Unit/SleepingPlaceTurnoverServiceTest.php tests/Feature/HostCalendarFeatureTest.php
```

Expected: all pass.

## Task 5: Availability And Double Booking Protection

**Files:**
- Modify: `app/Services/Availability/AvailabilityService.php`
- Modify: `app/Services/Availability/SleepingPlaceDateLockService.php`
- Modify: `app/Services/Bookings/BookingQuoteAvailabilityService.php`
- Modify: `app/Services/Bookings/BookingCreationService.php`
- Modify: `app/Services/BookingRequests/BookingRequestAvailabilityHoldService.php`
- Modify: `app/Models/AvailabilityDay.php`
- Modify: `app/Models/SleepingPlaceCalendarDay.php`
- Test: `tests/Unit/SleepingPlaceAvailabilityServiceTest.php`
- Test: `tests/Feature/SleepingPlaceAvailabilityCalendarTest.php`
- Test: `tests/Feature/AvailabilityCalendarFlowTest.php`

- [ ] **Step 1: Test overlap rule**

For existing `[2026-07-10, 2026-07-15)` reject:

```text
2026-07-09 -> 2026-07-11
2026-07-10 -> 2026-07-12
2026-07-14 -> 2026-07-16
2026-07-11 -> 2026-07-15
```

Allow:

```text
2026-07-08 -> 2026-07-10
2026-07-15 -> 2026-07-18 when turnover allows it
2026-07-16 -> 2026-07-18
```

- [ ] **Step 2: Ensure Eloquent overlap query uses half-open comparison**

The query shape must be:

```php
->where('check_in_date', '<', $requestedCheckOut)
->where('check_out_date', '>', $requestedCheckIn)
```

Expected: checkout day is reusable only when turnover allows the boundary.

- [ ] **Step 3: Recheck inside write transactions**

Before creating booking, booking request hold, extension hold, or relocation hold:

```text
load place with required relations
validate date range
check availability and locks
create locks/calendar rows
persist booking/request/extension/relocation
```

Expected: two users cannot reserve the same sleeping place for overlapping dates.

- [ ] **Step 4: Add index checks**

Confirm indexes exist for:

```text
availability_days: sleeping_place_id + date
availability_days: sleeping_place_id + status + date
sleeping_place_booking_date_locks: sleeping_place_id + date + status
bookings: sleeping_place_id + check_in_date + check_out_date + status
```

Expected: add a new migration only for missing indexes.

- [ ] **Step 5: Run tests**

Run:

```bash
php artisan test --compact tests/Unit/SleepingPlaceAvailabilityServiceTest.php tests/Feature/SleepingPlaceAvailabilityCalendarTest.php tests/Feature/AvailabilityCalendarFlowTest.php tests/Feature/BookingRequestsFeatureTest.php
```

Expected: all pass.

## Task 6: Smart Guest Calendar Behavior

**Files:**
- Modify: `app/Services/Bookings/BookingDateSelectionService.php`
- Modify: `app/Services/Availability/SleepingPlaceAvailabilitySuggestionService.php`
- Modify: `app/Services/Bookings/BookingQuoteSuggestionService.php`
- Modify: `resources/views/livewire/bookings/dates/available-checkout-dates.blade.php`
- Modify: `resources/views/livewire/bookings/dates/date-suggestions-panel.blade.php`
- Modify: `resources/views/livewire/bookings/dates/availability-warnings.blade.php`
- Test: `tests/Feature/BookingDateSelectorTest.php`
- Test: `tests/Feature/BookingDatesAndQuotesFeatureTest.php`

- [ ] **Step 1: Test available checkout dates after check-in**

When check-in changes, assert service returns:

```text
available checkout dates
earliest checkout
latest checkout
blocking reasons for unavailable dates
nearest available ranges
similar sleeping places
same-host alternatives
neighbor room alternatives
```

- [ ] **Step 2: Keep Livewire state compact**

Public properties may contain:

```php
public ?int $sleepingPlaceId = null;
public ?string $checkInDate = null;
public ?string $checkOutDate = null;
public int $guestsCount = 1;
public ?string $promoCode = null;
```

Expected: no huge arrays of full calendars in public state.

- [ ] **Step 3: Render mobile-first panels**

Use already existing partials under `resources/views/livewire/bookings/dates/`.

Expected: unavailable dates are disabled or hidden without rendering a huge hidden DOM.

- [ ] **Step 4: Run tests**

Run:

```bash
php artisan test --compact tests/Feature/BookingDateSelectorTest.php tests/Feature/BookingDatesAndQuotesFeatureTest.php
```

Expected: all pass.

## Task 7: Pricing Engine Completion

**Files:**
- Modify: `app/Services/Pricing/BookingPriceQuoteEngine.php`
- Modify: `app/Services/Pricing/NightlyPriceLineService.php`
- Modify: `app/Services/Pricing/DiscountCalculatorService.php`
- Modify: `app/Services/Pricing/FeeCalculatorService.php`
- Modify: `app/Services/Pricing/ServiceFeeCalculatorService.php`
- Modify: `app/Services/Pricing/TaxCalculatorService.php`
- Modify: `app/Services/Pricing/DepositCalculatorService.php`
- Modify: `app/Services/Pricing/HostPayoutCalculatorService.php`
- Modify: `app/Services/Pricing/RefundabilityCalculatorService.php`
- Modify: `app/Models/BookingQuoteLine.php`
- Modify: `lang/en/pricing.php`
- Modify: `lang/ru/pricing.php`
- Test: `tests/Unit/PricingServiceTest.php`
- Test: `tests/Unit/BookingPriceCalculatorTest.php`
- Test: `tests/Feature/PricingEngineFeatureTest.php`

- [ ] **Step 1: Test nightly line priority**

Expected priority:

```text
date-specific price
holiday price
weekend price
weekday/base price
```

Assert three selected dates produce visible line items before discounts.

- [ ] **Step 2: Test discount thresholds**

Cases:

```text
6 nights -> no weekly threshold
7 nights -> weekly discount can apply
30 nights -> monthly discount can apply
new guest -> new guest discount can apply
last minute -> last-minute discount can apply
promo code -> promo discount recalculates total
```

- [ ] **Step 3: Test fees**

Cases:

```text
early check-in fee
late checkout fee
extra guest fee for second guest on double place
cleaning fee
deposit payable now
service fee
tax or city fee when configured
```

- [ ] **Step 4: Persist quote lines in display order**

Line order:

```text
nightly lines
discount lines
time/extra guest fee lines
cleaning fee
service fee
tax/city fee
deposit
total summary fields on quote
```

- [ ] **Step 5: Validate example**

For July 10, 11, 12:

```text
20 + 20 + 25 = 65
discount = 5
cleaning = 10
deposit = 50
service fee = 6
total payable now = 126
refundable amount includes 50 deposit
```

- [ ] **Step 6: Run tests**

Run:

```bash
php artisan test --compact tests/Unit/PricingServiceTest.php tests/Unit/BookingPriceCalculatorTest.php tests/Feature/PricingEngineFeatureTest.php tests/Feature/BookingDatesAndQuotesFeatureTest.php
```

Expected: all pass.

## Task 8: Guest Price Display

**Files:**
- Modify: `resources/views/livewire/bookings/pricing/nightly-price-list.blade.php`
- Modify: `resources/views/livewire/bookings/pricing/price-breakdown.blade.php`
- Modify: `resources/views/livewire/bookings/pricing/price-quote-panel.blade.php`
- Modify: `resources/views/livewire/bookings/quotes/booking-quote-line-breakdown.blade.php`
- Modify: `resources/views/livewire/bookings/quotes/booking-quote-summary.blade.php`
- Modify: `lang/en/booking_quotes.php`
- Modify: `lang/ru/booking_quotes.php`
- Test: `tests/Feature/BookingDatesAndQuotesFeatureTest.php`

- [ ] **Step 1: Render required guest order**

Show:

```text
daily/nightly lines
stay days count
accommodation amount
discount amount
cleaning fee
deposit
service fee
total payable now
refundable deposit note
```

- [ ] **Step 2: Remove Blade calculations**

Blade may read prepared numbers and translation keys only.

Expected: no `@php`, no model queries, no relationship calls inside loops.

- [ ] **Step 3: Add loading state**

Use Livewire `data-loading` or scoped `wire:loading` with translated labels.

Expected: date and promo-code changes show calm slow-3G feedback.

- [ ] **Step 4: Run tests**

Run:

```bash
php artisan test --compact tests/Feature/BookingDatesAndQuotesFeatureTest.php tests/Feature/BladeNoPhpDirectiveTest.php
```

Expected: all pass.

## Task 9: Booking Request Creation And Host Card

**Files:**
- Modify: `app/Services/BookingRequests/BookingRequestCreationService.php`
- Modify: `app/Services/BookingRequests/BookingRequestHostViewService.php`
- Modify: `app/Services/BookingRequests/BookingRequestPrivacyService.php`
- Modify: `app/Services/BookingRequests/BookingRequestWarningService.php`
- Modify: `resources/views/livewire/bookings/requests/booking-request-form.blade.php`
- Modify: `resources/views/livewire/host/booking-requests/host-booking-request-card.blade.php`
- Modify: `resources/views/livewire/host/booking-requests/host-guest-profile-preview.blade.php`
- Modify: `resources/views/livewire/host/booking-requests/host-request-compatibility-panel.blade.php`
- Modify: `resources/views/livewire/host/booking-requests/host-request-warnings-panel.blade.php`
- Modify: `lang/en/booking_requests.php`
- Modify: `lang/ru/booking_requests.php`
- Test: `tests/Feature/BookingRequestsFeatureTest.php`
- Test: `tests/Feature/HostBookingRequestManagementTest.php`

- [ ] **Step 1: Test request fields**

Assert persisted request has:

```text
guest, host, sleeping place, check-in, check-out, stay days, guests count,
travel purpose, planned arrival time, message, baggage flags,
early/late flags, registration/reporting document flags,
status, expiration
```

- [ ] **Step 2: Test host privacy-safe summary**

Host sees:

```text
guest name, photo, age/age range, city, languages, rating, past stays,
review count, identity/phone/email verification status, complaint signal,
dates, stay days, total, purpose, message, compatibility, warnings
```

Expected: no private addresses, payment payloads, document paths, internal notes.

- [ ] **Step 3: Test warning levels**

Warnings:

```text
late_night_arrival
very_early_checkout
identity_not_verified
no_reviews
guest_had_cancellations
last_minute_request
above_max_stay
cleaning_gap_conflict
guest_has_confirmed_complaints
smoking_conflict
pet_conflict
too_many_guests
```

Expected: each has severity, blocking flag, message key, host visibility.

- [ ] **Step 4: Run tests**

Run:

```bash
php artisan test --compact tests/Feature/BookingRequestsFeatureTest.php tests/Feature/HostBookingRequestManagementTest.php
```

Expected: all pass.

## Task 10: Booking Creation And Quote Conversion

**Files:**
- Modify: `app/Services/Bookings/BookingQuoteConversionService.php`
- Modify: `app/Services/Bookings/BookingCreationService.php`
- Modify: `app/Services/Bookings/BookingCalendarIntegrationService.php`
- Modify: `app/Services/Bookings/BookingStatusService.php`
- Modify: `app/Services/Bookings/BookingTimelineDateService.php`
- Modify: `app/Services/Bookings/BookingSnapshotService.php`
- Modify: `app/Models/Booking.php`
- Modify: `app/Models/BookingPriceLine.php`
- Test: `tests/Feature/BookingCoreLifecycleFeatureTest.php`
- Test: `tests/Feature/QuoteToBookingConversionFeatureTest.php`

- [ ] **Step 1: Test quote to booking field copy**

Quote conversion must persist:

```text
booking number, status, guest, host, property, room, sleeping_place,
check-in/out dates and times, stay days, calendar days, guest count,
price per stay day, period price, discount, deposit, cleaning fee,
service fee, total, currency, payment status, payment deadline,
verification requirements, cancellation fields, timeline dates
```

- [ ] **Step 2: Recheck availability inside transaction**

Expected sequence:

```text
DB transaction starts
quote is reloaded with place and guest
availability is rechecked
booking row is created
price lines are copied to booking_price_lines
calendar/locks are written
status history is written
notifications are queued
transaction commits
```

- [ ] **Step 3: Keep payment status separate**

Lifecycle status may be `awaiting_payment`, `confirmed`, or `paid`, but `payment_status` remains the money state.

- [ ] **Step 4: Run tests**

Run:

```bash
php artisan test --compact tests/Feature/BookingCoreLifecycleFeatureTest.php tests/Feature/BookingPaymentsFeatureTest.php tests/Feature/BookingDatesAndQuotesFeatureTest.php
```

Expected: all pass.

## Task 11: Stay Extension Flow

**Files:**
- Modify: `app/Services/Bookings/BookingExtensionService.php`
- Modify: `app/Services/Bookings/BookingExtensionAvailabilityService.php`
- Modify: `app/Services/Bookings/BookingExtensionPriceService.php`
- Modify: `app/Services/Bookings/BookingExtensionApplyService.php`
- Modify: `app/Services/Bookings/BookingExtensionHoldService.php`
- Modify: `app/Services/Bookings/BookingExtensionNotificationService.php`
- Modify: `app/Livewire/Extensions/ExtendStay.php`
- Modify: `app/Livewire/Extensions/ManageExtension.php`
- Modify: `resources/views/livewire/extensions/extend-stay.blade.php`
- Modify: `resources/views/livewire/extensions/manage-extension.blade.php`
- Modify: `lang/en/booking_extensions.php`
- Modify: `lang/ru/booking_extensions.php`
- Test: `tests/Feature/BookingExtensionFlowPointThirteenTest.php`
- Test: `tests/Feature/BookingExtensionFlowTest.php`

- [ ] **Step 1: Test added range availability**

For current checkout July 15 and new checkout July 18, validate `[July 15, July 18)` only.

- [ ] **Step 2: Test another guest conflict**

If another booking or hold exists inside the added range, extension is rejected with translated reason key.

- [ ] **Step 3: Test additional price quote**

Persist extension lines:

```text
additional nightly lines
extension discount
fees if any
amount due
currency
```

- [ ] **Step 4: Test apply after approval/payment**

Expected updates:

```text
original booking checkout date/time
stay days and totals
booking status history
extension status
availability rows/date locks for added dates
guest and host notifications
```

- [ ] **Step 5: Run tests**

Run:

```bash
php artisan test --compact tests/Feature/BookingExtensionFlowPointThirteenTest.php tests/Feature/BookingExtensionFlowTest.php
```

Expected: all pass.

## Task 12: Stay Relocation Flow

**Files:**
- Modify: `app/Services/Bookings/BookingRelocationService.php`
- Modify: `app/Services/Bookings/BookingRelocationAvailabilityService.php`
- Modify: `app/Services/Bookings/BookingRelocationPriceService.php`
- Modify: `app/Services/Bookings/BookingRelocationConsentService.php`
- Modify: `app/Services/Bookings/BookingRelocationApplyService.php`
- Modify: `app/Services/Bookings/BookingRelocationHoldService.php`
- Modify: `app/Services/Bookings/BookingRelocationCalendarService.php`
- Modify: `app/Services/Bookings/BookingRelocationNotificationService.php`
- Modify: `resources/views/livewire/bookings/relocations/card.blade.php`
- Modify: `resources/views/livewire/host/relocations/card.blade.php`
- Modify: `lang/en/booking_relocations.php`
- Modify: `lang/ru/booking_relocations.php`
- Test: `tests/Feature/BookingRelocationFlowPointFourteenTest.php`

- [ ] **Step 1: Test relocation reasons**

Required reasons:

```text
noisy_neighbors
uncomfortable_bed
resident_conflict
broken_item
host_offered_other_place
wants_more_private_room
wants_cheaper_place
wants_more_comfort
```

- [ ] **Step 2: Test new place availability**

Validate new sleeping place for `[relocation_date, booking.check_out_date)`.

- [ ] **Step 3: Test old/new calendar handling**

Before approval:

```text
old place stays blocked through original booking
new place is held, not fully applied
```

After approval/application:

```text
old place remains blocked before relocation date
new place is blocked from relocation date to checkout
booking audit trail records relocation
price lines and refund/credit/deposit implications are persisted
```

- [ ] **Step 4: Test consent rules**

Host-offered relocation requires guest consent. Guest-requested relocation requires host consent unless rules explicitly allow self-service relocation.

- [ ] **Step 5: Run tests**

Run:

```bash
php artisan test --compact tests/Feature/BookingRelocationFlowPointFourteenTest.php
```

Expected: all pass.

## Task 13: Mobile Livewire UX Integration

**Files:**
- Modify: `app/Livewire/Bookings/Create/BookingCreatePage.php`
- Modify: `resources/views/livewire/bookings/create/booking-create-page.blade.php`
- Modify: `resources/views/livewire/bookings/create/booking-summary-step.blade.php`
- Modify: `resources/views/livewire/bookings/create/booking-payment-step.blade.php`
- Modify: `resources/views/livewire/bookings/dates/date-selection-panel.blade.php`
- Modify: `resources/views/livewire/bookings/dates/time-preference-sheet.blade.php`
- Modify: `resources/views/livewire/bookings/quotes/booking-quote-panel.blade.php`
- Modify: `resources/views/livewire/host/booking-requests/host-booking-requests-page.blade.php`
- Test: `tests/Feature/MobileBookingFlowTest.php`
- Test: `tests/Feature/FluxProComponentUsageTest.php`
- Test: `tests/Feature/BladeNoPhpDirectiveTest.php`

- [ ] **Step 1: Use correct Livewire bindings**

Bindings:

```blade
wire:model.change for dates, selects, checkboxes, radios, switches
wire:model.blur for text fields and time comments
wire:model.enter or live debounce for compact search only
```

- [ ] **Step 2: Keep first screen small**

First booking screen shows:

```text
date selection
guest count
availability/price summary
primary next action
```

Move advanced time comments and optional request details to bottom sheets/steps.

- [ ] **Step 3: Add friendly loading and empty states**

Every network action has translated loading feedback. Empty suggestions explain the next action.

- [ ] **Step 4: Run tests**

Run:

```bash
php artisan test --compact tests/Feature/MobileBookingFlowTest.php tests/Feature/FluxProComponentUsageTest.php tests/Feature/BladeNoPhpDirectiveTest.php
```

Expected: all pass.

## Task 14: Translations And Copy Contract

**Files:**
- Modify: `lang/en/availability.php`
- Modify: `lang/ru/availability.php`
- Modify: `lang/en/booking_dates.php`
- Modify: `lang/ru/booking_dates.php`
- Modify: `lang/en/booking_quotes.php`
- Modify: `lang/ru/booking_quotes.php`
- Modify: `lang/en/booking_requests.php`
- Modify: `lang/ru/booking_requests.php`
- Modify: `lang/en/booking_extensions.php`
- Modify: `lang/ru/booking_extensions.php`
- Modify: `lang/en/booking_relocations.php`
- Modify: `lang/ru/booking_relocations.php`
- Modify: `lang/en/statuses.php`
- Modify: `lang/ru/statuses.php`
- Test: `tests/Feature/LocalizationCatalogueTest.php`

- [ ] **Step 1: Add every status, reason, warning, and line label**

Translation groups:

```text
availability.statuses.*
availability.messages.*
booking_dates.validation.*
booking_quotes.lines.*
booking_requests.warnings.*
booking_extensions.statuses.*
booking_relocations.reasons.*
statuses.booking.*
statuses.availability.*
```

- [ ] **Step 2: Prohibit hard-coded visible strings**

Scan modified Blade and Livewire classes for visible copy.

Expected: visible copy uses translation keys.

- [ ] **Step 3: Run tests**

Run:

```bash
php artisan test --compact tests/Feature/LocalizationCatalogueTest.php
```

Expected: all pass.

## Task 15: Factories, Seeders, And Demo Coverage

**Files:**
- Modify only if needed: `database/factories/BookingFactory.php`
- Modify only if needed: `database/factories/BookingQuoteFactory.php`
- Modify only if needed: `database/factories/BookingRequestFactory.php`
- Modify only if needed: `database/factories/BookingExtensionFactory.php`
- Modify only if needed: `database/factories/BookingRelocationFactory.php`
- Modify only if needed: `database/factories/SleepingPlaceFactory.php`
- Modify only if needed: `database/factories/SleepingPlaceTurnoverRuleFactory.php`
- Modify carefully: `database/seeders/BulkMarketplaceSeeder.php`
- Test: `tests/Feature/DemoSeederTest.php`
- Test: `tests/Feature/FullIntegrationPassTest.php`

- [ ] **Step 1: Preserve existing dirty seeder/factory changes**

Before editing:

```bash
git diff -- database/factories/BookingStayFactory.php database/seeders/BulkMarketplaceSeeder.php
```

Expected: understand user changes and avoid overwriting unrelated work.

- [ ] **Step 2: Add realistic factory states**

States:

```text
instant booking place
request-only place
double sleeping place
female-only room
male-only room
turnover required
date-specific price
weekly/monthly discount
extension eligible booking
relocation eligible booking
```

- [ ] **Step 3: Keep demo data lightweight**

Expected: `DatabaseSeeder` stays fast; full GeoNames remains manual.

- [ ] **Step 4: Run tests**

Run:

```bash
php artisan test --compact tests/Feature/DemoSeederTest.php tests/Feature/FullIntegrationPassTest.php
```

Expected: all pass.

## Task 16: Performance And Query Audit

**Files:**
- Modify migrations only for missing indexes.
- Modify query services if N+1 is found.
- Test: `tests/Feature/MobilePerformanceBudgetTest.php`
- Test: `tests/Feature/Queries/VisibleListingCardsQueryTest.php`
- Test: `tests/Feature/Queries/RoomOccupantsForDateRangeQueryTest.php`

- [ ] **Step 1: Inspect schema indexes with Boost**

Use:

```text
database_schema(filter: "availability", include_column_details: true)
database_schema(filter: "booking", include_column_details: true)
database_schema(filter: "sleeping_place", include_column_details: true)
```

- [ ] **Step 2: Add indexes only where missing**

Critical patterns:

```text
sleeping_place_id + date
sleeping_place_id + status + date
sleeping_place_id + check_in_date + check_out_date + status
guest_user_id + status + check_in_date
host_user_id + status + check_in_date
booking_id + status
locale + translatable_id
```

- [ ] **Step 3: Prevent UI query regressions**

Check Livewire render methods and computed properties:

```text
no Model::all()
no aggregates inside loops
no relationship loading from Blade
no full calendars in public properties
cursor/simple pagination for lists
```

- [ ] **Step 4: Run tests**

Run:

```bash
php artisan test --compact tests/Feature/MobilePerformanceBudgetTest.php tests/Feature/Queries/VisibleListingCardsQueryTest.php tests/Feature/Queries/RoomOccupantsForDateRangeQueryTest.php
```

Expected: all pass.

## Task 17: Final Verification

**Files:**
- All modified implementation files.

- [ ] **Step 1: Run focused domain tests**

Run:

```bash
php artisan test --compact tests/Unit/StayLengthCalculatorServiceTest.php tests/Unit/PricingServiceTest.php tests/Unit/BookingPriceCalculatorTest.php tests/Unit/SleepingPlaceAvailabilityServiceTest.php tests/Feature/BookingDatesAndQuotesFeatureTest.php tests/Feature/BookingRequestsFeatureTest.php tests/Feature/BookingExtensionFlowPointThirteenTest.php tests/Feature/BookingRelocationFlowPointFourteenTest.php
```

Expected: all pass.

- [ ] **Step 2: Run architecture and localization tests**

Run:

```bash
php artisan test --compact tests/Feature/FoundationPointOneArchitectureTest.php tests/Feature/RootWebDirectoryArchitectureTest.php tests/Feature/BladeNoPhpDirectiveTest.php tests/Feature/LocalizationCatalogueTest.php tests/Feature/FluxProComponentUsageTest.php
```

Expected: all pass.

- [ ] **Step 3: Run project checks**

Run:

```bash
php artisan test
./vendor/bin/pint
npm run build
```

Expected: all pass before commit.

- [ ] **Step 4: Review scoped diff**

Run:

```bash
git diff --stat
git diff --check
```

Expected: no whitespace errors, no unrelated file churn, no legacy controller/Filament/Volt/Inertia surfaces.

## Suggested Milestone Order

1. Contracts and statuses.
2. Date math and validation.
3. Turnover and double-booking protection.
4. Pricing quote completeness.
5. Guest booking UI display.
6. Booking request host review and warnings.
7. Quote-to-booking conversion.
8. Extension flow.
9. Relocation flow.
10. Mobile/performance/i18n hardening.

## Global Constraints

- Laravel 13, PHP 8.3+, Livewire 4, Flux Pro, SQLite.
- Do not use Livewire Volt.
- Do not use Filament.
- Do not use Inertia.
- Do not create admin/staff/moderation/support/finance panels.
- Mobile-first, 320px first.
- Must work well on old Android and slow 3G.
- Keep Livewire public properties small.
- Use translations for every visible string.
- Support en and ru from day one.
- Prepare architecture for future languages.
- Add migrations, models, factories, seeders where needed.
- Add Livewire feature tests and unit tests where needed.
- Add indexes for all filtering/search/calendar queries.
- Run php artisan test, Pint, and npm build.
- Update docs when behavior or schema changes.

## Execution Options

1. Subagent-driven execution: one fresh implementation agent per task, with review after each task.
2. Inline execution: execute tasks in this session in small batches with checkpoints after each milestone.
