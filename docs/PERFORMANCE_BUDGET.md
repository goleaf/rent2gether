# Performance Budget

## Mobile baseline

Design and test at 320px-430px, including a slow 3G profile and an older Android-class CPU. Server-render useful content before optional enhancement.

The 2026-06-19 production build baseline is:

| Asset | Raw | Gzip |
|---|---:|---:|
| Application CSS | 288.37 kB | 38.35 kB |
| Application JS | 0.00 kB | 0.02 kB |

Flux and Livewire framework assets are separate from the application entry. Do not add a large client library to reproduce behavior they already provide.

## Budgets

| Surface | Budget |
|---|---:|
| Home page application queries | 0 |
| Health page application queries | 0 |
| Public card-list queries | 5 per initial request |
| Application JS entry | 50 kB gzip maximum |
| Application CSS entry | 50 kB gzip maximum |
| Initial listing cards | 20 maximum |
| Autocomplete results | 10 maximum |
| Search debounce | 500 ms minimum |

## Loading rules

- Do not load a map library on the home page or initial search render.
- Lazy-load below-the-fold components, maps, and image galleries.
- Use responsive images with explicit dimensions and native lazy loading outside the first viewport.
- Do not preload entire country, city, amenity, or rule datasets into HTML or Livewire state.
- Use cursor pagination for large or append-only result sets.
- Keep the DOM small; render disclosed content when requested instead of hiding large trees.

## Query rules

- Select only the columns required by a mobile card.
- Eager-load every relationship rendered in a list.
- Use `withCount`, `withExists`, and other Eloquent aggregates before entering loops.
- Add indexes that match each filter and ordering combination.
- Record `EXPLAIN QUERY PLAN` findings for search, availability overlap, and booking lookup changes.

## Verification

- Guard public query counts in feature tests.
- Run `npm run build` and compare the gzip totals above whenever frontend dependencies or imports change.
- Use a mobile browser trace after visual changes and verify a clean console, accessible names, and no unexpected network requests.
