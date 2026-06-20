# Listing Detail Feature

## Purpose

The listing detail page is the main guest decision screen for one `SleepingPlace`.
It explains the property, room, sleeping place, dates, price, rules, safety, host context, cancellation terms, and practical living instructions before a guest sends a booking request.

The page should reduce repeated pre-booking questions by explaining:

- what is included in the price
- what the guest should bring
- where belongings and food are stored
- how to use shared kitchen, bathroom, and laundry spaces
- how keys and night entry work
- how to contact the host safely
- what to do if there is a problem, conflict, lost key, or repair issue

## Page Blocks

The current public route is `/{locale}/places/{sleepingPlace}`.
It renders a sleeping-place-first detail page with context from the related room, property, host, availability, price, rules, amenities, reviews, safety, and similar places.

Mobile order keeps decision data early:

- photo gallery
- title, location, rating, and decision summary
- date, price, favorite, and booking panel
- sleeping place details
- room details and privacy-safe nearby guest count
- extended description and instructions
- property, amenities, rules, calendar, area, host, reviews, safety, cancellation, similar places, and FAQ

## Extended Content Fields

Extended user-facing content is stored on translation tables so English, Russian, and future locales can evolve independently.

`property_translations` stores broad living instructions:

- `short_description`
- `full_description`
- `why_convenient`
- `main_pros`
- `important_cons`
- `what_to_know_beforehand`
- `what_is_included`
- `what_is_not_included`
- `what_to_bring`
- `where_to_store_belongings`
- `where_to_store_food`
- `kitchen_instructions`
- `bathroom_instructions`
- `laundry_instructions`
- `key_pickup_instructions`
- `night_entry_instructions`
- `host_contact_instructions`
- `problem_instructions`
- `lost_key_instructions`
- `neighbor_conflict_instructions`
- `repair_problem_instructions`

`room_translations` stores room-specific shared-space context:

- `room_description`
- `room_rules_text`
- `room_pros`
- `room_cons`
- `who_lives_nearby_text`
- `quiet_hours_text`
- `storage_instructions`
- `shared_space_instructions`

`sleeping_place_translations` stores exact sleeping-place content:

- `sleeping_place_title`
- `sleeping_place_description`
- `sleeping_place_pros`
- `sleeping_place_cons`
- `sleeping_place_special_notes`
- `what_is_included_for_place`
- `what_guest_should_bring_for_place`

## Translation Structure

UI labels for this module live in:

- `lang/en/listing_detail.php`
- `lang/ru/listing_detail.php`

Guest-authored listing content is resolved per field, not only per translation row. If Russian exists but a specific Russian field is empty, the detail page may fall back to English for that field.

## Components And Services

Main Livewire surface:

- `App\Livewire\Places\ShowSleepingPlace`

Supporting services:

- `App\Services\Listings\ListingDetailContentService`
- `App\Services\Listings\ListingDetailPrivacyService`
- `App\Services\Listings\ListingDetailSectionService`

Blade/Flux display component:

- `resources/views/components/listings/detail/description-sections.blade.php`

The extended content sections are display-only. They stay Blade/Flux instead of separate Livewire components because they have no independent actions.

Property profile sections are also display-only on the public detail page. They are built by:

- `App\Services\Properties\PropertyGuestSummaryService`
- `App\Services\Properties\PropertyPrivacyService`
- `App\Services\Properties\PropertyLocationService`
- `App\Services\Properties\PropertyConditionService`
- `App\Services\Properties\PropertyAccessService`

The guest-facing property block can show compact main information, location, transport, condition, access, parking, and delivery sections. It must hide empty rows and must not expose exact address, apartment number, door/intercom/gate codes, key safe location, private host contact data, or internal notes before the booking privacy rules allow them.

Room profile sections are display-only too. They are built by:

- `App\Services\Rooms\RoomGuestSummaryService`
- `App\Services\Rooms\RoomPrivacyService`
- `App\Services\Rooms\RoomLayoutService`
- `App\Services\Rooms\RoomComfortService`
- `App\Services\Rooms\RoomAccessService`
- `App\Services\Rooms\RoomConditionService`
- `App\Services\Rooms\RoomOccupancyService`

The guest-facing room block can show compact main information, layout, comfort, access and storage, condition, and room rules. It must hide empty rows and must not expose private occupant data or withheld room numbers before booking rules allow it.

## Privacy Rules

The detail page must not expose:

- exact address before the booking rules allow it
- exact key pickup or night-entry instructions before confirmation
- private host phone/contact instructions before confirmation
- names or private details of other guests in the room
- internal notes

Before confirmation, guests see safe hints such as “exact entry details are shown after the booking is confirmed.”

## Mobile UX Rules

Long instructions are grouped into mobile accordions. Open by default:

- description
- what is included
- keys
- problem instructions

Other instruction sections stay collapsed and are hidden completely when empty for guests.

## Performance Rules

- Keep Livewire public properties small.
- Use selected columns for listing, room, property, host, and translations.
- Render only non-empty text sections.
- Keep reviews and similar places lazy.
- Do not load a real map on first render.
- Do not split static text into many Livewire components.
- Do not query inside Blade.

## Tests

Relevant coverage lives in `tests/Feature/PublicSleepingPlaceDetailTest.php`:

- English and Russian detail rendering
- price/date updates
- privacy-safe nearby guest summary
- decision context order
- extended instruction sections
- per-field translation fallback
- empty optional sections hidden
- private host/contact/key content hidden before booking

Run:

```bash
php artisan test tests/Feature/PublicSleepingPlaceDetailTest.php --compact
php artisan translations:missing
./vendor/bin/pint
npm run build
```
