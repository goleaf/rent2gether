# Guest Intake Before Booking

## Purpose

Guest intake is the short questionnaire shown before a booking request or instant booking. It helps the guest explain practical arrival and stay needs without turning booking into an interrogation.

The host receives a privacy-safe summary with:

- safe trip purpose
- planned arrival and departure timing
- early check-in or late checkout requests
- baggage, pet, and smoking notes
- quiet, workspace, Wi-Fi, socket, late entry, and self check-in needs
- registration or document request flags
- special requests and the guest's message
- warning and confirmation reasons

## Fields

`booking_guest_intakes` stores one draft or completed intake per guest and sleeping place context. It belongs to `users`, optional `bookings`, `properties`, `rooms`, and `sleeping_places`.

Main groups:

- trip purpose and purpose visibility
- arrival and departure time fields
- baggage, luggage storage, pet, and smoking fields
- quiet, workspace, Wi-Fi, power, online call, late entry, and self check-in needs
- registration, work documents, invoice, receipt, contract, company name, and document notes
- special requests, host message, generated host message, rules acceptance, compatibility status, warnings, and blocking reasons

## Database Structure

The table is intentionally separate from `bookings` so the guest can save a draft step-by-step before a payable booking or host request exists.

Important indexes:

- `user_id + status` for active draft lookup
- `booking_id` for confirmed booking summary
- `property_id`, `room_id`, and `sleeping_place_id` for host and listing context
- trip-purpose and need flags used by warnings and host screens
- `compatibility_status` for quick attention filters

Deleting a user, property, room, or sleeping place cascades the intake. Deleting a booking nulls `booking_id` so audit-safe intake history can remain tied to the guest/listing context.

## Services

- `BookingGuestIntakeService` creates drafts, updates step data, completes the intake, attaches it to a booking, and deletes drafts owned by the guest.
- `BookingGuestIntakeValidationService` detects conflicts with property, room, and sleeping-place rules.
- `BookingGuestIntakeCompatibilityService` converts warnings and blocking reasons into a compact status and score.
- `BookingGuestIntakePrivacyService` filters host-visible fields and hides sensitive trip purposes by default.
- `BookingGuestIntakeSummaryService` builds guest review and host summaries.
- `BookingGuestIntakeMessageService` creates a safe host message and message templates.

## Privacy Rules

Medical or treatment purpose is sensitive. If `trip_purpose = medical` and `trip_purpose_visibility = safe`, the host sees `private trip` instead of the exact purpose.

Host summaries must not expose private notes or detailed document content. They show only that documents are requested and what the host needs to confirm.

The guest review summary shows what will be sent to the host before completing the questionnaire.

## Mobile Wizard

The Livewire wizard is class-based and renders one step at a time:

1. Trip purpose
2. Arrival and departure
3. Baggage, pets, smoking
4. Comfort and work
5. Documents and special requests
6. Message to host

The wizard uses `wire:model.change` for toggles/selects/time fields and `wire:model.blur` for text and textarea fields. It saves a draft after each step and keeps public Livewire state to small scalar values only.

## Host Summary

`HostIntakeSummary` renders the privacy-safe host view. It includes the safe trip purpose, timings, baggage, practical needs, message, warnings, and required confirmations.

Hosts should use this summary inside request/booking review screens instead of reading raw intake columns in Blade.

## Validation And Warnings

Warnings currently cover:

- early check-in or late checkout not clearly allowed
- smoking mismatch
- quiet need with noisy room/property
- missing workspace, fast Wi-Fi, socket, or luggage support
- document requests that need confirmation
- late entry or self check-in not clearly available

Blocking reasons currently cover pets when property or room rules forbid pets.

## Translations

All visible strings live in:

- `lang/en/guest_intake.php`
- `lang/ru/guest_intake.php`

Blade and Livewire components must use translation keys only.

## Tests

Covered by `tests/Feature/BookingGuestIntakeFeatureTest.php`:

- schema, indexes, relationships, scopes, and cascade delete
- draft creation and step-by-step update
- owner-only edits
- completion rules acceptance
- attaching intake to booking
- medical purpose privacy
- warning and blocking reason persistence
- safe generated host message
- Livewire wizard, guest summary, and host summary in English and Russian

Run:

```bash
php artisan test tests/Feature/BookingGuestIntakeFeatureTest.php
php artisan test
./vendor/bin/pint
npm run build
```
