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
- Do not render thousands of checkboxes.
- Do not load full city/country lists into a select.
- Use search/autocomplete with min 2 characters and debounce.
- Use wire:model.blur for text.
- Use wire:model.change for select/radio/checkbox.
- Use wire:model.live.debounce.500ms or slower for search boxes.
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
- translated labels
- translated validation
- no hard-coded UI text
