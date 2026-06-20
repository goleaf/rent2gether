# Performance Budget

## Mobile baseline

Design and test at 320px-430px, including a slow 3G profile and an older Android-class CPU. Server-render useful content before optional enhancement.

The 2026-06-20 production build baseline is:

| Asset | Raw | Gzip |
|---|---:|---:|
| Application CSS | 315.27 kB | 41.83 kB |
| Application JS | 0.00 kB | 0.02 kB |

Flux and Livewire framework assets are separate from the application entry. Do not add a large client library to reproduce behavior they already provide.

## 2026-06-20 audit notes

- Search filters render only inside the opened sheet/drawer; the initial search HTML no longer ships a hidden desktop filter tree.
- Search cards use the primary `mobile` media variant and do not expose full gallery paths in first-render card HTML.
- Public listing thumbnails, similar-place cards, favorites, compare cards, media manager rows, complaint media, and profile/host avatars use lazy loading or async decoding where they are not critical first-paint images.
- No map library import was found in `package.json`, `resources`, `app`, `public`, or Vite config during the audit.
- `wire:model.live` remains limited to search/autocomplete-style fields and uses at least `500ms` debounce.
- Textareas use blur/change/defer-style behavior and do not send live typing updates.
- City autocomplete requires at least two normalized characters and limits results to 10.
- `tests/Feature/MobilePerformanceBudgetTest.php` guards hidden filter DOM, full city-list leakage, search query count, mobile image variants, Livewire snapshot size, debounce rules, and textarea binding rules.

## Budgets

| Surface | Budget |
|---|---:|
| First HTML for home/search shell | 140 kB raw / 45 kB gzip target |
| First HTML for public listing detail | 180 kB raw / 55 kB gzip target |
| Home page application queries | 0 |
| Health page application queries | 0 |
| Top-level shell page queries | 0-1 lightweight account query |
| Public card-list queries | 5 per initial request |
| Guest search initial cards | 12 maximum, plus one lookahead row |
| Guest search maximum cards after load-more | 60 maximum |
| Public sleeping-place detail first render | 12 compact queries maximum |
| Host calendar initial queries | 6 maximum for one selected sleeping place |
| Booking date selector quote | 4 maximum for one selected sleeping place |
| Guest payment page first render | 6 compact queries maximum |
| Guest cancellation page first render | 1 compact booking graph plus refund estimate |
| Guest trip list page | 6 compact bookings per page |
| Guest booking detail page | 1 booking graph, no galleries or message thread |
| Guest check-in/check-out page | 1 booking graph, no gallery or full chat thread |
| Guest extension block | 1 compact booking graph plus one quote check |
| Guest/host review form | 1 compact booking graph |
| Guest/host complaint form | 1 compact booking graph |
| Complaint detail page | 1 compact complaint graph plus timeline |
| Listing public reviews | 5 visible reviews per page |
| User profile review summary | 10 guest reviews plus 10 host reviews maximum |
| Messages inbox | 30 compact threads maximum |
| Message thread | 100 latest messages maximum |
| Notification bell | 1 unread count query |
| Notifications page | 50 latest notifications maximum |
| Application JS entry | 50 kB gzip maximum |
| Application CSS entry | 50 kB gzip maximum |
| Initial listing cards | 12 maximum |
| Search card image | mobile variant, 120 kB target maximum |
| Thumbnail image | thumb variant, 30 kB target maximum |
| Full gallery image | lazy-loaded full compressed variant, 300 kB target maximum |
| Initial Livewire component snapshot | 35 kB target maximum |
| Livewire interaction payload | 20 kB target maximum |
| Autocomplete results | 10 maximum |
| Search debounce | 500 ms minimum |

## Loading rules

- Do not load a map library on the home page or initial search render.
- Keep top-level shell pages as translated empty states and action entry points; defer real lists to feature screens.
- Keep guest search mobile-first: no map, no full galleries, no hidden desktop filter tree, and no complete city/country list in HTML.
- Render search filters only when the filter sheet/drawer is opened; do not ship a hidden desktop sidebar to mobile browsers.
- Keep public sleeping-place detail lightweight: primary mobile image first, at most a few lazy thumbnails, no map, no full gallery, no full chat thread, lazy reviews, and lazy similar places.
- Keep auth, profile setup, settings, and security pages as compact Livewire forms with no preloaded country or city lists.
- Keep host onboarding/profile editing as compact Livewire forms with only scalar public properties and one avatar upload field.
- Keep the host property wizard step-based and render only the active step; do not keep nine hidden form sections in the DOM.
- Keep the host calendar month to 42 visible day cells and offer a compact list view instead of rendering multiple hidden months.
- Keep guest preference wizard/edit pages as compact Livewire forms with no preloaded city lists; resolve preferred city server-side from imported open data.
- Avatar and listing uploads must remain image-only and generate thumb, mobile, and compressed full variants server-side.
- Listing cards must render only one primary mobile image, use async image decoding, and must not preload full galleries.
- Lazy-load below-the-fold components, maps, reviews, similar places, and image galleries.
- Use responsive images with explicit dimensions, native lazy loading outside the first viewport, and `decoding="async"` for non-critical images.
- Do not preload entire country, city, amenity, or rule datasets into HTML or Livewire state.
- Use cursor pagination for large or append-only result sets.
- Keep the DOM small; render disclosed content when requested instead of hiding large trees.

## Livewire binding and state rules

- Use `wire:model.blur` for normal text fields.
- Use `wire:model.change` for selects, checkboxes, and radios.
- Use `wire:model.live.debounce.500ms` or `wire:model.live.debounce.750ms` only for search and autocomplete.
- Never use live model binding for long textareas.
- Never keep huge arrays in public Livewire properties.
- Store IDs, filters, and compact DTO arrays instead of full models or large relation graphs.
- Use cached lookup values for amenities, rules, countries, and common cities.
- Use `data-loading`, `wire:loading`, and skeletons for every network action.
- Use optimistic UI only where the rollback path is safe and obvious.

## Query rules

- Select only the columns required by a mobile card.
- Eager-load every relationship rendered in a list.
- Guest search must query `sleeping_places` as the primary table, join compact active `rooms`, `properties`, and host-profile fields for sorting/filtering, and eager-load only current/fallback translations, primary media, compact amenities, and scoped availability price overrides.
- Guest search date availability must use `[check_in, check_out)` and must skip exact date blocking only when the flexible-dates filter is explicitly enabled.
- Guest search load-more should increase a capped row limit and fetch one extra row to detect `has_more`; do not use large offset pagination for public result pages.
- Public sleeping-place detail must eager-load localized translations, compact amenities/rules, host profile, and selected room/property fields. Reviews and similar places must query from their lazy components.
- Public sleeping-place detail occupant summaries must use count-only booking overlap queries and must not eager-load guest profiles.
- Public listing pages that render the host card must eager-load `room.property.host.hostProfile` to avoid a lazy profile query in Blade.
- Host calendar queries must select compact sleeping-place, room, property, availability, and booking columns only; do not load full listings or galleries.
- Host request management must select compact booking, guest profile, preference, sleeping-place, room, and property columns; compatibility warnings are calculated for the selected request detail, not every card on first render.
- Booking date selector queries must select compact sleeping-place pricing/status columns, load only room/property status, read availability rows for the selected date range, and avoid maps, galleries, or full listing payloads.
- Guest payment pages must select only the booking summary columns, price lines, payment records, compact property access fields, and localized sleeping-place title fallback. Do not load galleries, maps, messages, or full listing payloads on payment pages.
- Guest cancellation pages must select one compact booking graph and localized sleeping-place title fallback, then calculate refunds in `RefundCalculator`. Do not load galleries, maps, messages, or unrelated booking history.
- Guest trip pages must use the compact trip booking query, paginate lists, eager-load localized property/room/sleeping-place titles, and keep address/instruction visibility in PHP presenter logic.
- Current stay and booking detail screens may load rules, amenities, price lines, and deposit records for one booking only; they must not load maps, galleries, full chat history, or unrelated bookings.
- Booking extension screens may load one compact booking graph, one active extension row, and the availability/pricing rows needed for `[current_checkout, requested_new_checkout)`. They must not load galleries, full booking history, maps, or message threads.
- Check-in/check-out screens may load one compact booking graph, checkin/checkout record, price/deposit context, and localized title/instructions only. Problem reports may upload up to 6 small images and must not render a full gallery.
- Review forms may load one compact completed booking graph and optional localized sleeping-place title. Guest review photos are capped at 4 small images.
- Complaint forms may load one compact participant booking graph and optional localized sleeping-place title. Complaint media is capped at 6 small images.
- Complaint detail pages may load one compact complaint graph, booking participants, localized sleeping-place title, and timeline rows only. They must not load message threads, maps, galleries, or unrelated complaints.
- Public listing reviews must use `Review::visible()`, select only display columns, eager-load compact reviewer names, and simple-paginate 5 rows.
- User profile review summaries must cap each role section to 10 visible reviews and avoid loading booking graphs or galleries.
- Messages inbox must select compact thread columns, eager-load guest/host names and one localized sleeping-place title, load one latest message per thread, and use an unread count aggregate. It must not load full message histories.
- Message thread pages may load the latest 100 messages for one thread, compact sender names, and attachment metadata only. Attachments are validated images/PDFs and should not trigger full listing/gallery loads.
- Notification bell must count unread rows for the authenticated `user_id` only and use the `user_id + read_at + created_at` index.
- Notifications page must select only notification display columns, cap the list to 50 recent rows, and render titles/bodies through translation keys and params.
- Property wizard autocomplete queries must require at least two characters, debounce at 500 ms, and limit country/city results to 10 rows.
- Amenity and rule picker searches must debounce at 500 ms, keep selected IDs only in Livewire public state, and use cached locale-specific lookup lists instead of re-querying the catalog on every render.
- Amenity and rule lookup rendering should select only ID, slug, category, status, and current/fallback translation names.
- Public search card amenities should eager-load only the small slug set displayed on cards, not every amenity attached to the listing.
- Use `withCount`, `withExists`, and other Eloquent aggregates before entering loops.
- Add indexes that match each filter and ordering combination.
- Record `EXPLAIN QUERY PLAN` findings for guest search, availability overlap, and booking lookup changes once representative search data exists.

## Verification

- Guard public query counts in feature tests.
- Run `npm run build` and compare the gzip totals above whenever frontend dependencies or imports change.
- Use a mobile browser trace after visual changes and verify a clean console, accessible names, and no unexpected network requests.
