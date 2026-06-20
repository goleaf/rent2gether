---
name: project-architect
description: Use when planning or changing architecture, database schema, domain models, routes, folder structure, or feature boundaries for the sleeping-place marketplace.
---

You are working on a Laravel 13 + Livewire 4 + Flux Pro + SQLite mobile-first marketplace.

Always enforce:
- No Livewire Volt.
- No Filament.
- No admin area.
- No Inertia.
- No React/Vue SPA.
- Mobile-first.
- Full i18n for English and Russian.
- Future language support.
- SQLite performance.
- Friendly UX.

Core marketplace loop:
- Guest chooses city + dates + sleeping place.
- System calculates availability + stay days + nights + calendar days + price + discount + deposit + cleaning fee + service fee + total due + cancellation deadlines + host payout timing + reminders + rules + compatibility.
- Host controls property + rooms + sleeping places + calendar + price + rules + requests.
- Everything is mobile-first + multilingual + fast + friendly + Livewire-native.
- This frame prevents drift into Filament, Volt, admin panels, desktop-first UI, or heavy SPA architecture.

Advanced guest search:
- Location filters include country_id, city_id, district, street, landmark, near_center, near_metro, near_bus_stop, near_train_station, near_airport, near_university, near_work, near_hospital, near_sea, near_park, near_shopping_center, near_gym, near_coworking, near_nightlife, area_quiet, area_safe, area_residential, area_city_center, area_suburb, area_industrial, area_tourist, area_students, area_workers, area_long_stay.
- Country/city use imported SQLite geo autocomplete; no full select lists and no live external geo APIs during search.
- District/street/landmark and proximity filters use normalized local fields, stored coordinates, precomputed distances, or offline/imported points of interest.
- `near_work` must use a saved user point or local area reference and must not send private addresses to public geocoders during search.
- Group advanced filters in lazy mobile bottom sheets/drawers and keep Livewire state to scalar IDs, booleans, and short text.

Sleeping place availability:
- Every sleeping place has its own separate calendar keyed by `sleeping_place_id + date`.
- Room and property closures may cascade down, but final availability is checked per sleeping place.
- Date statuses: available, occupied, pending_payment, pending_host_confirmation, booked, guest_checked_in, guest_checked_out, closed_by_host, closed_by_service, cleaning, repair, broken, complaint_blocked, hidden, request_only.
- Status labels and blocking reasons must be translated.
- Availability queries must use indexed `sleeping_place_id + date` and status-filtered calendar queries should plan `sleeping_place_id + status + date`.
- Double booking protection uses `[check_in_date, check_out_date)`: reject overlap when requested check-in is before existing checkout and requested checkout is after existing check-in. Same-day next check-in is allowed only when turnover, cleaning gap, and boundary flags allow it.
- Booking/request actions must recheck availability inside the transaction before writing holds and return translated friendly messages plus nearest alternatives when dates are no longer available.
- Turnover fields: minimum_turnover_minutes, cleaning_required_between_guests, cleaning_duration_minutes, inspection_required_after_checkout, same_day_check_in_allowed, morning_checkout_evening_checkin_allowed, earliest_next_check_in_time, latest_previous_check_out_time.
- Same-day turnover is allowed only when the previous checkout time plus required turnover/cleaning/inspection time is before or equal to the next check-in time and all host/calendar boundary rules pass.

Automatic date logic:
- When a guest chooses check-in and check-out dates, calculate stay days, nights, calendar days, total price, discount, deposit, cleaning fee, service fee, total due, free cancellation date, cancellation penalty start date, host payout date, check-in reminder date, and check-out reminder date.
- Date selection fields are check-in date, check-in time, check-out date, check-out time, nights count, stay days count, calendar days count, early check-in requested, late check-out requested, flexible check-in time, flexible check-out time, host time approval required, check-in time comment, and check-out time comment.
- Overnight sleeping-place billing uses the half-open range `[check_in_date, check_out_date)`: July 10 to July 13 is 3 nights / 3 payable stay days and 4 calendar presence days. The main payable quantity is nights / stay days, not inclusive calendar days.
- Automatic date checks must reject checkout before check-in, same-day checkout without a future daily/hourly mode, occupied or held sleeping-place dates, host-closed property, room, or sleeping-place dates, unavailable/repair room dates, required cleaning gaps, min/max-stay violations, disabled check-in/check-out weekdays, missing required guest verification, guest age conflicts, gender-policy conflicts, and guest-count over-limit.
- When check-in changes, the calendar must highlight available checkout dates, hide or disable unavailable checkout dates, show earliest/latest checkout dates, warn about blocked dates inside the selected range, suggest nearest available ranges, similar sleeping places, a neighboring room, and another place from the same host, then recalculate the full price quote automatically.
- When dates, guest count, timing options, promo code, or booking conditions change, pricing must recalculate check-in/out, stay days, base/weekday/weekend/holiday/weekly/monthly prices, long-stay/weekly/monthly/early-booking/last-minute/new-guest/personal discounts, early check-in/late checkout/extra guest fees, cleaning fee, deposit, service fee, taxes or city fees, promo code, discount total, before/after discount amounts, total due, host payout, refundable amount, and non-refundable amount.
- Pricing logic: under 7 days uses per-day pricing; from 7 days may apply weekly discount; from 30 days may apply monthly discount; weekend, holiday, and host date-specific prices adjust nightly lines; promo codes and checkout-date changes recalculate everything; extra guests, early check-in, and late checkout add fees or host-approval requests according to host rules.
- Price display must show per-day lines first, then stay days, accommodation amount, discount, cleaning fee, deposit, service fee, total due now, and a clear refundable-deposit note, all via translation keys.
- Keep this logic in services/actions with tests, not in Blade or Livewire view templates.
- Return compact DTO data and translation keys for explanations/warnings.

Booking request logic:
- Booking classification must separate primary flow, payment mode, deposit mode, and modifiers instead of collapsing all variants into one status.
- Primary flows: instant_booking, host_confirmation_booking, stay_request, preliminary_inquiry, long_term_request, urgent_today_booking.
- Payment/deposit modes: awaiting_payment, with_deposit, without_deposit, partial_payment, full_payment.
- Modifiers/scenarios: extension, relocation, group_booking, two_guest_sleeping_place.
- A two-guest booking for one sleeping place requires max_guests >= 2, compatible room rules, and recalculated extra guest fees.
- Group booking still reserves availability per sleeping place; extension and relocation must link to the original booking and preserve status history.
- Booking fields: booking_number, status, guest_user_id, host_user_id, property_id, room_id, sleeping_place_id, check_in_date, check_in_time, check_out_date, check_out_time, stay_days_count, calendar_days_count, guests_count, price_per_stay_day, period_price_amount, discount_amount, deposit_amount, cleaning_fee_amount, service_fee_amount, total_amount, currency, payment_status, payment_method, paid_at, payment_deadline_at, guest_message, host_response, verification requirements, decline/cancellation/refund fields, check_in_instructions, guest/host check-in/out timestamps, dispute/complaint flags, and guest/host review submission timestamps.
- Prefer timestamps over booleans for confirmations and reviews; expose booleans only as derived DTO values.
- Booking statuses: draft, created, awaiting_host_approval, awaiting_guest_response, awaiting_payment, awaiting_identity_verification, awaiting_document_verification, confirmed, paid, ready_for_check_in, guest_checked_in, in_progress, check_out_soon, guest_checked_out, awaiting_room_inspection, awaiting_deposit_return, completed, awaiting_review, closed, declined_by_host, cancelled_by_guest, cancelled_by_host, cancelled_by_service, unpaid, guest_no_show, host_unresponsive, dispute_opened, frozen_pending_dispute_resolution, requires_support_intervention.
- Every status label must use translation keys; requires_support_intervention is a state only and must not create a support/staff/admin panel.
- Keep payment_status separate from booking lifecycle status and write booking_status_histories for lifecycle transitions.
- If a sleeping place is not instant-bookable, the guest sends a booking request.
- Request fields: guest, host, sleeping place, check-in date, check-out date, stay days count, guests count, travel purpose, planned arrival time, message to host, has luggage, needs luggage space, needs early check-in, needs late check-out, needs residence registration, needs reporting documents, request status, host response, decline reason, and request expiration time.
- Host sees privacy-safe guest summary, dates, stay days, total amount, travel purpose, guest message, rule compatibility, and warning reasons.
- Warning reasons include night arrival, very early checkout, missing identity verification, no reviews, prior cancellations, last-minute booking, stay over max duration, cleaning-gap conflict, complaints, smoking conflict, pet conflict, and too many guests for the sleeping place.
- Keep request assessment in services/actions with tests and translated reason keys.

Stay extension logic:
- Extension fields: booking_id, current_check_out_date, new_check_out_date, additional_stay_days_count, additional_price_per_stay_day, extension_discount_amount, extension_amount_due, status, host_approval_required, host_response, decline_reason, extension_paid_at.
- Extensions require the sleeping place to be free in `[current_check_out_date, new_check_out_date)` and must reject another guest's booking or hold.
- PricingService recalculates the additional amount due; ExtensionService shows the quote, sends host approval when required, applies payment, updates the original booking checkout/stay days/totals/status history/price lines, blocks availability, and notifies guest and host.
- Extension statuses, responses, decline reasons, price explanations, and notifications use translation keys.
- Do not create finance, support, staff, or admin workflows for extensions.

Stay relocation logic:
- Relocation fields: booking_id, current_sleeping_place_id, new_sleeping_place_id, reason, relocation_date, price_difference_amount, price_difference_payer, guest_consent_required, host_consent_required, status, guest_comment, host_comment, support_comment.
- Reasons include noisy_neighbors, uncomfortable_bed, resident_conflict, broken_item, host_offered_other_place, wants_more_private_room, wants_cheaper_place, wants_more_comfort.
- RelocationService checks the new sleeping place for `[relocation_date, booking.check_out_date)`, keeps the old place blocked before relocation, updates old/new holds only after approval/application, recalculates price/refund/credit/deposit implications, updates the original booking audit trail, and notifies guest and host.
- Guest consent is required for host-offered relocation; host consent is required for guest-requested relocation unless rules explicitly allow self-service relocation.
- Relocation reasons, statuses, consent labels, price-difference explanations, and notifications use translation keys.
- `support_comment` is reserved data only; do not create support, staff, finance, moderation, or admin workflows.

Before implementing architecture:
1. Read AGENTS.md.
2. Check existing migrations, models, routes, Livewire components, lang files, and tests.
3. Avoid duplicated concepts.
4. Preserve the central hierarchy:
   User -> Host profile
   Property -> Room -> SleepingPlace
   SleepingPlace -> Availability -> Booking
5. Keep booking logic in services/actions, not in Blade.
6. Keep pricing logic testable.
7. Keep availability logic testable.
8. Keep translations separated and indexed.
9. Keep media metadata separated from physical files.
10. Use domain names that are clear and stable.

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

Core domain models:
- User
- UserProfile
- HostProfile
- GuestPreference
- Country
- Region
- City
- Property
- PropertyTranslation
- Room
- RoomTranslation
- SleepingPlace
- SleepingPlaceTranslation
- Amenity
- AmenityTranslation
- Rule
- RuleTranslation
- PropertyAmenity
- RoomAmenity
- SleepingPlaceAmenity
- PropertyRule
- RoomRule
- SleepingPlaceRule
- Media
- AvailabilityDay
- PriceRule
- DiscountRule
- Booking
- BookingGuest
- BookingPriceLine
- BookingStatusHistory
- PaymentRecord
- DepositRecord
- RefundRequest
- MessageThread
- Message
- Favorite
- SavedSearch
- WaitlistItem
- Review
- Complaint
- Notification
- UserSetting
- LocaleSetting

Never implement staff/admin workflows yet.
