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
- System calculates availability + nights + calendar days + price + discount + deposit + rules + compatibility.
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
- English translations
- Russian translations
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
- The system calculates availability, nights, calendar days, price, discount, deposit, rules, and compatibility.
- Host controls property, rooms, sleeping places, calendar, price, rules, and requests.
- Date selection must compute nights, calendar days, price, discounts, deposit, fees, and reminders.
- Availability must be per sleeping place and must prevent double booking.
- Pricing must be transparent and broken into line items.
- The UI must stay mobile-first, multilingual, fast, friendly, and Livewire-native.
