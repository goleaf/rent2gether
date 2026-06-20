---
name: localization-i18n
description: Use when adding translations, localized routes, locale switcher, translatable models, translated validation, multilingual user content, or new languages.
---

The app must support English and Russian from day one.

Locales:
- en
- ru

Rules:
- No hard-coded visible strings in Blade, Livewire, PHP responses, validation messages, notifications, emails, or seed labels.
- Use translation keys for all UI.
- Store user-selected locale.
- Support locale in URL, session, and user settings.
- Use fallback locale.
- All validation attributes must be translated.
- Every notification title/body must be translated.
- Every enum/status label must be translated.
- Booking lifecycle status labels must live in translation keys such as `statuses.booking.*`; do not hard-code status labels in Blade, Livewire, PHP responses, notifications, emails, seeders, factories, or tests.
- Every amenity/rule label must be translatable.
- Every new feature must include English and Russian translations for headings, labels, buttons, empty states, loading states, validation attributes, statuses, notifications, seed labels, and any user-visible copy.
- User-generated listing content must support translations:
  title, summary, description, house rules, check-in instructions, check-out instructions, safety notes, cancellation text.
- Use separate translation tables for important public content:
  property_translations
  room_translations
  sleeping_place_translations
  amenity_translations
  rule_translations
- Translation tables require locale index and unique constraints.
- Add tests that switch locale and verify strings change.
- Add an Artisan command to report missing translation keys.

Laravel 13 localization docs confirm that Laravel provides localization features for retrieving strings in different languages and supporting multiple languages. (Laravel)
