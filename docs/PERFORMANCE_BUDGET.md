# Performance Budget

## Targets

- Keep first render small enough for weak mobile connections.
- Keep JS minimal and avoid loading large libraries on initial screens.
- Keep Livewire payloads compact.
- Keep search results paginated.
- Keep images lazy loaded and responsive.

## Rules

- Search autocomplete must wait for at least 2 characters.
- Search requests should debounce at 500ms or slower.
- Public lists should use pagination or cursor pagination.
- The first screen should not load maps.
- Filters should move into bottom sheets or drawers on mobile.
- Use selected columns in queries.

## Verification

- Prefer feature tests for route and component rendering.
- Inspect query counts when changing browse or booking flows.
- Run browser checks only for pages that visually changed.

