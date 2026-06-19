# I18N Rules

## Supported locales

- English: `en`
- Russian: `ru`

English is the fallback locale. Locale selection is carried in the URL, stored in the session, and persisted to the authenticated user's `user_settings` row.

## UI copy

- Use domain translation keys such as `navigation.search` and `booking.total`; do not use an English sentence as its own key.
- Translate visible Blade text, accessible labels, placeholders, validation attributes, enum labels, notifications, mail, and seeded labels.
- Keep English and Russian key trees in parity.
- Keep mobile copy short, calm, and actionable. Explain what happened and what the user can do next.
- Brand names, locale codes, currency codes, and user content may remain literal values.

## Routes and middleware

Public pages use an `{locale}` prefix constrained to `en|ru`. `SetLocale` must set the application locale and URL defaults before a localized page renders.

Internal localized links must pass the current locale and should use named routes. The locale switcher preserves the current named route, route parameters, and query string.

## User-generated content

Store important translated listing content in child translation tables. This includes titles, summaries, descriptions, house rules, check-in instructions, check-out instructions, safety notes, and cancellation text.

Each translated row should contain:

- parent foreign key
- normalized locale code
- translated fields
- timestamps where editorial history matters

Use a unique parent-and-locale constraint and an index beginning with locale for locale-first discovery queries.

## Validation and testing

- Translate validation messages and attribute names in the active locale.
- Add feature tests that render each public page in English and Russian.
- Assert important layout labels as well as page headings so untranslated shell copy is caught.
- Run `php artisan translations:missing` to check catalogue parity and statically referenced keys.
- Treat a returned translation key or English fallback on a Russian screen as a test failure unless explicitly approved.
