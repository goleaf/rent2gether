# Listing Calendar And Publish

## Purpose

This module covers Step 4 and Step 5 of the host listing wizard:

1. Configure calendar and pricing per sleeping place.
2. Run automatic readiness checks before publication.

Guests book `SleepingPlace` records, so availability, prices, min/max stay rules, check-in/check-out rules, and cleaning gaps are stored at the sleeping-place level.

There is no moderator, staff, or admin UI. Review fields are future-ready metadata only.

## Calendar Tables

`sleeping_place_calendar_settings` stores default settings for one sleeping place:

- default status
- default price and currency
- min/max nights
- weekly and monthly discount percent
- cleaning gap hours and days
- instant booking and host approval flags
- extension and same-day turnover flags
- default check-in and check-out time windows

`sleeping_place_calendar_days` stores date-level availability and price snapshots:

- date
- status: `available`, `unavailable`, `booked`, `blocked`, `cleaning`, `repair`, `pending`, `request_only`
- optional date price
- optional min/max nights
- check-in/check-out permissions
- reason and source
- booking link for booking blocks
- host blocking flag

`sleeping_place_calendar_rules` stores recurring rules:

- weekday, weekend, or holiday prices
- blocked or available periods
- check-in/check-out weekdays
- min/max stay rules
- priority

## Publication Fields

`listing_publication_checks` stores the latest automatic readiness results for a property, room, or sleeping place.

Publication metadata is stored on `properties`, `rooms`, and `sleeping_places`:

- publication status
- review status
- review requested/reviewed timestamps
- review comment
- rejection reason
- published/paused/archived timestamps

## Calendar Logic

The calendar follows these precedence rules:

- Booking blocks are stronger than manual availability.
- Blocked dates are stronger than available dates.
- Explicit date-specific prices from `setPriceForDates` override calendar rules.
- Calendar rules override default price.
- Higher-priority rules are resolved first.
- Cleaning gap blocks dates after checkout.
- Check-in and check-out weekday rules must be respected.
- Min/max nights are stored in settings and can also be overridden by date or rule.

Quick-open date ranges can set availability and a baseline date price. Explicit date price overrides use `setPriceForDates`.

## Cleaning Gap

`CalendarCleaningGapService` calculates cleaning dates from the booking checkout date and the sleeping place settings.

When a booking is confirmed:

- booked stay dates become unavailable through `CalendarAvailabilityService`
- cleaning dates become `cleaning`
- matching legacy `availability_days` rows are also updated for compatibility

When a booking is cancelled or released:

- booking blocks return to `available`
- cleaning blocks return to `available`

## Price Rules

`CalendarPriceService` resolves one date at a time:

1. explicit date price override
2. highest-priority matching calendar rule with price
3. calendar default price
4. sleeping-place base price

Totals use `[check_in_date, check_out_date)` nights. Weekly discounts apply from 7 nights. Monthly discounts apply from 30 nights.

## Readiness Checks

Blocking checks include:

- at least one room
- at least one sleeping place
- sleeping-place price
- calendar settings
- available calendar dates
- listing photos
- house rules
- check-in time
- check-out time
- key pickup method
- deposit or explicit no-deposit state
- cancellation policy
- kitchen rules
- bathroom rules
- emergency contact

Recommended checks include:

- more sleeping-place photos
- bathroom photos
- kitchen photos
- entrance photos
- personal locker information
- quiet hours
- laundry rules
- current occupants count
- weekly discount
- monthly discount

Blocking checks prevent publication. Recommended checks stay as friendly suggestions.

## Publication Statuses

Publication statuses:

- `draft`
- `incomplete`
- `ready_to_publish`
- `pending_review`
- `published`
- `rejected`
- `paused`
- `hidden`
- `archived`

Review statuses:

- `not_required`
- `not_requested`
- `pending`
- `approved`
- `rejected`
- `auto_approved`
- `auto_rejected`

For the MVP, `publishIfReady` publishes immediately when no blocking checks remain and sets review status to `auto_approved`. `requestPublication` stores review comments and sets `pending_review` for future manual review workflows.

## Services

Calendar services:

- `HostCalendarDraftService`
- `CalendarAvailabilityService`
- `CalendarPriceService`
- `CalendarRuleService`
- `CalendarCleaningGapService`

Publication services:

- `HostListingReadinessService`
- `HostListingPublishService`
- `ListingPublicationCheckService`

All host-facing write methods verify that the authenticated host owns the property or sleeping place.

## Livewire UI

Calendar components:

- `Host/Listings/Steps/CalendarStep`
- `Host/Listings/CalendarBulkEditor`
- `Host/Listings/CalendarRulesEditor`
- `Host/Listings/PriceByDateEditor`

Publish components:

- `Host/Listings/Steps/PublishStep`
- `Host/Listings/BeforePublishChecklist`
- `Host/Listings/ListingReadinessChecklist`
- `Host/Listings/ListingDraftSaveIndicator`

The UI is mobile-first: quick date range actions, compact settings cards, no giant desktop calendar, sticky wizard navigation from the parent flow, and translated friendly validation.

## Translations

Visible strings live in:

- `lang/en/listing_calendar.php`
- `lang/ru/listing_calendar.php`
- `lang/en/listing_publish.php`
- `lang/ru/listing_publish.php`
- shared readiness strings in `lang/en/listing_wizard.php`
- shared readiness strings in `lang/ru/listing_wizard.php`

Blade and Livewire components must not hard-code visible text.

## Tests

Coverage lives in:

- `tests/Feature/ListingCalendarAndPublishFeatureTest.php`
- `tests/Feature/HostListingWizardFeatureTest.php`

Run focused checks:

```bash
php artisan test tests/Feature/ListingCalendarAndPublishFeatureTest.php
php artisan test tests/Feature/HostListingWizardFeatureTest.php
```

Before shipping, run:

```bash
php artisan test
./vendor/bin/pint --test
npm run build
```
