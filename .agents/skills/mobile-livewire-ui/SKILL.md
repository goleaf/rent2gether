---
name: mobile-livewire-ui
description: Use when building Blade, Flux, Tailwind, or Livewire UI for mobile-first pages, forms, search, booking, host onboarding, messages, and dashboards.
---

Build UI for mobile first.

Rules:
- Start at 320px width.
- Avoid desktop-first layouts.
- Use one-column layouts by default.
- Use sticky bottom action bars for primary actions.
- Use bottom sheets/drawers for filters and secondary controls.
- Use accordions for long details.
- Use cards for listings.
- Use compact summaries above long forms.
- Use step-by-step forms for host onboarding.
- Use skeleton loaders for deferred content.
- Use loading states for all Livewire actions.
- Use Flux components where available.
- Keep DOM small.
- Do not render large hidden sections.
- Never render hidden huge filters.
- Use bottom sheets, drawers, and lazy components for large secondary UI.
- Do not render thousands of checkboxes.
- Never load full countries/cities into a select.
- Use search/autocomplete with min 2 characters and debounce.
- Use wire:model.blur for normal text fields.
- Use wire:model.change for selects, radios, and checkboxes.
- Use wire:model.live.debounce.500ms or wire:model.live.debounce.750ms only for search/autocomplete.
- Never use live model binding for long textareas.
- Use cursor pagination or load-more behavior for search.
- Use compact DTO arrays for cards.
- Use data-loading states and skeletons.
- Avoid custom JS except tiny Alpine interactions when Flux/Livewire needs it.

Mobile navigation:
- Bottom nav for main guest actions:
  Search, Trips, Favorites, Messages, Profile.
- Host mode bottom nav:
  Listings, Calendar, Requests, Messages, Profile.
- Account switcher:
  Guest mode / Host mode.

Every screen must have:
- clear title
- short helper text
- visible primary action
- friendly empty state
- friendly loading state
- translated labels
- translated validation
- no hard-coded UI text

Every new user-facing feature must include Flux UI where practical, a mobile-first layout, translated English and Russian copy, friendly empty and loading states, and tests that render the mobile page.
