---
name: livewire-quality
description: Use when writing or reviewing Livewire components, actions, forms, validation, loading states, uploads, events, tests, and AJAX behavior.
---

Livewire rules:
- Use class components only.
- Do not use Volt.
- Do not create web controllers for Livewire pages or actions.
- Do not create `resources/views/auth/` or `resources/views/search/`; use existing Livewire auth/search views.
- Read `docs/PROJECT_STRUCTURE.md` before creating a component or view.
- Use small public properties.
- Use IDs instead of full models in component state.
- Use computed properties for derived data.
- Use services/actions for business logic.
- Use real-time validation only when useful.
- Use wire:model.blur for normal text fields.
- Use wire:model.change for selects, checkboxes, and radios.
- Use wire:model.live.debounce.500ms or wire:model.live.debounce.750ms only for search/autocomplete.
- Never use live model binding for long textareas.
- Never keep huge arrays in public Livewire properties.
- Use compact DTO arrays for cards and summaries.
- Use data-loading classes and wire:loading for every network action.
- Use skeletons for deferred network content.
- Use optimistic UI only where the rollback path is safe and obvious.
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
- New user-facing features must include a Livewire class component, Blade view, Flux UI where practical, validation, friendly empty and loading states, authorization or policy when needed, and tests.
- Full-page Livewire components should be mounted directly from `routes/web.php` and should apply the project layout from the component `render()` method.

Livewire 4 docs say default wire:model does not send a network request on every input update unless timing modifiers are used, .live has debounce behavior, and wire:model.blur/.change are available. This is important for old phones and 3G. (Laravel)
Livewire 4 также поддерживает loading states/data-loading, real-time validation через #[Validate], file uploads через WithFileUploads, computed properties и wire:navigate. (Laravel)
