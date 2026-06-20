# Extended Property Feature

## Purpose

`Property` is the top-level place around rooms and sleeping places. The extended property module lets hosts describe the building, address privacy, location, condition, access, parking, delivery, and guest-visible summaries without turning the base `properties` table into a large unstructured record.

Guests should understand:

- what type of place this is
- where it is approximately before booking
- how easy it is to reach
- whether the district is quiet and safe
- what condition the property is in
- how entry, keys, night access, visitors, couriers, and delivery work
- which exact address and access details stay private until booking rules allow them

## Table Structure

Core property identity and compact searchable counters stay in `properties`.

One-to-one detail tables hold the larger structured groups:

- `property_location_details`
- `property_condition_details`
- `property_access_details`

Translated public text stays in `property_translations`.

Important indexed property fields include:

- `host_user_id + status`
- `country_id + city_id`
- `city_id + status`
- `property_type + status`
- `district + city_id`
- `has_elevator`
- `free_sleeping_places_count`
- `occupied_sleeping_places_count`

Detail tables keep a unique `property_id` foreign key with cascade delete. Public filters and summaries use indexed scalar fields such as `distance_to_center_meters`, `transport_minutes_to_center`, `district_safety_level`, `repair_state`, `cleanliness_level`, `self_check_in_available`, `access_24_7`, and key/parking booleans.

## Translation Fields

`property_translations` supports:

- `title`
- `short_description`
- `full_description`
- `location_description`
- `transport_description`
- `neighborhood_description`
- `parking_description`
- `condition_description`
- `access_description`
- `self_check_in_instructions`
- `night_entry_instructions`
- `delivery_instructions`
- `guest_visitor_rules_text`
- `courier_rules_text`
- `important_notes`

UI strings live in:

- `lang/en/property.php`
- `lang/ru/property.php`

English and Russian keys must stay in parity. Future languages add translation rows and locale files, not schema changes.

## Host Wizard

The host wizard uses Livewire class components only:

- `App\Livewire\Host\Properties\PropertyMainInfoStep`
- `App\Livewire\Host\Properties\PropertyStructureStep`
- `App\Livewire\Host\Properties\PropertyLocationStep`
- `App\Livewire\Host\Properties\PropertyConditionStep`
- `App\Livewire\Host\Properties\PropertyAccessStep`
- `App\Livewire\Host\Properties\PropertyCompletionPanel`

Routes live under:

- `/{locale}/host/properties/{property}/extended/main`
- `/{locale}/host/properties/{property}/extended/structure`
- `/{locale}/host/properties/{property}/extended/location`
- `/{locale}/host/properties/{property}/extended/condition`
- `/{locale}/host/properties/{property}/extended/access`
- `/{locale}/host/properties/{property}/extended/completion`

Each step authorizes ownership through `Property::isOwnedBy()`. Text inputs use `wire:model.blur`; selects and checkboxes use `wire:model.change`.

## Public Display

The listing detail page reads a privacy-safe property summary through:

- `App\Services\Properties\PropertyGuestSummaryService`
- `App\Services\Properties\PropertyPrivacyService`
- `App\Services\Properties\PropertyLocationService`
- `App\Services\Properties\PropertyConditionService`
- `App\Services\Properties\PropertyAccessService`

Public static property sections render as Blade/Flux inside the sleeping-place detail page rather than many small Livewire components. This keeps the mobile first render smaller.

Visible guest sections include:

- main property summary
- location
- transport
- condition
- access
- parking
- delivery

Empty rows are hidden. Warnings such as mold, insects, unstable heating, unstable hot water, or damp marks are shown only when the host-filled data says they apply.

## Privacy Rules

Before a confirmed or paid booking allows it, guests must not see:

- full exact address
- apartment number
- door/intercom/gate codes
- key safe location
- private host contact details
- internal notes

The public page can show city, district, floor/elevator, approximate area, transport, parking, and high-level access summaries.

There is no property manager role. If someone other than the host helps with keys or entry, the UI and data use `host_representative`.

## Services

- `PropertyProfileService` builds guest and host profile data and updates main fields/counters.
- `PropertyLocationService` updates and summarizes location, transport, and parking data.
- `PropertyConditionService` updates condition details and returns guest warnings.
- `PropertyAccessService` updates access details and reveals private instructions only when booking privacy allows it.
- `PropertyPrivacyService` decides whether exact address, apartment number, codes, and key safe location can be shown.
- `PropertyCompletionService` calculates host completion percentage.
- `PropertyGuestSummaryService` groups property data into mobile-friendly public sections.

## Performance Rules

- Do not query from Blade.
- Keep Livewire public state to scalar fields and IDs.
- Use one-to-one detail tables instead of a huge `properties` table.
- Use selected columns when loading listing detail.
- Lazy or collapse map sections; do not load maps on first render.
- Hide empty public rows instead of rendering technical placeholders.

## Tests

Primary coverage:

```bash
php artisan test tests/Feature/ExtendedPropertyFieldsTest.php --compact
```

This verifies detail relationships, cascade deletes, key indexes, English/Russian translation fields, host step updates, owner authorization, privacy-safe public summaries, confirmed access instructions, condition warnings, and private access data hidden before booking.
