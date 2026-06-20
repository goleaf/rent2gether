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
- System calculates availability + nights + calendar days + price + discount + deposit + rules + compatibility.
- Host controls property + rooms + sleeping places + calendar + price + rules + requests.
- Everything is mobile-first + multilingual + fast + friendly + Livewire-native.
- This frame prevents drift into Filament, Volt, admin panels, desktop-first UI, or heavy SPA architecture.

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
