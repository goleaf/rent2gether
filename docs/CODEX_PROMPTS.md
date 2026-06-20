# Codex Prompts

This document stores the high-level implementation prompts and product logic for the sleeping-place marketplace.

## Big session starter prompt

Use this prompt at the beginning of a large Codex session:

```text
You are Codex working on a Laravel 13 + Livewire 4 + Flux Pro + SQLite mobile-first website.

The product is a marketplace for renting individual sleeping places inside rooms/properties.

The main unit is SleepingPlace.

The system is built around this loop:
- Guest chooses city + dates + sleeping place.
- System calculates availability + stay days + nights + calendar days + price + discount + deposit + cleaning fee + service fee + total due + cancellation deadlines + host payout timing + reminders + rules + compatibility.
- Host controls property + rooms + sleeping places + calendar + price + rules + requests.

Everything must be mobile-first + multilingual + fast + friendly + Livewire-native.
This frame prevents drift into Filament, Volt, admin panels, desktop-first UI, or heavy SPA architecture.

Allowed user roles:
- Guest
- Host
- Guest + Host

Do not build any admin panel.
Do not build staff tools.
Do not create moderator/support/finance/cleaner/property-manager features.
Do not use Filament.
Do not use Inertia.
Do not use Livewire Volt.
Use Livewire class components.

The system must support English and Russian from day one and allow adding more languages later.
Every visible UI string must be translated.
User-generated public listing content must support translations.

The site must be mobile-first and optimized for old Android devices and slow 3G.
Avoid heavy JS, huge DOM, huge Livewire payloads, huge selects, map-first UI, and full data loading.
Use progressive disclosure, bottom sheets, step forms, skeletons, lazy loading, debounce, caching, indexes, and compact queries.

Use SQLite carefully:
- migrations
- foreign keys
- indexes
- composite indexes
- cursor pagination
- selected columns
- no N+1
- EXPLAIN QUERY PLAN for critical queries

For countries/cities:
- use open data imports
- GeoNames for cities
- ISO-compatible country codes
- Natural Earth only for map shape data
- do not use live external API calls during normal search
- do not bulk geocode through public Nominatim

Before changing code:
1. Read AGENTS.md.
2. Read .agents/skills.
3. Inspect current code.
4. Make a short plan.
5. Implement.
6. Add tests.
7. Run tests/build/format.
8. Update docs.

Every new feature must include, when applicable:
- Migration if data is needed
- Model relationships
- Factory
- Seeder if lookup data is introduced
- Livewire class component
- Blade view
- Flux UI
- Mobile-first layout
- Translations for every supported locale
- Validation
- Friendly empty state
- Friendly loading state
- Authorization or policy if needed
- Tests
- Indexes for queries
- Docs update if behavior is important

Build production-quality code with friendly UX.
```

## Canonical prompt order

Use Codex prompts in this exact order. Treat this list as the implementation sequence for future prompt packs, not as proof that a feature is already complete.

01. Initial project audit and setup
02. Core database schema
03. Localization foundation
04. Geo data import
05. Mobile app shell
06. Authentication and account profile
07. Guest preferences and compatibility
08. Host profile and host onboarding
09. Property creation wizard
10. Room creation wizard
11. Sleeping place creation wizard
12. Amenities and rules system
13. Media uploads
14. Availability calendar
15. Date picker + nights + price calculation
16. Search and filters
17. Listing detail page
18. Favorites + saved searches + waitlist + comparison
19. Booking flow
20. Host booking requests
21. Payment records placeholder
22. My trips/current stay
23. Check-in/check-out
24. Extension of stay
25. Messages
26. Notifications
27. Reviews
28. Complaints/problem reports
29. Cancellation/refund calculation
30. Host listings dashboard
31. Host calendar/occupancy
32. Host income screen
33. Privacy settings
34. Mobile performance hardening
35. Translation coverage audit
36. Demo seed data
37. Final integration test pass

## Core product logic

- The main rental unit is a sleeping place.
- Guest chooses city, dates, and sleeping place.
- Guest search must support advanced location filters: country, city, district, street, landmark, proximity to center/metro/bus/train/airport/university/work/hospital/sea/park/shopping/gym/coworking/nightlife, and area types quiet/safe/residential/center/suburb/industrial/tourist/student/worker/long-stay, all backed by local SQLite data or stored metadata.
- The system calculates availability, stay days, nights, calendar days, price, discount, deposit, cleaning fee, service fee, total due, cancellation deadlines, host payout timing, reminders, rules, and compatibility.
- Host controls property, rooms, sleeping places, calendar, price, rules, and requests.
- Each sleeping place has its own calendar keyed by `sleeping_place_id + date`; supported date statuses are available, occupied, pending_payment, pending_host_confirmation, booked, guest_checked_in, guest_checked_out, closed_by_host, closed_by_service, cleaning, repair, broken, complaint_blocked, hidden, and request_only, with translated labels.
- Double booking protection uses `[check_in_date, check_out_date)`: overlapping ranges are rejected for the same sleeping place, but a new stay may start on the previous checkout date when same-day turnover, cleaning gap, and check-in/check-out rules allow it.
- Host turnover rules must support minimum turnover minutes, cleaning required/duration, post-checkout inspection, same-day check-in permission, morning-checkout/evening-check-in permission, earliest next check-in time, and latest previous checkout time; same-day turnover is allowed only when those time rules pass.
- Date selection must compute stay days, nights, calendar days, price, discounts, deposit, cleaning fee, service fee, total due, free cancellation date, cancellation penalty start date, host payout date, check-in reminder date, and check-out reminder date.
- Overnight sleeping-place billing uses `[check_in_date, check_out_date)`: July 10 to July 13 is 3 nights / 3 payable stay days and 4 calendar presence days. The main quantity for payment is nights / stay days, not inclusive calendar days.
- Date selection fields are: check-in date, check-in time, check-out date, check-out time, nights count, stay days count, calendar days count, early check-in, late check-out, flexible check-in time, flexible check-out time, host time approval required, check-in time comment, and check-out time comment.
- Automatic date checks must reject checkout before check-in, same-day checkout without a future daily/hourly mode, occupied or held sleeping-place dates, host-closed property, room, or sleeping-place dates, unavailable/repair room dates, required cleaning gaps, min/max-stay violations, disabled check-in/check-out weekdays, missing required guest verification, guest age conflicts, gender-policy conflicts, and guest-count over-limit.
- When check-in changes, the calendar must highlight available checkout dates, hide or disable unavailable checkout dates, show earliest/latest checkout dates, warn about blocked dates inside the selected range, suggest nearest available ranges, similar sleeping places, a neighboring room, and another place from the same host, then recalculate the full price quote automatically.
- When dates, guest count, timing options, promo code, or booking conditions change, pricing must recalculate check-in/out, stay days, base/weekday/weekend/holiday/weekly/monthly prices, long-stay/weekly/monthly/early-booking/last-minute/new-guest/personal discounts, early check-in/late checkout/extra guest fees, cleaning fee, deposit, service fee, taxes or city fees, promo code, discount total, before/after discount amounts, total due, host payout, refundable amount, and non-refundable amount.
- Pricing logic: under 7 days uses per-day pricing; from 7 days may apply weekly discount; from 30 days may apply monthly discount; weekend, holiday, and host date-specific prices adjust nightly lines; promo codes and checkout-date changes recalculate everything; extra guests, early check-in, and late checkout add fees or host-approval requests according to host rules.
- Price display must show per-day lines first, then stay days, accommodation amount, discount, cleaning fee, deposit, service fee, total due now, and a clear refundable-deposit note, all via translation keys.
- Booking classification must support primary flows instant_booking, host_confirmation_booking, stay_request, preliminary_inquiry, long_term_request, and urgent_today_booking; payment/deposit modes awaiting_payment, with_deposit, without_deposit, partial_payment, and full_payment; and modifiers extension, relocation, group_booking, and two_guest_sleeping_place.
- Booking fields must cover booking_number, status, guest/host/property/room/sleeping place, check-in/out dates and times, stay/calendar day counts, guest count, pricing totals, currency, payment status/method/date/deadline, guest/host messages, verification requirements, decline/cancellation/refund fields, check-in instructions, guest/host check-in/out confirmations, dispute/complaint flags, and guest/host review submission flags.
- Booking statuses must include draft, created, awaiting_host_approval, awaiting_guest_response, awaiting_payment, awaiting_identity_verification, awaiting_document_verification, confirmed, paid, ready_for_check_in, guest_checked_in, in_progress, check_out_soon, guest_checked_out, awaiting_room_inspection, awaiting_deposit_return, completed, awaiting_review, closed, declined_by_host, cancelled_by_guest, cancelled_by_host, cancelled_by_service, unpaid, guest_no_show, host_unresponsive, dispute_opened, frozen_pending_dispute_resolution, and requires_support_intervention, all with translated labels and status history.
- If the sleeping place is not instant-bookable, the guest sends a booking request with guest, host, sleeping place, dates, stay days, guests count, travel purpose, planned arrival time, host message, luggage flags, early/late timing needs, residence registration need, reporting document need, status, host response, decline reason, and expiration time.
- Host request detail shows a privacy-safe guest summary, dates, stay days, total amount, travel purpose, guest message, rule compatibility, and translated warning reasons.
- Host warnings may include night arrival, very early checkout, no identity verification, no reviews, prior cancellations, last-minute booking, stay longer than max allowed, cleaning-gap conflict, complaints, smoking conflict, pet conflict, or too many guests for the sleeping place.
- Stay extension must track current booking, current checkout, new checkout, additional stay days, added price, extension discount, amount due, status, host approval requirement, host response, decline reason, and extension payment date; it must recheck `[current_checkout, new_checkout)` availability, reject another guest's booking/hold, recalculate price, request host approval when needed, update the original booking and calendar after payment, and notify both sides.
- Stay relocation must track current sleeping place, new sleeping place, reason, relocation date, price difference, who pays the difference, required guest/host consent, status, guest comment, host comment, and reserved support comment; it must check new-place availability for the remaining stay, preserve the old-place hold before relocation, update holds after approval/application, recalculate price/refund/credit, update the original booking audit trail, and notify guest and host.
- Availability must be per sleeping place and must prevent double booking.
- Pricing must be transparent and broken into line items.
- The UI must stay mobile-first, multilingual, fast, friendly, and Livewire-native.
