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
- Use `#[Validate]` for simple static Livewire rules and always call `$this->validate()` or `$this->form->validate()` before persisting.
- Use `#[Validate(..., onUpdate: false)]` when colocated rules should not run on every update.
- Use `rules()` or Livewire Form Object rules for Laravel `Rule` objects, dynamic validation, uniqueness checks, authenticated-user constraints, cross-field date logic, and database-dependent rules.
- Use Livewire Form Objects for larger forms, reusable form state, multi-step flows, and dense booking/listing/edit surfaces.
- Reference form object fields in Blade and tests with their form prefix, such as `form.title`.
- Use real-time validation only when useful.
- Prefer `wire:model.live.blur` for text fields that genuinely need early validation feedback.
- Avoid live validation for long textareas.
- Do not use `translate: false` for user-facing validation messages.
- Do not hard-code validation messages or attribute labels in `message:`, `as:`, `messages()`, `validationAttributes()`, `$this->addError()`, or custom validators; use translation keys or Laravel validation language files.
- Use `assertHasErrors()` and rule-specific validation assertions in Livewire tests.
- Use wire:model.blur for normal text fields.
- Use wire:model.change for selects, checkboxes, and radios.
- Use wire:model.live.debounce.500ms or wire:model.live.debounce.750ms only for search/autocomplete.
- Never use live model binding for long textareas.
- Never keep huge arrays in public Livewire properties.
- Use compact DTO arrays for cards and summaries.
- Use `#[Computed]` for derived display data, query-backed DTOs, and values read multiple times in one render or action.
- Access computed properties in Blade through `$this`, such as `$this->results`.
- Do not use `#[Computed]` on Livewire Form Objects.
- Remember normal computed properties are memoized only for one Livewire request; they are recalculated on the next update.
- Use `unset($this->computedName)` after an action mutates data already read by that computed property.
- Use `#[Computed(persist: true)]` or `#[Computed(cache: true)]` only with a clear lifetime, key/invalidation plan, and safe sharing scope.
- Query-backed computed properties must still use selected columns, eager loading, pagination, scopes, and indexes.
- Use `WithPagination` in every Livewire component that owns pagination.
- Keep paginated queries in computed methods or concise query methods, not Blade templates.
- Prefer `cursorPaginate()` for large, append-only, or feed-like datasets; use `simplePaginate()` when next/previous is enough; use `paginate()` only when numbered pages or totals are needed.
- Keep URL pagination for shareable search/listing pages; use `WithoutUrlPagination` only for private widgets or embedded panels.
- Call `$this->resetPage()` when filters, sorting, date ranges, tabs, or search state changes.
- Use named paginator `pageName` values when multiple paginated lists appear on one screen.
- Use `links(data: ['scrollTo' => '#selector'])` when pagination should return the user to a below-the-top list.
- Keep custom pagination views translated, mobile-first, and minimal.
- Use `#[Url]` only for small shareable state such as search, filters, sort keys, date/location filters, selected tabs, and pagination-adjacent state.
- Do not put sensitive/private data, access codes, internal notes, payment/provider payloads, large arrays, models, DTOs, or long form bodies into URL-bound properties.
- Prefer compact aliases with `#[Url(as: 'q', except: '')]`, use `except` for clean URLs, and use `keep: true` sparingly.
- Use `history: true` only when the browser Back button should step through previous query values; keep the default replace-state behavior for noisy live search.
- Validate, coerce, and whitelist URL-backed state before applying Eloquent scopes.
- Reset pagination when URL-backed filters, sorting, date ranges, tabs, or search state changes.
- Use `$this->redirect()` or `$this->redirectRoute()` from Livewire actions only after successful validation, authorization, and persistence.
- Prefer `redirectRoute()` with named localized routes for internal UI flows.
- Use `redirectIntended()` only with a safe fallback route or URL.
- Use `navigate: true` on internal redirects only when `wire:navigate` behavior is appropriate for the destination.
- Do not use `redirectAction()` for new UI flows; do not create controllers just to redirect from Livewire.
- Never redirect to raw user-supplied URLs or put sensitive data in redirect URLs.
- Flash post-redirect messages as translation keys/context, not hard-coded visible strings.
- Prefer `data-loading` Tailwind variants for Livewire network-action feedback.
- Use `wire:loading` only for simple show/hide loading indicators where it is clearer than `data-loading` selectors.
- Use `data-loading:*` on the request trigger, `in-data-loading:*` for child label swaps, `has-data-loading:*` for parent styling, and `peer-data-loading:*` for sibling styling when useful.
- Avoid deeply nested `in-data-loading:*` selectors when parent and child components can load at the same time.
- Every visible loading label must use translation keys.
- Loading states must not replace backend validation, authorization, idempotency, transactions, or duplicate-submit protection.
- Use skeletons for deferred network content.
- Use optimistic UI only where the rollback path is safe and obvious.
- Use wire:navigate for internal links where appropriate.
- Use lazy loading for below-the-fold sections.
- Read `docs/LIVEWIRE_4_REFERENCE.md` before using a Livewire 4 feature covered by user-provided official docs.
- Use `lazy` for below-the-fold child components and `defer` for secondary visible child components that should load after the first render.
- For class-based lazy/deferred child components, use a `placeholder()` method and keep the placeholder root element type the same as the final component root.
- Prefer scalar IDs, filters, booleans, and compact strings over full models or large arrays in lazy/deferred component props.
- Bundle lazy/defer requests only for many similar components with similar load cost; otherwise keep Livewire's default isolated parallel requests.
- Use `Livewire::withoutLazyLoading()` in tests when assertions need final lazy-loaded content.
- Use `@island` for isolated expensive or independent update regions inside one component when it improves mobile performance without requiring a reusable child component.
- Use `@island(lazy: true)` or `@island(defer: true)` with `@placeholder` for expensive below-the-fold or post-load content.
- Do not put `@island` inside loops or conditionals; put loops/conditionals inside the island and expose state through component properties or computed properties.
- Do not rely on template-local variables or `@php` inside islands.
- Use named islands with `wire:island`, `wire:island.append`, or `wire:island.prepend` only for focused refresh/load-more/feed behavior.
- Use WithFileUploads only inside upload-focused components.
- Do not name Livewire upload methods or properties `upload`; Livewire reserves that term.
- Keep upload public properties small: one temporary file or a bounded array of temporary files.
- Validate files by size, type, and dimensions.
- Validate authorization before storing uploaded files.
- Use `temporaryUrl()` only for image previews and keep previews thumbnail-sized.
- Use `data-loading`, scoped `wire:loading wire:target`, upload progress events, and `$cancelUpload('property')` only where they improve mobile upload clarity.
- Store final files through Laravel filesystem APIs or project media services, persist only path/metadata, and reset temporary upload properties after success.
- Do not introduce S3 or `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK` without explicit infrastructure approval plus config, env, docs, and tests.
- Test uploads with `Storage::fake()`, `UploadedFile::fake()`, success assertions, invalid-file assertions, and authorization failures.
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
