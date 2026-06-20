# Automatic Host Hints

## Purpose

Automatic Host Hints help hosts complete properties, rooms, and sleeping places without remembering every detail manually. The module analyzes listing data and shows friendly, non-aggressive suggestions such as missing sleeping place photos, missing check-in time, missing kitchen rules, missing deposit, calendar gaps, and safety details.

Hints are educational and operational. They must not create fake urgency, shame the host, or claim statistics unless the data exists.

## Hint Categories

- `photos`: sleeping place, room, bathroom, kitchen, and main photo checks.
- `description`: short summary, full description, and important notes.
- `sleeping_place`: locker and sleeping place-specific details.
- `room`: room rules and current occupant count.
- `pricing`: price position, deposit, cleaning fee, discounts, cancellation policy.
- `rules`: house, kitchen, bathroom, quiet, smoking, pet, and lost key rules.
- `calendar`: open dates, next 30 days, minimum and maximum stay.
- `access`: check-in, check-out, key pickup, self check-in, night entry.
- `safety`: emergency contact, lock, and problem instructions.

## Table Structure

### `host_hint_snapshots`

Stores calculated hints for a host and optional property, room, or sleeping place target.

Important columns:

- `user_id`
- `property_id`
- `room_id`
- `sleeping_place_id`
- `hint_key`
- `category`
- `type`
- `importance`
- `priority`
- `message_key`
- `message_params_json`
- `action_key`
- `status`
- `show_in_wizard`
- `show_in_dashboard`
- `show_before_publish`
- `show_on_listing_card`
- `calculated_at`
- `expires_at`

Indexes cover host/status dashboards, target/status lookups, category, priority, expiration, and display surfaces.

### `host_hint_dismissals`

Stores hidden or postponed hints per host and target. Critical before-publish blockers are never permanently hidden on the before-publish surface.

### `host_hint_actions`

Stores host actions such as marking a hint as completed.

## Services

- `HostHintService`: orchestrates refresh, dashboard, wizard, before-publish, completion, and dismissal flows.
- `HostListingQualityService`: calculates completion score, missing required fields, recommended fields, critical issues, and publish readiness.
- `HostPhotoHintService`: detects missing photo coverage.
- `HostDescriptionHintService`: detects missing listing text.
- `HostPricingHintService`: detects price position and missing pricing policies.
- `HostRulesHintService`: detects missing rules.
- `HostCalendarHintService`: detects missing or weak availability setup.
- `HostSafetyHintService`: detects missing safety and room clarity.
- `HostAccessHintService`: detects missing check-in, checkout, and key details.
- `HostHintPriorityService`: sorts, groups, and deduplicates hints.
- `HostHintDismissalService`: hides optional hints, reminds later, and restores expired dismissals.

## Wizard Placement

`HostWizardHints` receives a target type, target ID, and wizard step. It shows only hints relevant to the current step, for example:

- photo step: photo hints
- rules step: rules and room hints
- price step: pricing hints
- access step: check-in, checkout, keys, and night entry

## Dashboard Placement

`HostHintsPanel` is rendered lazily on the host dashboard and groups active hints by category. It is meant for quick improvements and important listing gaps.

## Before Publish Checklist

`HostBeforePublishChecklist` shows required issues first and recommended improvements second. Critical publishing blockers stay visible even if the host previously postponed the same hint elsewhere.

## Completion Score Logic

`HostListingQualityService` calculates a percentage from required and recommended checks. For sleeping places the current checks include:

- title
- photos
- price
- calendar
- access times
- rules
- deposit
- cleaning fee
- weekly discount
- monthly discount
- cancellation policy
- locker information

Critical checks currently include photos, price, and calendar.

## Mobile UX

The UI uses compact cards, accordions, short labels, and lazy rendering. Hints are grouped and progressively disclosed instead of shown as large tables.

## Translation Keys

Visible text lives in:

- `lang/en/host_hints.php`
- `lang/ru/host_hints.php`

Blade and Livewire components use translation keys only.

## Tests

Covered by `tests/Feature/AutomaticHostHintsFeatureTest.php`:

- tables, indexes, models, factories, and cascade behavior
- hint refresh for missing listing details
- missing photos, rules, locker info, occupant count, pricing, access, safety, and calendar hints
- quality score and publish readiness
- completed hint actions
- optional dismissal
- critical before-publish protection
- Livewire dashboard, wizard, before-publish, quality, and dismissal components
- English and Russian rendering
