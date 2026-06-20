# Localization Rules

Supported foundation locales:

- `en`
- `ru`

Every visible UI string must use a translation key. Do not hard-code visible labels, headings, validation messages, empty states, notification text, status labels, or action copy in Blade or Livewire components.

Use module files for domain copy:

- `lang/en/common.php`
- `lang/ru/common.php`
- `lang/en/domain.php`
- `lang/ru/domain.php`
- `lang/en/properties.php`
- `lang/ru/properties.php`
- `lang/en/rooms.php`
- `lang/ru/rooms.php`
- `lang/en/sleeping_places.php`
- `lang/ru/sleeping_places.php`

Public user-generated listing content should be stored in translation tables, not in language-specific columns on the base record. Important public content belongs in tables such as `property_translations`, `room_translations`, and `sleeping_place_translations`.

Locale should be preserved through URL, session, and user settings where applicable. Use the fallback locale when a translation is missing.
