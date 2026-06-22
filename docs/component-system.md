# Component System

This project uses a Laravel-first component system built on Blade, Tailwind CSS v4, Livewire, and Flux Pro.

## Current State

- Blade is available.
- Tailwind CSS v4 is configured in `resources/css/app.scss`.
- Vite is configured in `vite.config.js`.
- Livewire `4.3.1` is installed.
- Flux UI Pro `2.14.1` is installed.
- Flux runtime directives are wired in `resources/views/components/layouts/app.blade.php`.
- The welcome view uses Flux components.

## Target Layers

Use these layers:

1. Base layout: shared HTML shell, Vite assets, Flux appearance/scripts, navigation regions.
2. Flux primitives: buttons, inputs, badges, cards, modals, tabs, tables, nav, tooltips, and form controls.
3. App components: domain-specific Blade wrappers around repeated Flux compositions.
4. Page views: route-specific presentation with preloaded data only.

## Livewire 4 Reference

Livewire framework documentation reviewed for this project lives in [LIVEWIRE_4_REFERENCE.md](LIVEWIRE_4_REFERENCE.md). Read it before adding or changing Livewire features that touch navigation, loading, re-render boundaries, polling, lazy/deferred content, nested components, or other documented Livewire 4 APIs.

Do not copy official Livewire examples that use inline PHP or view-based components directly into this project. Convert them to class components under `app/Livewire/...` with matching Blade views under `resources/views/livewire/...`.

### Computed Properties

Use Livewire `#[Computed]` for derived values that belong to a class component.

- Use computed properties for query-backed DTOs, derived display state, and values read multiple times in one render or action.
- Access computed properties in Blade with `$this->propertyName`.
- Do not use `#[Computed]` inside Livewire Form Objects.
- Normal computed properties are memoized only for one Livewire request, so expensive queries still need selected columns, eager loading, indexes, pagination, or Laravel cache.
- If an action mutates data already read by a computed property, clear it with `unset($this->propertyName)`.
- Use `#[Computed(persist: true)]` or `#[Computed(cache: true)]` only with explicit cache lifetime, cache key/invalidation, and safe sharing scope.
- Keep computed return values compact for mobile; avoid raw model graphs and unbounded collections.
- Do not copy official examples that use `Model::all()` or inline templates into project components.

### Validation

Use Livewire validation in class components and form objects, not in Blade templates.

- Use `#[Validate]` for simple static property rules, and still call `$this->validate()` before persisting.
- Use Livewire Form Objects for larger forms, reusable form state, and multi-step booking/listing flows.
- Use a `rules()` method when validation needs Laravel `Rule` objects, dynamic values, uniqueness checks, cross-field logic, or database state.
- Use `#[Validate(..., onUpdate: false)]` when automatic update-time validation would be too chatty.
- Use real-time validation sparingly; prefer `wire:model.live.blur` for text fields that need early feedback.
- Avoid live validation on long textareas.
- Keep custom validation messages and attribute names translated through Laravel language files or semantic translation keys.
- Test validation with `assertHasErrors()`, rule-specific assertions, and form object keys such as `form.title`.

### File Uploads

Use Livewire uploads only in upload-focused class components.

- Add `WithFileUploads` only to components that own a file input.
- Never name a method or property `upload`; Livewire reserves that term.
- Keep upload state small: one temporary file or a bounded array.
- Validate type, size, dimensions, and authorization before storage.
- Use `temporaryUrl()` only for image previews, and keep previews thumbnail-sized.
- Use `data-loading`, scoped `wire:loading wire:target`, progress events, and cancel buttons only when they improve mobile upload clarity.
- Store files through Laravel filesystem/project media services and persist only path/metadata records.
- Do not introduce S3 temporary uploads without explicit infrastructure approval.
- Test uploads with `Storage::fake()`, `UploadedFile::fake()`, success assertions, invalid-file assertions, and authorization failures.

### Pagination

Use Livewire pagination for repeatable lists and keep list data out of public properties.

- Add `WithPagination` to components that own paginated lists.
- Keep paginated queries in computed methods or concise query methods.
- Prefer `cursorPaginate()` for large, feed-like, append-only, or public result lists.
- Use `simplePaginate()` when next/previous controls are enough.
- Use full `paginate()` only when numbered pages or total counts are useful.
- Call `$this->resetPage()` when filters, sorting, dates, tabs, or search state changes.
- Use named paginator `pageName` values when multiple paginators appear on the same screen.
- Keep URL pagination for shareable search/listing pages; use `WithoutUrlPagination` only for private widgets.
- Use `links(data: ['scrollTo' => '#selector'])` for below-the-top lists.
- Keep custom pagination views translated, mobile-first, and minimal.

### URL Query Parameters

Use Livewire `#[Url]` only for small, shareable page state.

- Use URL state for public search, filters, sort keys, date/location filters, selected content tabs, and pagination-adjacent state.
- Keep URL-bound properties scalar and compact; never store large arrays, DTOs, models, long form bodies, or full selected records in the URL.
- Do not expose sensitive data in query strings, including access codes, private addresses, internal notes, payment/provider payloads, or private dispute details.
- Prefer compact aliases and clean defaults, such as `#[Url(as: 'q', except: '')]`.
- Use `keep: true` only when default/empty query parameters must be visible on first load.
- Use `history: true` only when browser Back should step through previous query values; keep the default replace-state behavior for live search.
- Validate, coerce, and whitelist URL-backed values before applying Eloquent scopes.
- Reset pagination when URL-backed filters, sorting, dates, tabs, or search state changes.
- Test query-string hydration, shareable links, aliases, page resets, and privacy-sensitive omissions.

### Redirecting

Use Livewire redirect helpers from component actions after the action succeeds.

- Prefer `$this->redirectRoute()` with named localized routes for internal UI flows.
- Use `$this->redirect()` for simple known internal paths.
- Use `$this->redirectIntended()` only with a safe fallback.
- Use `navigate: true` only for internal destinations where SPA-like navigation improves the mobile UX.
- Do not use `redirectAction()` for new UI work; this project should not add controllers just to redirect Livewire pages.
- Never redirect to raw user-supplied URLs.
- Do not include sensitive data, access details, payment/provider payloads, or internal notes in redirect URLs.
- Flash post-redirect messages as translation keys/context and render them through localized UI.
- Test success redirects, failure paths that should not redirect, flash messages, and locale preservation.

### File Downloads

Use Livewire downloads only for small files where the base64 transfer through a Livewire response is acceptable.

- Return `response()->download()` or `Storage::download()` from a Livewire action after authorization.
- Treat `streamDownload()` as collected-before-download in Livewire, not true browser streaming.
- Keep public component state to IDs and compact metadata; never expose storage paths or file contents.
- Resolve files through models, policies, and services/actions.
- Avoid Livewire downloads for large media, archives, or heavy reports; use a dedicated named download route/endpoint with policy-backed service/action logic when needed.
- Use ASCII-safe filenames and avoid private data in filenames.
- Add loading feedback for slow downloads, but keep backend authorization and idempotency authoritative.
- Test successful downloads with `assertFileDownloaded()` and blocked downloads with `assertNoFileDownloaded()`.

### Teleport

Use Livewire `@teleport` only when custom overlay content needs to escape parent stacking, overflow, or nested-dialog contexts.

- Prefer Flux modal, popover, dropdown, toast, and drawer primitives when they already solve the UI need.
- Use `@teleport('body')` as the default for custom modal, bottom-sheet, popover, and toast shells that need body-level placement.
- Teleport targets must be stable CSS selectors outside the current Livewire component, usually `body` or a documented layout-level root such as `#modal-root`.
- Never teleport into another element inside the same component.
- Put exactly one root element inside each `@teleport` block.
- Do not put `@teleport` inside large loops or use it to keep huge hidden modal DOM in the page. Render one active overlay by selected ID/state or extract a focused child component.
- Keep teleported content translated, mobile-first, small in DOM size, and backed by normal Livewire authorization, validation, and tests.

### Wire Bind

Use Livewire `wire:bind:{attribute}` for lightweight client-side attribute changes that should react without a full component re-render.

- Good uses: character-limit classes, selected-state classes, state-driven disabled buttons, ARIA state, small `data-*` counters, and bounded style tokens.
- Keep expressions short. Move business rules, authorization, route generation, translation lookup, and complex formatting to the Livewire class, computed properties, DTOs, or services.
- Prefer static, enumerated class values. Do not build unbounded Tailwind class names from user data.
- Bind styles only from sanitized, bounded values. Do not pass raw user input into `wire:bind:style`.
- Use `wire:bind:disabled` only for state-driven client interactivity. Server actions must still validate, authorize, and protect against duplicate submissions.
- Prefer `data-loading` or `wire:loading.attr="disabled"` for request-in-flight disabling.
- Use `wire:bind:href` only with component-generated or whitelisted URLs, preferably named localized routes.
- Never bind access codes, exact private addresses, payment/provider payloads, internal notes, storage paths, or other sensitive values into client-readable attributes.

### Wire Click

Use Livewire `wire:click` for user-triggered component actions, not as a replacement for normal links or form submissions.

- Put click behavior in public Livewire action methods and delegate real business logic to services/actions.
- Use `type="button"` on buttons that are not form submissions.
- Prefer buttons for actions. If an action is rendered as an `<a>` tag, use `wire:click.prevent`.
- Use named route links with `wire:navigate` for navigation.
- Pass compact scalar IDs only. Treat click parameters like request input: re-load models, authorize, validate state, and check idempotency before mutating data.
- Pair risky actions with translated confirmation UI, but keep backend protection authoritative.
- Use `data-loading`, scoped `wire:loading`, and stable disabled-looking states for click feedback.
- Use `.renderless` only for side effects that do not need UI updates.
- Use `.preserve-scroll` for load-more and in-place list/panel actions.
- Use `.async` only for independent idempotent actions; do not use it for booking, payment, deposit, checkout, inventory, or same-record mutations.
- Test success, authorization failure, tampered IDs, duplicate submission, and translated feedback.

### Wire Submit

Use Livewire `wire:submit` on forms for form submission. Livewire automatically prevents default form submission and disables submit buttons/read-only inputs during the request.

- Prefer `wire:submit="save"` on `<form>` over `wire:click` on submit buttons.
- Use `type="submit"` for the primary submit button and `type="button"` for secondary actions.
- Validate and authorize in the submit action before persistence.
- Use Livewire Form Objects for larger forms and multi-step flows.
- Delegate persistence, pricing, availability, uploads, workflow transitions, and side effects to services/actions.
- Treat submit parameters as untrusted; re-load models, authorize, validate state, and check idempotency.
- Keep automatic disabled/readonly behavior as UX only; backend duplicate-submit protection remains required.
- Use translated loading/success/error feedback and stable layout on mobile.
- Use `.preserve-scroll` for inline forms where needed; avoid `.async` for ordered workflows.
- Test success, validation, authorization/tampering, duplicate submit, redirect/state, and translated feedback.

### Wire Model

Use Livewire `wire:model` to bind form input values to component properties. By default, `wire:model` does not send a network request on every input update; values synchronize with the server when an action runs, such as `wire:submit` or `wire:click`.

- Use plain `wire:model` when submit/action-time synchronization is enough.
- Use `wire:model.blur` for normal text fields and `wire:model.change` for selects, checkboxes, radios, switches, and bounded option controls.
- Use `wire:model.enter` for compact fields where Enter is the intended commit point.
- Use `wire:model.live` only for real-time validation, search, autocomplete, dependent filters, or small previews that need server updates while editing.
- Prefer `wire:model.live.debounce.500ms` or `wire:model.live.debounce.750ms` for search/autocomplete; `.live` has a 150ms default debounce.
- Avoid live updates for long textareas such as messages, reviews, complaints, notes, rules, descriptions, and dispute details.
- Use `.number` and `.boolean` as helper casts only; still validate and normalize server-side.
- Use `.fill`, `.deep`, `.preserve-scroll`, `.debounce.Xms`, and `.throttle.Xms` only for deliberate cases documented in `docs/LIVEWIRE_4_REFERENCE.md`.
- For dependent selects, add a stable `wire:key` to the changing select so Livewire resets and refreshes the value when options change.
- Bind Form Object fields with dot notation such as `wire:model.blur="form.title"`.
- Do not put content inside a bound `<textarea>`; initialize component state instead.
- Do not copy official examples that query in Blade loops or use `Model::all()` for options. Preload option DTOs through the Livewire class, computed properties, or services.
- Keep bound state small and safe: IDs, enum-like strings, booleans, dates, and bounded arrays.
- Never bind access codes, payment/provider payloads, private dispute details, internal notes, full models, or DTO graphs into client-readable state.

### Loading States

Use Livewire's automatic `data-loading` attribute as the default loading-state styling hook for network actions.

- Use `data-loading:*` on buttons, links, and form controls that trigger Livewire requests.
- Use `in-data-loading:*` for child label swaps inside a loading trigger.
- Use `has-data-loading:*` when a parent section should visually react to a loading child.
- Use `peer-data-loading:*` for sibling feedback when it keeps markup simple.
- Use `wire:loading` only for simple show/hide loading indicators where the directive is clearer than Tailwind variants.
- Keep loading states translated, subtle, and layout-stable for old phones and slow 3G.
- Do not hide slow queries behind loading states; fix the query, index, pagination, or payload size first.

### Islands

Use Livewire Islands for isolated update regions inside a single component when they improve mobile performance without needing a reusable nested component.

- Use `@island` for expensive computed sections, independent dashboard panels, counters, feeds, badges, and other regions that should not re-render with the parent.
- Use `@island(lazy: true)` for below-the-fold expensive content and include `@placeholder` for a stable skeleton/loading state.
- Use `@island(defer: true)` when the region should load immediately after the first page render.
- Use named islands plus `wire:island`, `wire:island.append`, or `wire:island.prepend` for targeted refreshes and load-more/feed behavior.
- Do not put `@island` inside loops or conditionals. Move loops/conditionals inside the island and use component properties or computed properties.
- Do not rely on template-local variables inside islands. Islands can access Livewire component properties and methods.
- Avoid state races: do not let the root component and multiple islands mutate the same public state concurrently.
- Prefer nested Livewire components instead of islands when the UI is reusable, needs its own lifecycle, owns complex state, or needs a separate authorization/validation/upload boundary.

### Lazy And Deferred Child Components

Use Livewire lazy/defer for whole child components when a reusable component boundary is useful and slow data should not block the first render.

- Use `lazy` for below-the-fold child components such as review lists, similar sleeping places, compatibility summaries, and large dashboard panels.
- Use `defer` for secondary child components that should load immediately after the first render instead of waiting for viewport visibility.
- For class components, use a `placeholder()` method instead of Blade `@placeholder`; keep the placeholder root element type the same as the final component root element.
- Keep placeholder views query-free, translated, and skeleton-friendly.
- Pass scalar IDs, filters, and compact state into lazy/deferred components instead of full models or large arrays.
- Keep Livewire's default isolated parallel requests unless there are many similar components with similar load cost; then use `lazy.bundle`, `defer.bundle`, `#[Lazy(bundle: true)]`, or `#[Defer(bundle: true)]`.
- Do not lazy-load core booking, payment, access/privacy, or first-screen search actions.
- Use `Livewire::withoutLazyLoading()` in tests when assertions need final lazy-loaded content.

## Blade Components

- Use anonymous Blade components for simple app wrappers.
- Use class-based components only when PHP logic is meaningful.
- Put `@props(...)` at the top of every anonymous component.
- Merge attributes on the root element with `$attributes->merge(...)`.
- Keep data queries and business decisions out of component templates.

## Flux Usage Rules

- Prefer Flux components over custom Tailwind markup for common controls.
- Prefer Flux props before class overrides.
- Use `variant`, `size`, `icon`, `icon:trailing`, `kbd`, `label`, and similar component props where supported.
- Use shorthand form components for simple fields, and long-form `flux:field` composition when layout requires it.
- Use Flux layout components for app shell work when they fit the page structure.
- Publish Flux components only when project-level customization is unavoidable.

## Tailwind CSS v4

Keep Tailwind configuration CSS-first.

The current SCSS entrypoint starts with:

```css
@use 'tailwindcss';
```

The Flux integration uses:

```css
@import '../../vendor/livewire/flux/dist/flux.css';
@import '../../vendor/livewire/flux-pro/dist/editor.css';
@custom-variant dark (&:where(.dark, .dark *));
```

Preserve existing `@source` and `@theme` declarations unless a verified Flux change requires a specific update.
Tailwind v4 directives are processed through `@tailwindcss/postcss` after Sass compilation; keep `postcss.config.mjs` aligned with `resources/css/app.scss`.

## Dark Mode And Theme

- Let Flux manage appearance with `@fluxAppearance` unless the project explicitly decides otherwise.
- Define project accent colors with Flux/Tailwind CSS variables in `resources/css/app.scss`.
- Do not create a separate Tailwind config file unless the project has a verified Tailwind v4 reason.

## Testing

For component work:

- Render pages with feature tests where possible.
- Use Laravel view/component test helpers for reusable components.
- Use `npm run build` after changing CSS, Vite, layout assets, or Flux integration.
