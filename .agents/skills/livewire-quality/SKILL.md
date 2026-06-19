---
name: livewire-quality
description: Use when writing or reviewing Livewire components, actions, forms, validation, loading states, uploads, events, tests, and AJAX behavior.
---

Livewire rules:
- Use class components only.
- Do not use Volt.
- Use small public properties.
- Use IDs instead of full models in component state.
- Use computed properties for derived data.
- Use services/actions for business logic.
- Use real-time validation only when useful.
- Use wire:model.blur for text fields.
- Use wire:model.change for select, checkbox, radio.
- Use wire:model.live.debounce.500ms or 750ms for search/autocomplete.
- Use data-loading classes and wire:loading for every network action.
- Use wire:navigate for internal links where appropriate.
- Use lazy loading for below-the-fold sections.
- Use WithFileUploads only inside upload-focused components.
- Validate files by size, type, and dimensions.
- Keep uploads mobile-friendly.
- Tests are mandatory:
  component renders
  validation works
  action succeeds
  unauthorized action fails
  locale strings display correctly

Livewire 4 docs say default wire:model does not send a network request on every input update unless timing modifiers are used, .live has debounce behavior, and wire:model.blur/.change are available. This is important for old phones and 3G. (Laravel)
Livewire 4 также поддерживает loading states/data-loading, real-time validation через #[Validate], file uploads через WithFileUploads, computed properties и wire:navigate. (Laravel)
