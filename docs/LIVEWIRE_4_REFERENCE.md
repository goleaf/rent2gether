# Livewire 4 Reference

This file records Livewire 4 documentation links reviewed for `rent2gether`.
When the user provides a new `https://livewire.laravel.com/docs/4.x/...` link, read it, verify with Laravel Boost or Context7 when useful, and update this file before implementing related Livewire changes.

Project override: official examples may use view-based or single-file components, but this project uses Livewire class components only. Do not copy inline PHP examples into Blade. Put PHP state, computed properties, authorization, validation, and actions in `app/Livewire/...` classes, and keep views under `resources/views/livewire/...`.

## Reviewed Sources

| Source | Reviewed | Project status |
| --- | --- | --- |
| <https://livewire.laravel.com/docs/4.x/islands> | 2026-06-22 | Binding for isolated Livewire update regions and mobile performance work. |
| <https://livewire.laravel.com/docs/4.x/lazy> | 2026-06-22 | Binding for lazy/deferred Livewire components, placeholders, bundling, and lazy-testing rules. |
| <https://livewire.laravel.com/docs/4.x/navigate> | 2026-06-22 | Binding for Livewire Navigate SPA-like visits, redirects, prefetching, persisted elements, active links, JavaScript hooks, script evaluation, analytics, and progress bar configuration. |
| <https://livewire.laravel.com/docs/4.x/wire-navigate> | 2026-06-22 | Binding for the `wire:navigate` directive, `.hover` prefetching, and `data-current` active-link styling. |
| <https://livewire.laravel.com/docs/4.x/wire-current> | 2026-06-22 | Binding for `wire:current`, active link class application, partial matching, `.exact`, `.strict`, and troubleshooting requirements. |
| <https://livewire.laravel.com/docs/4.x/wire-cloak> | 2026-06-22 | Binding for `wire:cloak`, initial-load cloaking, dynamic `wire:show` flicker prevention, and the no-modifier reference. |
| <https://livewire.laravel.com/docs/4.x/loading-states> | 2026-06-22 | Binding for `data-loading`, Tailwind loading variants, and `wire:loading` fallback rules. |
| <https://livewire.laravel.com/docs/4.x/wire-loading> | 2026-06-22 | Binding for `wire:loading`, `wire:target`, `.remove`, `.class`, `.attr`, `.delay`, display modifiers, and targeted loading indicators. |
| <https://livewire.laravel.com/docs/4.x/validation> | 2026-06-22 | Binding for `#[Validate]`, form objects, real-time validation, custom rules, and validation tests. |
| <https://livewire.laravel.com/docs/4.x/uploads> | 2026-06-22 | Binding for `WithFileUploads`, temporary files, previews, progress, cancellation, configuration, and upload tests. |
| <https://livewire.laravel.com/docs/4.x/pagination> | 2026-06-22 | Binding for `WithPagination`, URL pagination, page resets, named paginators, cursor pagination, and pagination views. |
| <https://livewire.laravel.com/docs/4.x/url> | 2026-06-22 | Binding for `#[Url]`, query aliases, nullable URL state, `except`, `keep`, history behavior, `queryString()`, and trait hooks. |
| <https://livewire.laravel.com/docs/4.x/computed-properties> | 2026-06-22 | Binding for `#[Computed]`, request memoization, memo busting, persisted/global computed cache, and computed-property view access. |
| <https://livewire.laravel.com/docs/4.x/redirecting> | 2026-06-22 | Binding for Livewire action redirects, `redirectRoute()`, `redirectIntended()`, `redirectAction()`, flash messages, and `navigate: true` redirects. |
| <https://livewire.laravel.com/docs/4.x/downloads> | 2026-06-22 | Binding for Livewire file downloads, Laravel download responses, `Storage::download()`, `streamDownload()`, and download assertions. |
| <https://livewire.laravel.com/docs/4.x/teleport> | 2026-06-22 | Binding for `@teleport`, modal/overlay DOM placement, CSS selector targets, outside-component targets, and single-root teleport content. |
| <https://livewire.laravel.com/docs/4.x/wire-bind> | 2026-06-22 | Binding for `wire:bind:{attribute}` reactive client-side attribute binding, including class, style, href, disabled, and data attributes. |
| <https://livewire.laravel.com/docs/4.x/wire-click> | 2026-06-22 | Binding for `wire:click`, action parameters, link `.prevent`, `.renderless`, `.preserve-scroll`, `.async`, and click modifiers. |
| <https://livewire.laravel.com/docs/4.x/wire-submit> | 2026-06-22 | Binding for `wire:submit`, form actions, automatic `preventDefault()`, automatic submit disabling/readonly inputs, and submit modifiers. |
| <https://livewire.laravel.com/docs/4.x/wire-model> | 2026-06-22 | Binding for `wire:model`, default update timing, `.live`, `.blur`, `.change`, `.enter`, casts, nested properties, dependent selects, and event propagation. |
| <https://livewire.laravel.com/docs/4.x/wire-dirty> | 2026-06-22 | Binding for `wire:dirty`, `$dirty`, targeted dirty indicators, `.remove`, and `.class` dirty styling. |
| <https://livewire.laravel.com/docs/4.x/wire-confirm> | 2026-06-22 | Binding for `wire:confirm`, browser confirmation, `.prompt`, destructive-action UX, and translated confirmation copy. |
| <https://livewire.laravel.com/docs/4.x/wire-transition> | 2026-06-22 | Binding for View Transitions, transition names, typed transitions, reduced-motion behavior, and `#[Transition]` coordination. |
| <https://livewire.laravel.com/docs/4.x/wire-init> | 2026-06-22 | Binding for post-initial-render component actions and non-critical deferred initialization. |
| <https://livewire.laravel.com/docs/4.x/wire-intersect> | 2026-06-22 | Binding for viewport-triggered actions, `:enter`, `:leave`, `.once`, `.half`, `.full`, `.threshold`, and `.margin`. |
| <https://livewire.laravel.com/docs/4.x/wire-poll> | 2026-06-22 | Binding for conservative polling, `.visible`, interval modifiers, background behavior, and island-scoped urgent panels. |
| <https://livewire.laravel.com/docs/4.x/wire-offline> | 2026-06-22 | Binding for offline banners, class/attribute modifiers, and mobile connectivity feedback. |
| <https://livewire.laravel.com/docs/4.x/wire-ignore> | 2026-06-22 | Binding for DOM regions managed outside Livewire, `.self`, third-party widgets, and safe synchronization boundaries. |
| <https://livewire.laravel.com/docs/4.x/wire-ref> | 2026-06-22 | Binding for JavaScript element references, `$refs`, `@script`, and cleanup requirements. |
| <https://livewire.laravel.com/docs/4.x/wire-replace> | 2026-06-22 | Binding for whole-element replacement when morphing would break external DOM ownership or browser state. |
| <https://livewire.laravel.com/docs/4.x/wire-show> | 2026-06-22 | Binding for CSS display toggling, `wire:cloak` pairing, and small conditional UI. |
| <https://livewire.laravel.com/docs/4.x/wire-sort> | 2026-06-22 | Binding for drag sorting, `wire:sort:item`, groups, handles, ignore regions, persistence, and authorization. |
| <https://livewire.laravel.com/docs/4.x/wire-stream> | 2026-06-22 | Binding for streamed DOM updates, append/replace behavior, and cautious use in lightweight progressive output. |
| <https://livewire.laravel.com/docs/4.x/wire-text> | 2026-06-22 | Binding for client-side text updates from component state without a full morph. |
| <https://livewire.laravel.com/docs/4.x/attribute-async> | 2026-06-22 | Binding for `#[Async]`, `.async`, independent idempotent side effects, and workflow-safety restrictions. |
| <https://livewire.laravel.com/docs/4.x/attribute-computed> | 2026-06-22 | Binding for the dedicated `#[Computed]` attribute reference in addition to the feature overview. |
| <https://livewire.laravel.com/docs/4.x/attribute-defer> | 2026-06-22 | Binding for `#[Defer]` as the class-level version of deferred component loading. |
| <https://livewire.laravel.com/docs/4.x/attribute-isolate> | 2026-06-22 | Binding for `#[Isolate]`, request isolation, and avoiding unrelated slow component blocking. |
| <https://livewire.laravel.com/docs/4.x/attribute-js> | 2026-06-22 | Binding for `#[Js]`, JavaScript actions, and browser-only interactions. |
| <https://livewire.laravel.com/docs/4.x/attribute-json> | 2026-06-22 | Binding for `#[Json]`, safe JSON serialization, and public-state payload constraints. |
| <https://livewire.laravel.com/docs/4.x/attribute-layout> | 2026-06-22 | Binding for `#[Layout]` on full-page components and project layout consistency. |
| <https://livewire.laravel.com/docs/4.x/attribute-lazy> | 2026-06-22 | Binding for `#[Lazy]` as the class-level version of lazy component loading. |
| <https://livewire.laravel.com/docs/4.x/attribute-locked> | 2026-06-22 | Binding for `#[Locked]`, tamper-resistant public IDs, and the rule that locking is not authorization. |
| <https://livewire.laravel.com/docs/4.x/attribute-modelable> | 2026-06-22 | Binding for `#[Modelable]`, parent-child `wire:model` APIs, and small form-control state. |
| <https://livewire.laravel.com/docs/4.x/attribute-on> | 2026-06-22 | Binding for `#[On]`, event listeners, dynamic event names, and simple component boundaries. |
| <https://livewire.laravel.com/docs/4.x/attribute-reactive> | 2026-06-22 | Binding for `#[Reactive]`, reactive child props, and payload caution. |
| <https://livewire.laravel.com/docs/4.x/attribute-renderless> | 2026-06-22 | Binding for `#[Renderless]`, no-render side effects, and performance-focused actions. |
| <https://livewire.laravel.com/docs/4.x/attribute-session> | 2026-06-22 | Binding for `#[Session]`, session-backed component state, and compact non-sensitive persistence. |
| <https://livewire.laravel.com/docs/4.x/attribute-title> | 2026-06-22 | Binding for `#[Title]`, full-page component titles, and translated title rules. |
| <https://livewire.laravel.com/docs/4.x/attribute-transition> | 2026-06-22 | Binding for `#[Transition]`, typed transitions, and skip behavior. |
| <https://livewire.laravel.com/docs/4.x/attribute-url> | 2026-06-22 | Binding for the dedicated `#[Url]` attribute reference in addition to the URL feature overview. |
| <https://livewire.laravel.com/docs/4.x/attribute-validate> | 2026-06-22 | Binding for the dedicated `#[Validate]` attribute reference in addition to the validation feature overview. |
| <https://livewire.laravel.com/docs/4.x/directive-island> | 2026-06-22 | Binding for the `@island` directive reference in addition to the Islands feature overview. |
| <https://livewire.laravel.com/docs/4.x/directive-placeholder> | 2026-06-22 | Binding for `@placeholder` inside lazy/defer/skip islands and placeholder skeleton rules. |
| <https://livewire.laravel.com/docs/4.x/directive-persist> | 2026-06-22 | Binding for `@persist`, Navigate-preserved DOM, layout-only persistence, and scroll containers. |
| <https://livewire.laravel.com/docs/4.x/directive-teleport> | 2026-06-22 | Binding for the dedicated `@teleport` directive reference in addition to the Teleport feature overview. |
| <https://livewire.laravel.com/docs/4.x/morphing> | 2026-06-22 | Binding for Livewire morphing, stable DOM structure, keys, and when to use ignore/replace boundaries. |
| <https://livewire.laravel.com/docs/4.x/hydration> | 2026-06-22 | Binding for hydration/dehydration, public property payloads, and client-visible state constraints. |
| <https://livewire.laravel.com/docs/4.x/understanding-nesting> | 2026-06-22 | Binding for nested components, prop boundaries, keys, reactive props, and update isolation. |
| <https://livewire.laravel.com/docs/4.x/troubleshooting> | 2026-06-22 | Binding for DOM-diff troubleshooting, root/key errors, asset/config checks, and diagnostics. |
| <https://livewire.laravel.com/docs/4.x/security> | 2026-06-22 | Binding for Livewire security, public-state trust boundaries, authorization, and locked properties. |
| <https://livewire.laravel.com/docs/4.x/csp> | 2026-06-22 | Binding for CSP-aware Livewire/Alpine usage and strict-script deployment caution. |
| <https://livewire.laravel.com/docs/4.x/javascript> | 2026-06-22 | Binding for `@script`, `$wire`, Livewire hooks, custom directives, cleanup, and Navigate lifecycle. |
| <https://livewire.laravel.com/docs/4.x/synthesizers> | 2026-06-22 | Binding for custom property synthesizers as advanced infrastructure only. |
| <https://livewire.laravel.com/docs/4.x/packages> | 2026-06-22 | Binding for package development guidance, but not a default app feature path. |
| <https://livewire.laravel.com/docs/4.x/contribution-guide> | 2026-06-22 | Binding for upstream contribution context only; no app runtime changes required. |

## Validation

Livewire validation builds on Laravel validation. In this project, validation belongs in Livewire class components, Livewire form objects, services, actions, or form/request-style collaborators. Blade may render errors, but must not own validation rules or business decisions.

### Basic Rules

- Always validate again before persistence with `$this->validate()` or `$this->form->validate()`, even when using `#[Validate]` for real-time feedback.
- Use `#[Validate]` for simple static rules that belong next to a small component property.
- Use `#[Validate(..., onUpdate: false)]` when rules should be colocated but automatic update-time validation would create too many requests or noisy UX.
- Use a `rules()` method for dynamic rules, Laravel `Rule` objects, uniqueness checks, authenticated-user constraints, cross-field date logic, or rules that depend on database state.
- Use Livewire Form Objects for larger forms, reusable form state, multi-step flows, and dense booking/listing/edit surfaces.
- Never use deprecated `#[Rule]`; use `#[Validate]`.

### Form Objects

- Put larger form state in `app/Livewire/Forms/...` classes extending `Livewire\Form`.
- Reference form object fields in Blade with the form property prefix, such as `wire:model.blur="form.title"` and `@error('form.title')`.
- For complex form object rules, define `rules()` inside the form object and call `$this->form->validate()` before the action persists anything.
- When manually adding errors inside a form object, remember Livewire prefixes keys with the parent component form property name.

### Real-Time Validation

Real-time validation requires an update request. Use it sparingly because this project targets old Android devices and slow 3G.

- Prefer `wire:model.live.blur` for text inputs that genuinely need early validation feedback.
- Prefer `wire:model.change` or `wire:model.change.live` for selects, checkboxes, and radios.
- Avoid live validation for long textareas, message bodies, review comments, complaint details, and other long-form text.
- If `rules()` provides the rules but real-time validation is needed for a property, add an empty `#[Validate]` attribute to that property.

### Localization

- Livewire validation messages and attribute names are translated through Laravel by default.
- Do not use `translate: false` for user-facing validation messages in this project.
- Do not hard-code English or Russian phrases in `message:`, `as:`, `messages()`, `validationAttributes()`, `$this->addError()`, or custom validators.
- Use semantic translation keys or Laravel validation language files for custom validation copy.

### Manual Errors And Advanced Hooks

- Use `$this->addError()` for domain/service validation that cannot be expressed cleanly as field rules.
- Use `$this->resetValidation()` when a user changes context and stale errors no longer apply.
- Use `$this->withValidator()` for cross-field or after-validation checks that need the Validator instance.
- Custom validators are allowed when they throw Laravel `ValidationException`, but prefer Livewire/Laravel validation APIs first.
- Do not create public properties or methods named `rules`, `messages`, `validationAttributes`, or `validationCustomValues` unless they intentionally customize validation.

### Testing

- Every Livewire form action must have validation tests for required fields, invalid values, authorization failures when relevant, and the successful path.
- Use `assertHasErrors()` for expected validation failures.
- Use rule-specific assertions such as `assertHasErrors(['field' => ['required']])` when the rule matters.
- Use form object keys in assertions, such as `assertHasErrors(['form.title' => ['required']])`.
- Add locale coverage when validation copy, attribute labels, or custom messages are part of the user experience.

## File Uploads

Livewire uploads use temporary files before the component validates and stores them. In this project, keep uploads in upload-focused class components and store only final path/metadata records after validation.

### Component Rules

- Use `Livewire\WithFileUploads` only in components that actually own file upload behavior.
- Do not name a method or property `upload`; Livewire reserves that term for its upload internals. Use names like `savePhoto`, `storeMedia`, `attachDocument`, or `saveAvatar`.
- Keep upload public properties small: a single temporary file property or a bounded array of temporary files.
- Reset upload properties after successful storage so temporary state does not linger.
- Use services/actions for storage naming, media records, resizing, privacy checks, and domain decisions.

### Validation

- Validate file type, max size, and dimensions where relevant before storage.
- Use array rules such as `photos.*` for multiple uploads.
- Use `accept` attributes only as browser hints; server-side validation is still required.
- Keep validation errors translated through Laravel validation language files or semantic translation keys.
- Do not rely on Livewire's default temporary upload validation alone. Livewire defaults temporary uploads to `file|max:12288`, but feature-level validation must still match the product rule.

### Preview And UX

- Use `$file->temporaryUrl()` only for image previews; Livewire restricts temporary preview URLs to image MIME types.
- Keep previews thumbnail-sized and layout-stable on old phones.
- Use `data-loading` or scoped `wire:loading wire:target="photo"` for upload status.
- Use Livewire upload progress events only when a progress bar materially helps the user.
- Provide cancel behavior with `$cancelUpload('property')` for large or slow uploads.
- Avoid heavy JavaScript upload integrations unless a third-party uploader is explicitly required.

### Storage And Configuration

- Store final files through Laravel filesystem APIs such as `store()`, `storeAs()`, `storePublicly()`, or project media services.
- Do not store original client filenames as trusted filenames; keep them only as optional display metadata.
- Do not introduce S3 or a different `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK` unless the user explicitly requests that infrastructure and config/env/docs are updated together.
- If Livewire temporary upload config is changed, document global rules, middleware/throttle, and temporary directory behavior.
- Local temporary upload cleanup is handled by Livewire. S3 cleanup commands apply only if S3 temporary uploads are deliberately adopted.

### Testing

- Use `Storage::fake()` and `UploadedFile::fake()` for upload tests.
- Test successful storage and persisted metadata.
- Test invalid type, file size, dimensions when relevant, and unauthorized access.
- Test multiple upload validation with keys such as `photos.*`.

## Pagination

Livewire pagination keeps users on the same page while updating the query string and list content. In this project, every large or repeatable list must use pagination, cursor pagination, simple pagination, or an explicit load-more pattern.

### Component Rules

- Use `Livewire\WithPagination` in every component that owns Livewire pagination.
- Keep paginated queries in computed methods or concise query methods, not Blade templates.
- Never load all rows into Livewire public properties and then slice them in PHP.
- Use selected columns, eager loads, aggregates, and indexes before adding pagination UI.
- Keep page size small for mobile, usually 10-30 rows depending on card density.
- Use `data-loading` on pagination controls for visual feedback.

### Pagination Type

- Use `cursorPaginate()` for large, append-only, or feed-like datasets such as search results, messages, notifications, reviews, favorites, and activity lists.
- Use `simplePaginate()` when next/previous navigation is enough and total page counts are not needed.
- Use `paginate()` only when numbered pages or total counts are a real user need.
- Cursor pagination needs stable ordering and matching indexes. For SQLite, document and add the composite indexes used by the cursor order and filters.

### URL And State

- By default, Livewire stores the current page in the URL query string. Keep this for shareable search and listing result pages.
- Use `WithoutUrlPagination` only for private widgets, dashboards, or embedded panels where page state should not be shareable.
- Call `$this->resetPage()` when search, filters, sorting, date range, locale-sensitive filters, or tab state changes.
- Use named paginators with `pageName` when one page contains multiple paginated lists.
- When navigating named paginators programmatically, pass `pageName` to `setPage`, `resetPage`, `nextPage`, or `previousPage`.

### Scroll And Views

- Use `links(data: ['scrollTo' => '#selector'])` when the paginated list is below the top of the page.
- Use `links(data: ['scrollTo' => false])` only when preserving scroll is less confusing than jumping to the list.
- Prefer default Tailwind pagination views unless project-level mobile UI requires a custom view.
- Do not switch Livewire pagination to Bootstrap.
- If custom pagination views are introduced, every visible label must use translation keys and controls must stay mobile-first.

### Hooks And Tests

- Use `updatingPage`, `updatedPage`, named hooks, or `updatingPaginators`/`updatedPaginators` only for focused side effects such as analytics, scroll state, or cache cleanup.
- Test that initial lists render with pagination.
- Test that filters and sorts reset pagination to the first page.
- Test named paginators independently when multiple paginators exist on one screen.
- Test cursor/simple pagination on the list surfaces that rely on it, including empty and load-more states.

## URL Query Parameters

Livewire `#[Url]` stores small component properties in the browser query string and initializes those properties from existing query parameters on page load. Use it for shareable, bookmarkable state, not as component storage.

### Use URL State For

- Public search and listing filters users should bookmark or share.
- Sort keys, selected content tabs, date/location filters, and compact status filters that shape list content.
- Review, message, notification, favorite, saved-search, and waitlist filters where the URL should restore the same view.
- Pagination-adjacent state that must stay compatible with Livewire URL pagination.

### Do Not Put In URL State

- Access codes, exact private address details before disclosure is allowed, internal notes, payment/provider payloads, dispute private data, or any other sensitive values.
- Large arrays, DTOs, Eloquent models, full selected records, media metadata batches, or long form bodies.
- Ephemeral drawer/modal UI state unless restoring it from a shared URL is truly useful.
- Values that have not been sanitized or coerced before being applied to Eloquent scopes.

### Attribute Options

- Prefer compact semantic aliases with `as`, such as `#[Url(as: 'q', except: '')]`, when the public query key should be shorter or stable.
- Use `except` to keep URLs clean when the value is empty or equal to the project's default.
- Use nullable property types only when an empty query value such as `?q=` should mean `null`; otherwise prefer explicit defaults like `''` or `'all'`.
- Use `keep: true` sparingly because it makes empty/default query parameters visible on page load.
- Use `history: true` only when the browser Back button should step through previous query values. For noisy live search, keep Livewire's default replace-state behavior.
- Use a `queryString()` method or trait hooks such as `queryStringWithSorting()` when URL options are dynamic or shared across components.

### Project Rules

- Keep URL-bound public properties scalar and small: strings, integers, booleans, enum-like keys, or short date strings.
- Pair URL-backed filters with `wire:model.live.debounce.500ms` or `wire:model.live.debounce.750ms` only for search/autocomplete; use `wire:model.blur` or `wire:model.change` for normal fields.
- Normalize, validate, and whitelist URL-backed values before they reach query scopes.
- Reset pagination when URL-backed search, filters, sorting, date ranges, or tab state changes.
- Keep aliases stable after release so shared links do not break.
- Test initial state from query strings, URL updates after interactions, alias/`except` behavior, page resets, and the absence of sensitive query parameters.

## Computed Properties

Livewire `#[Computed]` turns a component method into a derived property that is memoized for the current component request. Use computed properties for derived display data, query-backed DTOs, and expensive values that may be read multiple times during one render or action.

### Component Rules

- Import `Livewire\Attributes\Computed` in the Livewire class before using `#[Computed]`.
- Access computed properties in Blade through `$this`, such as `$this->results`, not as plain `$results`.
- Computed properties can be accessed inside component methods as `$this->propertyName`.
- Do not use `#[Computed]` on `Livewire\Form` objects. Put computed methods on the parent component or a dedicated service/action instead.
- Use computed properties for derived read state, not for mutable form state or action input.
- Keep computed return values mobile-sized. For lists, return paginated results, cursor paginators, compact DTO arrays, or bounded collections.

### Performance Rules

- Livewire memoizes normal computed properties only for the duration of one component request. The value is recalculated on the next Livewire update.
- Do not treat a computed property as cross-request cache. Use Laravel cache, `#[Computed(persist: true)]`, or `#[Computed(cache: true)]` only when invalidation is designed.
- Use selected columns, eager loading, `withCount`/`withExists`, scopes, indexes, and pagination inside query-backed computed methods.
- Do not copy official examples that use `Model::all()` into this project. That violates the project query rules unless the dataset is deliberately bounded elsewhere.
- Computed properties are not an authorization boundary. Authorize actions and scope queries before returning data.

### Memo And Cache Busting

- If an action mutates data that a computed property already read during the same request, clear the memo with `unset($this->propertyName)`.
- `unset($this->propertyName)` also clears Livewire's persisted computed cache for that property.
- Use `#[Computed(persist: true)]` only for component-instance cache across requests. The default cache lifetime is 3600 seconds; override it with `seconds` when needed.
- Use `#[Computed(cache: true)]` only for data that is safe to share across all component instances. Set an explicit `key` and cache invalidation plan for shared cached data.
- Use cache `tags` only with a cache driver that supports tags and with documented invalidation.

### When To Use

- A query or calculation is needed only when a Blade branch actually renders it.
- The same derived value is read multiple times in one render or action.
- A class component omits view data from `render()` and exposes display data through computed properties.
- The value depends on small public state such as IDs, filters, booleans, or URL-backed keys.

### When Not To Use

- Simple page-refresh persistence for private user state. Use `#[Session]` when the state is simple, user-specific, not shareable by URL, and not computationally expensive.
- Large static lookup values that should be cached by a service or loaded from a small lookup table.
- Long-running business workflows. Put those in services/actions and expose only their compact results.
- Raw Eloquent model graphs that would create large Livewire payloads or hidden N+1 queries.

### Testing

- Test that components render computed data with the expected selected/scoped records.
- Test conditional branches that should not execute expensive computed queries until needed.
- Test actions that mutate computed data and then `unset()` the computed property before re-rendering.
- Test persisted/shared computed cache only when the cache behavior is part of the feature contract.

## Redirecting

Livewire action requests are not normal full-page browser requests. Use Livewire redirect helpers inside component actions so Livewire can perform the redirect on the frontend after the action response.

### Component Rules

- Use `$this->redirect()` for simple path redirects from a Livewire action.
- Prefer `$this->redirectRoute()` for internal application routes so redirects stay route-name based and locale-aware.
- Pass route parameters as the second argument to `redirectRoute()`.
- Use `$this->redirectIntended()` only with a safe default fallback route or URL.
- Use `navigate: true` for internal redirects when `wire:navigate` behavior improves perceived speed and the destination is a normal Livewire/Blade page.
- Return or stop after issuing a redirect when continuing the action would create confusing side effects.

### Project Rules

- Do not create controller actions just to have a redirect target. This project uses Livewire class components and named routes for UI flows.
- Avoid `redirectAction()` for new UI work because it points to controller actions. Use named routes or full-page Livewire components instead.
- Never redirect directly to user-supplied URLs. Whitelist route names or known internal paths to avoid open redirects.
- Do not redirect before validation, authorization, idempotency checks, and persistence/transaction work have succeeded.
- Do not put sensitive payloads, access codes, payment-provider data, or internal notes in redirect URLs.
- Preserve the active locale when redirecting between localized routes.
- Prefer compact route parameters such as IDs or slugs; do not pass model graphs or large query payloads through the URL.

### Flash Messages

- Use Laravel flash data for post-redirect notices.
- Store flash message keys and context where possible, not hard-coded visible strings.
- Render flash messages through translation keys in the destination Blade/Livewire page.
- Keep flash messages short, friendly, and mobile-safe.
- Do not flash private dispute/payment/access details into a general UI surface.

### Testing

- Test successful actions assert the expected redirect target.
- Test validation and authorization failures do not redirect.
- Test flash message keys/context are set when user feedback is part of the workflow.
- Test locale-aware redirects preserve the expected locale route segment or route parameter.
- Test `navigate: true` redirects only where the UX contract requires SPA-like navigation.

## File Downloads

Livewire file downloads can return normal Laravel download responses from component actions. Behind the scenes, Livewire base64-encodes the file contents, sends them to the browser, and decodes them on the client for download. This means Livewire downloads are convenient, but they are not the right default for large files on old phones or slow 3G.

### Component Rules

- Return Laravel download responses from actions, such as `response()->download($path, $downloadName)` or `Storage::disk($disk)->download($path, $downloadName)`.
- Use `response()->streamDownload()` only for small generated content. In Livewire it is not truly streamed to the browser; the download starts after the contents are collected and delivered.
- Keep download action state small: store IDs and compact metadata in public properties, not file paths, file contents, or large model graphs.
- Authorize before reading or returning any file.
- Resolve file paths through models, policies, and services/actions; do not trust request/query/public-property paths.
- Use stable ASCII-safe download filenames. Keep localized display labels separate from the actual download filename when needed.

### Project Rules

- Prefer Livewire downloads for small user documents, exports, receipts, and generated text/CSV/PDF files that are safe to base64 through a Livewire response.
- Avoid Livewire downloads for large media, galleries, archives, long reports, or anything likely to strain old Android devices or slow 3G.
- For large or sensitive files, design a dedicated named Laravel download route/endpoint backed by policy and service/action logic instead of forcing the file through a Livewire AJAX response.
- Never expose storage paths, access codes, private payment payloads, internal notes, or other sensitive data in download URLs or public Livewire properties.
- Do not introduce S3, public disks, or new storage infrastructure without explicit approval plus config/env/docs/tests.
- Use `data-loading`, scoped `wire:loading`, and disabled-looking button states for slow downloads, but do not rely on UI loading states for authorization or duplicate-submit protection.
- Log or audit downloads only when the domain requires it, such as access instructions, documents, payment records, or dispute evidence.

### Testing

- Use `Livewire::test(...)->call('download')->assertFileDownloaded('name.pdf')` for successful Livewire downloads.
- Use `assertNoFileDownloaded()` for unauthorized, missing, expired, or not-ready files.
- Test that the action does not reveal files belonging to another guest/host/booking.
- Test generated filenames are stable, ASCII-safe, and do not leak private data.
- Use `Storage::fake()` where the file is stored on Laravel disks.

## Teleport

Livewire `@teleport` renders a portion of a component template somewhere else in the DOM. Use it for overlays that need to escape parent stacking contexts, such as nested modal dialogs, bottom sheets, popovers, and toast containers. It is powered by Alpine's `x-teleport` behavior, so it should remain a focused DOM-placement tool, not a general component-boundary replacement.

### Component Rules

- Use `@teleport('body')` as the default for modal and overlay content that must escape parent z-index or overflow contexts.
- The teleport selector can be any CSS selector accepted by `document.querySelector()`, but the target must already exist and must be outside the current Livewire component.
- Never teleport into another element inside the same Livewire component; Livewire does not support that.
- Put exactly one root element inside each `@teleport` block.
- Keep teleported markup small, focused, and mobile-friendly. Do not use teleport as a way to keep huge hidden modal DOM in the page.
- Keep visible text inside teleported content translated through locale files.
- Keep authorization, validation, and persistence in the Livewire class or services/actions. Teleport only changes DOM placement.

### Project Rules

- Prefer Flux modal, popover, dropdown, toast, or drawer primitives when they already solve the UI need.
- Use `@teleport` when a custom overlay, nested dialog, or bottom-sheet surface has real stacking-context problems that Flux composition does not already handle.
- For mobile bottom sheets and action sheets, teleport the overlay shell to `body`, keep tap targets large, trap/restore focus where the component requires it, and provide a clear close action.
- Do not place `@teleport` inside large loops. Extract a single overlay component or use IDs/state to render one active teleported surface.
- Avoid teleporting large galleries, full histories, large filter trees, or heavy relationship payloads. Combine teleport with lazy/deferred content where the overlay is secondary.
- Use stable external targets such as `body` or a documented layout-level root like `#modal-root`; do not invent per-component teleport targets without a clear reason.

### Testing

- Test the Livewire action/state that opens and closes the teleported UI.
- Test that only the intended overlay content is rendered for the active record or selected ID.
- Test validation, authorization, and persistence paths behind teleported forms the same way as normal Livewire forms.
- Test translated labels and empty/error states inside teleported content.

## Wire Bind

Livewire `wire:bind` dynamically binds HTML attributes to component properties or expressions on the client, without requiring a full server re-render for each attribute change. It is similar to Alpine's `x-bind` and uses the form `wire:bind:{attribute}="expression"`. The directive has no modifiers.

### Component Rules

- Use `wire:bind` only for lightweight attribute changes such as `class`, `style`, `disabled`, `aria-*`, and `data-*`.
- Keep expressions short and readable. If the expression needs business rules, localization decisions, permission checks, route generation, or complex formatting, move that work to the Livewire class, a computed property, a DTO, or a service.
- Prefer static, enumerated class values. Do not build unbounded Tailwind class names from user data.
- Bind styles only from sanitized, bounded values such as approved colors, spacing tokens, sizes, or numeric limits. Do not pass raw user input into style strings or style objects.
- Treat `wire:bind:disabled` as visual/client interactivity only. Server actions must still validate, authorize, and protect against duplicate submissions.
- Prefer `data-loading` or `wire:loading.attr="disabled"` for request-in-flight disabling; use `wire:bind:disabled` for state-driven disabling such as archived, not allowed, over limit, or not ready.
- Use `wire:bind:href` only with URLs generated or whitelisted by the component. Prefer named localized routes and never bind raw user-entered URLs.
- Never bind access codes, exact private addresses, payment/provider payloads, internal notes, storage paths, or other sensitive values into `href`, `data-*`, `title`, `aria-label`, or any visible/client-readable attribute.

### Project Rules

- Prefer ordinary Blade attributes and Flux component props when the value is static for a render.
- Use `wire:bind` when the attribute should react instantly on the client to existing Livewire/Alpine state, such as character-limit styling, selected-state classes, disabled state, ARIA state, or small `data-*` counters.
- Do not use `wire:bind` to hide expensive rendering, large DOM, or missing backend checks. Fix the component state, query, validation, or authorization boundary instead.
- Keep visible labels and accessible text translated. Dynamic `aria-label` or `title` values must come from translated, precomputed strings.
- For mobile performance, keep bound expressions cheap and avoid binding many attributes inside large lists.

### Testing

- Test the Livewire state or action that drives the bound attribute.
- For important disabled/hidden/available states, assert both the UI state and the backend authorization/validation failure path.
- Test that bound URLs are safe named/localized URLs and do not expose sensitive data.
- Test locale-specific accessible labels when `aria-*` or `title` bindings are user-visible.

## Wire Click

Livewire `wire:click` calls component methods, also called actions, when a user clicks an element. It supports `wire:click="methodName"` and `wire:click="methodName(param1, param2)"`. Action parameters must be treated like HTTP request input and must never be trusted.

### Component Rules

- Put click behavior in public Livewire action methods, then delegate business logic to services/actions when the operation is more than trivial UI state.
- Use `type="button"` on buttons that are not form submissions.
- Prefer buttons for actions. If `wire:click` is used on an `<a>` element, add `.prevent` so the browser does not follow the `href`.
- Use route links with `wire:navigate` for navigation. Do not use `wire:click` to fake ordinary page navigation.
- Pass compact scalar IDs when needed, not model objects, arrays, or sensitive values.
- Treat every `wire:click` parameter as untrusted request input. Re-load the model by ID, authorize ownership/ability, validate state, and check idempotency before changing data.
- Never rely on disabled buttons, hidden elements, `wire:confirm`, `.once`, `.debounce`, `.throttle`, or loading states as the only protection for destructive or paid actions.
- Use `data-loading`, scoped `wire:loading`, and disabled-looking states for feedback, but keep backend duplicate-submit protection authoritative.
- Use `.renderless` only for side-effect actions that do not need a UI refresh, such as logging or analytics. Do not use it when user-visible state should update.
- Use `.preserve-scroll` for load-more, infinite-list, or in-place panel actions where keeping scroll position improves mobile UX.
- Use `.async` only for independent, idempotent actions that can safely run in parallel. Avoid `.async` for booking, payment, inventory, deposit, checkout, or any action that mutates the same record or ordered workflow.
- Use `.stop`, `.self`, `.outside`, `.window`, `.document`, `.capture`, and `.passive` only when event scope genuinely requires it.

### Project Rules

- Destructive, irreversible, paid, access-related, booking-changing, or privacy-sensitive actions need a confirmation step and server-side authorization.
- Use `wire:confirm` or a translated Flux confirmation surface for risky clicks, but still enforce the rule in the action.
- Keep visible button/link labels translated. Do not hard-code action text in Blade or Livewire.
- Keep click expressions simple. Do not embed business logic, complex conditionals, route generation, or localization in `wire:click`.
- For old Android and slow 3G, keep tap targets large and make loading/disabled states layout-stable.
- For repeated click surfaces inside lists, make sure list queries are paginated/eager-loaded and that the click action only sends the needed scalar ID.

### Testing

- Test successful `wire:click` actions through Livewire component tests.
- Test authorization failures, validation failures, and tampered IDs from click parameters.
- Test destructive actions with confirmation-related UI state where the project uses a custom confirmation surface.
- Test duplicate submission/idempotency for booking, payment, deposit, inventory, checkout, and similar workflow actions.
- Test `.renderless` actions by asserting the side effect, not a UI re-render.
- Test locale-visible labels and feedback messages around click actions.

## Wire Submit

Livewire `wire:submit` belongs on `<form>` elements and calls a component action when the form is submitted. Livewire intercepts the submit event, automatically calls `preventDefault()`, disables submit buttons, and marks form inputs as `readonly` while the request is running.

### Component Rules

- Use `wire:submit="save"` on forms instead of putting `wire:click` on the submit button.
- Do not add `.prevent` to normal `wire:submit` forms; Livewire already prevents the default browser form submission.
- Use `type="submit"` for the primary submit button and `type="button"` for secondary actions inside the form.
- Validate inside the submit action before persistence with `$this->validate()` or `$this->form->validate()`.
- Authorize inside the submit action before persistence, especially for booking, payment, deposit, inventory, checkout, messaging, reviews, profile, and host listing forms.
- Delegate non-trivial persistence, pricing, availability checks, uploads, workflow transitions, and side effects to services/actions.
- Wrap multi-record writes in transactions inside the relevant service/action.
- Treat any `wire:submit` parameters as untrusted request input. Prefer IDs stored in component state, re-load models by ID, authorize, validate current state, and check idempotency before mutating data.
- Keep Livewire public form state small. Use Livewire Form Objects for larger forms, multi-step surfaces, and reusable form state.
- Do not rely on Livewire's automatic submit disabling/readonly behavior as the only duplicate-submit protection. Backend idempotency and state checks remain required.
- Use translated `data-loading` or scoped `wire:loading` feedback for slow form submissions, but keep layout stable on mobile.
- Use `.preserve-scroll` for inline forms where validation errors or saved feedback should not move the user's viewport unexpectedly.
- Use `.renderless` only for submit-like forms that perform side effects without any user-visible state changes. Do not use it for normal save/update flows.
- Use `.async` only for independent idempotent submit actions. Avoid `.async` for booking, payment, deposit, checkout, inventory, or any ordered workflow.

### Project Rules

- Every visible label, placeholder, validation message, loading label, success message, and error message in a submitted form must use translation keys.
- Keep forms short per mobile step. Split dense booking/listing/review/complaint flows into steps or sections backed by compact form state.
- Never put business logic, price calculations, availability checks, authorization, or persistence decisions in Blade.
- Use named localized redirects after successful submit actions when the flow leaves the current page.
- For forms that remain on the page after success, reset only the intended fields and bust any computed properties already read in the request.
- For destructive or paid form submissions, require an explicit confirmation step and server-side idempotency.

### Testing

- Test successful submit actions through Livewire component tests.
- Test validation failures with `assertHasErrors()` and locale-aware error rendering.
- Test authorization failures and tampered IDs.
- Test duplicate submissions/idempotency for booking, payment, deposit, checkout, inventory, and other workflow forms.
- Test that successful submits redirect or update state exactly as the UX contract requires.
- Test translated loading, success, and error feedback when the form has user-visible messages.

## Wire Model

Livewire `wire:model` binds form input values to component properties. By default in Livewire 4, updating a `wire:model` input does not send a network request by itself; the value is synchronized with the server when an action runs, such as `wire:submit` or `wire:click`. Use live update modifiers only when the UX truly needs earlier server synchronization.

### Component Rules

- Use plain `wire:model` for fields that only need to synchronize when the form is submitted or another action runs.
- Use `wire:model.blur` for normal text inputs when local component state should update after the user leaves the field.
- Use `wire:model.change` for selects, checkboxes, radios, switches, and bounded option controls.
- Use `wire:model.enter` for compact search/lookup fields where the Enter key is the intended commit point.
- Use `wire:model.live` only for real-time validation, search, autocomplete, dependent filters, or small interactive previews that genuinely need a request while editing.
- Remember `.live` has a default 150ms debounce. In this project, prefer `.live.debounce.500ms` or `.live.debounce.750ms` for search/autocomplete on old phones and slow 3G.
- Use `.debounce.Xms` or `.throttle.Xms` only with `.live`, and choose timings that reduce request noise.
- Avoid live updates for long textareas such as messages, reviews, complaints, host notes, rules, descriptions, and dispute details.
- Use `.number` and `.boolean` for simple scalar casting, but still validate and normalize server-side before persistence or queries.
- Use `.fill` only when an HTML `value` attribute is the deliberate initial state source.
- Use `.preserve-scroll` only when live model updates must not move the user's viewport.
- Use `.deep` sparingly; `wire:model` normally listens only to events from the bound element itself, and descendant event capture should be rare.

### Inputs And Form State

- Bind native text inputs, textareas, checkboxes, radio buttons, selects, and multiple selects with `wire:model` when the state belongs to the component.
- Do not put content inside a bound `<textarea>`; initialize the component property and let Livewire fill the textarea value.
- For checkbox groups and multi-selects, keep arrays bounded, validated, and made of safe scalar values.
- Use a disabled placeholder `<option value="">` for selects that need an empty prompt; there is no native select `placeholder`.
- For dependent selects, add a stable `wire:key` to the changing select so Livewire resets and refreshes its value when options change.
- Bind Livewire Form Object fields with dot notation, such as `wire:model.blur="form.title"`, and assert validation errors with the same key prefix.
- Dot notation and bracket notation are both supported for nested properties, arrays, and form state, but keep paths readable and shallow.
- Prefer IDs, enum-like strings, booleans, and small arrays in model-bound public state. Do not bind full models, DTO graphs, sensitive values, access codes, payment/provider payloads, or private dispute/internal-note text to client-readable state unless the user is allowed to edit that exact value.

### Project Rules

- Official examples may show `Model::all()` or relationship queries inside Blade loops. Do not copy that pattern. Preload options in the Livewire class, computed properties, DTOs, or services with selected columns, limits, indexes, and authorization.
- Do not preload huge country/city/location lists into selects. Use autocomplete/backend search against local SQLite data for large datasets.
- Pair URL-backed search/filter properties with validated compact `wire:model` state and reset pagination when query-shaping state changes.
- Use Livewire Form Objects for larger forms, reusable form state, and multi-step booking/listing/review/complaint flows.
- Every visible label, placeholder, option, validation message, loading label, and empty state around model-bound controls must use translation keys.
- Model-bound public properties are not a security boundary. Validate, authorize, coerce, and re-check current database state in the action before using values for persistence, pricing, availability, booking, payment, deposit, inventory, access, or privacy decisions.

### Testing

- Test form submission with default `wire:model` state to prove values are synchronized when the action runs.
- Test `.blur`, `.change`, `.enter`, and `.live.debounce.*` behavior where those modifiers are part of the feature contract.
- Test type casting, array inputs, dependent select resets, and nested/form object keys when used.
- Test validation and authorization failures for tampered model-bound values.
- Test locale-visible labels, placeholders, validation feedback, and empty states around model-bound controls.

## Wire Navigate

Livewire Navigate turns normal internal links into Livewire-managed page visits. Livewire intercepts the click, fetches the next page in the background, shows the top progress bar when needed, then swaps the URL, page title, and body without a full browser load.

### Component Rules

- Use `wire:navigate` on internal app links where SPA-like navigation improves perceived speed.
- Prefer named localized route URLs in `href`; never navigate to raw user-supplied URLs.
- Do not use `wire:navigate` for external links, downloads, file/media URLs, anchor-only links, forms, or flows that intentionally need a full page load.
- Use `$this->redirect(..., navigate: true)` or `$this->redirectRoute(..., navigate: true)` only after validation, authorization, and persistence succeed, and only for internal destinations.
- Active `wire:navigate` links automatically receive `data-current`; prefer Tailwind `data-current:*` variants for active navigation styles.
- Use `wire:current` only when its documented class behavior is needed, and use `wire:current.ignore` when automatic active styling must be disabled on a navigate link.
- Use `wire:navigate.hover` sparingly. It prefetches after about 60ms of hover and can request pages users never visit.
- Avoid `.hover` on dense lists, search results, feeds, message rows, notification rows, or expensive pages.
- Remember default mousedown/up prefetch is already built into Navigate, so `.hover` is not the default.
- Use `@persist` only for layout-level elements that truly must survive page changes, such as media players or app chrome, and keep persisted elements outside Livewire components.
- Use `wire:navigate:scroll` on persisted scroll containers when their own scroll position must survive navigation.
- Use manual `Livewire.navigate('/path')` only for internal, generated or whitelisted paths and rare JavaScript integration points.

### JavaScript And Assets

- `DOMContentLoaded` fires only on the first page load. Use `livewire:navigated` for code that must run after every Navigate visit.
- Use `livewire:navigate`, `livewire:navigating`, and `livewire:navigated` only for focused integration needs; `livewire:navigate` can be cancelled and `livewire:navigating` supports `event.detail.onSwap()`.
- Document event listeners persist across page visits. Clean them up or register them with `{ once: true }` when they should not accumulate.
- Identical `<head>` scripts run once, new head scripts are evaluated, and head assets can block the page swap.
- Vite assets are tracked automatically with `data-navigate-track`; avoid ad hoc asset swapping unless the page truly needs it.
- Body scripts are re-evaluated after navigation. Use `data-navigate-once` only for body scripts that must run once.
- Analytics snippets may need SPA-aware configuration, such as Fathom's `data-spa="auto"`.
- The Navigate progress bar appears only after a short delay and should be configured through `config/livewire.php`, not page-local UI code.

### Project Rules

- Keep `wire:navigate` links translated and accessible; link labels, active labels, and `aria-*` text must come from translation keys.
- Preserve locale and query-string intent on named localized routes.
- Do not use Navigate to hide slow pages. Fix queries, eager loading, indexes, pagination, lazy loading, or payload size first.
- For mobile shell navigation, use `data-current:*` active styling and stable dimensions so labels do not shift.
- Test important Navigate links for locale preservation, active state, back/forward behavior, and JavaScript lifecycle when custom scripts are involved.

## Wire Current

Livewire `wire:current` applies CSS classes to links whose `href` matches the current page. It works with `wire:navigate` page changes and also adds `data-current` to matching links.

### Component Rules

- Prefer automatic `data-current` styling for normal `wire:navigate` links, especially in the mobile shell.
- Use `wire:current="classes"` only when class-based active styling is clearer than Tailwind `data-current:*`, or inside persisted navigation where server-side active conditionals will not rerender.
- Every `wire:current` link must have a real `href`, preferably generated from a named localized route.
- Do not use `wire:current` to create links, change destinations, hide authorization state, or replace backend route/policy checks.
- Keep `wire:current` class lists static and bounded. Do not build active class strings from user input or unbounded data.
- By default, `wire:current` uses partial path matching, so a `/posts` link matches `/posts/1`.
- Use `.exact` when only the exact path should be active, especially for root, dashboard, and top-level tab links.
- Use `.strict` only when trailing slashes are intentionally meaningful; default matching ignores trailing slashes.
- Use `wire:current.ignore` on `wire:navigate` links when automatic `data-current` behavior must be disabled.

### Project Rules

- Active navigation styles must be mobile-stable: no layout jumps, clipped labels, or tap-target shrinkage.
- Active labels, tooltips, and accessible text must still use translation keys; `wire:current` should only contain CSS classes.
- Prefer active styling that improves orientation without overloading dense lists, search results, messages, or notifications.
- Do not use PHP route checks in Blade for links inside `@persist`; use `data-current` or `wire:current` so active state follows Navigate page changes.
- If active state does not update, verify the page has Livewire scripts or at least one Livewire component, and verify the link has `href`.
- Test root-route `.exact` behavior, nested route partial behavior, locale-prefixed paths, and persisted navigation active state when those surfaces are used.

## Wire Cloak

Livewire `wire:cloak` hides an element on initial page load until Livewire has fully initialized. It prevents users from briefly seeing uninitialized dynamic content, especially content controlled by `wire:show`.

### Component Rules

- Use `wire:cloak` for small state-dependent elements that would otherwise flash in the wrong state before Livewire initializes.
- Pair `wire:cloak` with `wire:show` when multiple mutually exclusive states could briefly render together on initial load.
- Remember `wire:cloak` has no modifiers, parameters, target syntax, or delay configuration.
- Do not use `wire:cloak` as a loading state for Livewire requests; use `data-loading`, `wire:loading`, lazy/defer, or skeletons for request-time feedback.
- Do not use `wire:cloak` to hide sensitive, unauthorized, private, paid, access-code, payment, dispute, or internal-note data. Sensitive data must not be rendered for unauthorized users at all.
- Do not cloak large first-screen sections, long lists, forms, or primary content just to avoid designing a stable loading/empty state.
- Keep cloaked elements layout-stable so initialization does not cause a jump on old phones or slow 3G.
- Prefer server-rendered safe defaults when possible; use `wire:cloak` only when the initial client-side state would otherwise be misleading.

### Project Rules

- Visible text inside cloaked elements must still use translation keys.
- Cloaked icon swaps, badges, menus, and small panels should reserve stable dimensions.
- When a large section needs delayed data, use lazy/deferred components or islands with translated placeholders instead of `wire:cloak`.
- Test that the final initialized state is correct; `wire:cloak` only hides the pre-init flash and does not prove state, authorization, or visibility logic.

## Loading States

Livewire automatically adds a `data-loading` attribute to elements that trigger network requests. In this project, prefer `data-loading` Tailwind variants for most loading feedback and reserve `wire:loading` for simple show/hide cases where the directive is clearer.

### Use Data Loading For

- Buttons and links that trigger Livewire actions with `wire:click`.
- Forms submitted with `wire:submit`.
- Search/autocomplete inputs using `wire:model.live.debounce.*`.
- Event dispatch buttons, including events handled by another Livewire component.
- Inline label swaps, opacity changes, disabled-looking states, and subtle mobile feedback.

### Syntax

Basic action feedback:

```blade
<flux:button
    type="button"
    wire:click="save"
    class="data-loading:opacity-60 data-loading:pointer-events-none"
>
    {{ __('app.actions.save') }}
</flux:button>
```

Swapping button text while a request is running:

```blade
<flux:button type="button" wire:click="save">
    <span class="in-data-loading:hidden">
        {{ __('app.actions.save') }}
    </span>

    <span class="not-in-data-loading:hidden">
        {{ __('app.messages.saving') }}
    </span>
</flux:button>
```

Styling a parent when a child is loading:

```blade
<section class="has-data-loading:opacity-70">
    <flux:button type="button" wire:click="refresh">
        {{ __('app.actions.refresh') }}
    </flux:button>
</section>
```

### Tailwind Variants

- Use `data-loading:*` on the element that triggers the request.
- Use `not-data-loading:hidden` for elements that should appear only during loading.
- Use `in-data-loading:*` for children of a loading trigger.
- Use `has-data-loading:*` for a parent that should react when a child is loading.
- Use `peer-data-loading:*` when sibling styling is cleaner than parent styling.
- Advanced variants such as `in-data-loading:`, `has-data-loading:`, `peer-data-loading:`, and `not-data-loading:` require Tailwind CSS v4+, which this project uses.

### Wire Loading

`wire:loading` remains allowed for simple show/hide loading indicators, especially where an existing component already uses it clearly. Prefer `data-loading` when styling the request trigger itself, when events cross component boundaries, or when the loading state is mostly Tailwind styling.

- `wire:loading` hides the element by default and shows it while the component has an in-flight request.
- Use `wire:loading.remove` for inverse behavior: visible by default and hidden during loading.
- Use `wire:loading.class="..."` or `wire:loading.class.remove="..."` only for simple class toggles; prefer `data-loading:*` variants for most Tailwind styling on request triggers.
- Use `wire:loading.attr="disabled"` for non-submit buttons or controls outside `wire:submit` forms. `wire:submit` already disables submit buttons and marks inputs readonly while submitting.
- Add `wire:target="action"` or `wire:target="property"` when a component has more than one possible request and the indicator must react only to one action or one live property update.
- Use comma-separated targets, such as `wire:target="save, remove"`, only when one indicator deliberately covers multiple actions.
- Use parameter targets such as `wire:target="remove({{ $id }})"` for repeated list rows so one row's indicator does not appear for every row.
- Use `wire:target.except="download"` for broad indicators that should ignore one action or property.
- Use display modifiers such as `.inline`, `.block`, `.flex`, `.grid`, `.table`, or `.inline-flex` when the default `inline-block` display value is wrong for the layout.
- Use `.delay` to avoid flicker for fast requests. The default delay is 200ms; aliases are available from `.delay.shortest` at 50ms through `.delay.longest` at 1000ms.
- Keep `wire:loading` indicators short, translated, and layout-stable. For old phones and slow 3G, avoid adding large hidden DOM or shifting content during loading.
- For file uploads, scope upload feedback with `wire:loading wire:target="photo"` or use the automatic `data-loading` approach documented above.

### Project Rules

- Every visible loading label must use translation keys.
- Keep loading feedback subtle on old phones: opacity, disabled pointer behavior, small spinner/icons, skeletons, and stable layout are preferred.
- Avoid deeply nested `in-data-loading:*` selectors when parent and child components can load at the same time; the variant reacts to any loading ancestor.
- Do not use broad unscoped `wire:loading` in components with multiple actions unless every request should show the same indicator.
- Do not use loading states to hide slow queries. Optimize queries, selected columns, indexes, eager loading, and pagination first.
- Do not rely on loading state to prevent duplicate writes. Server-side validation, authorization, idempotency, and transactions still matter.

## Lazy And Deferred Components

Lazy and deferred loading delay whole Livewire child components. Use them when a child component's data should not block the first mobile render. Use Islands when the delayed or isolated region belongs inside the current component and does not need a reusable child component boundary.

### Use Lazy For

- Below-the-fold child components that users may not scroll to.
- Slow secondary sections such as reviews, similar sleeping places, host hints, compatibility summaries, feeds, and large dashboard panels.
- Expensive queries or external service calls that are already optimized but should not block the first render.

### Use Defer For

- Secondary components that are visible early but are not required for the first meaningful render.
- Dashboard cards, notification slices, message summaries, and status panels that should load immediately after the page loads.
- Components that should not wait for viewport visibility.

### Syntax

Prefer instance-level loading when only some usages should be delayed:

```blade
<livewire:listings.reviews-summary :sleeping-place-id="$sleepingPlaceId" lazy />

<livewire:dashboard.host-quick-stats defer />
```

Use attributes only when every usage of the component should be delayed:

```php
use Livewire\Attributes\Defer;
use Livewire\Attributes\Lazy;

#[Lazy]
class SimilarSleepingPlaces extends Component
{
    //
}

#[Defer]
class HostQuickStats extends Component
{
    //
}
```

### Placeholders

This project uses class components, so define placeholders with a `placeholder()` method instead of Blade `@placeholder` for lazy or deferred child components.

```php
public function placeholder(array $params = []): View
{
    return view('livewire.placeholders.card-list', $params);
}
```

- The placeholder root element type must match the final component root element type.
- Placeholder views must be small, translated, and skeleton-friendly.
- Do not run queries from placeholder views.
- Do not add `@php` to placeholder views. Prepare values in the component, service, DTO, or class-based Blade component.

### Props And Payload

- Pass scalar IDs, filters, booleans, and compact strings into lazy/deferred components whenever possible.
- Avoid passing full Eloquent models unless the re-query behavior is intentional.
- Lazy component props are dehydrated on the page and used later when the component loads, so never pass sensitive data that the browser should not hold.

### Bundling

Livewire loads multiple lazy/deferred components in isolated parallel requests by default.

- Keep the default isolated behavior when components have different load times or each component should appear as soon as it is ready.
- Use `lazy.bundle`, `defer.bundle`, `#[Lazy(bundle: true)]`, or `#[Defer(bundle: true)]` only for many similar components with similar load cost, where reducing request overhead matters.
- Prefer `bundle` naming. Do not introduce legacy `isolate: false` in new code.
- Do not bundle one slow component with several fast components, because the fast components will wait for the slowest response.

### Full-Page Components

Livewire route-level lazy/defer exists, but use it sparingly in this project. Prefer lazy nested sections over lazy full pages for core flows such as search, booking, check-in, checkout, payments, messages, and notifications. If a route-level component is lazy or deferred, its shell must still be useful on old phones and slow 3G.

### Testing

- Use `Livewire::withoutLazyLoading()` in tests that need to assert final lazy component content.
- Test placeholder output separately when the placeholder is part of the user experience contract.
- Lazy/deferred loading is not an authorization boundary; tests must still cover authorization inside the component action or query.

### Mistakes To Avoid

- Do not lazy-load critical booking actions, payment actions, access/privacy decisions, or first-screen search controls.
- Do not use lazy/defer to hide N+1 queries, missing indexes, huge public properties, or overly broad selects.
- Do not use Blade `@placeholder` for class-based lazy/deferred child components in this project.
- Do not pass large arrays or sensitive payloads into lazy/deferred component props.
- Do not bundle unrelated components just because they are on the same page.

## Islands

Livewire Islands create isolated regions inside a Livewire component. When an action runs inside an island, only that island re-renders, not the whole component.

### Use Islands For

- Expensive computed sections that should not re-render with every parent update.
- Independent regions inside one screen, such as stats, counters, side panels, summaries, feeds, badges, or notification slices.
- Slow below-the-fold content that should not block the first mobile render.
- Polling or lightweight real-time refresh when only part of the page needs updates.
- Load-more or feed-like regions where append/prepend behavior is useful.

### Prefer Nested Components Instead When

- The UI must be reusable across multiple screens.
- The region needs its own lifecycle hooks, validation, upload handling, or authorization boundary.
- The region owns complex independent state.
- The feature belongs in a reusable component library.

### Syntax

Basic isolated island:

```blade
@island
    <section>
        {{ $this->expensiveSummary }}

        <flux:button type="button" wire:click="$refresh">
            {{ __('app.actions.refresh') }}
        </flux:button>
    </section>
@endisland
```

Named island targeted by an action:

```blade
@island(name: 'summary')
    <section>{{ $this->summary }}</section>
@endisland

<flux:button type="button" wire:click="$refresh" wire:island="summary">
    {{ __('app.actions.refresh') }}
</flux:button>
```

Lazy island with a translated/skeleton placeholder:

```blade
@island(lazy: true)
    @placeholder
        <x-ui.skeleton-card />
    @endplaceholder

    <section>{{ $this->belowFoldSummary }}</section>
@endisland
```

### Loading Modes

- `@island(lazy: true)` loads when the island becomes visible in the viewport.
- `@island(defer: true)` loads immediately after the page load, regardless of viewport visibility.
- `@island(skip: true)` skips initial render and shows placeholder content until triggered.
- `@placeholder` should be used for lazy, defer, or skip islands so old phones and 3G users see a stable loading state.

### Targeting And Update Modes

- Use `wire:island="name"` with a Livewire action directive to update only the named island.
- Use `wire:island.append="name"` for load-more lists and feeds that append content.
- Use `wire:island.prepend="name"` for newest-first feeds.
- Multiple islands with the same name update together.
- Nested islands are allowed; inner islands are skipped by default when an outer island updates.
- Use `always: true` only when the island must stay synchronized with every parent update.

### Polling

Use `wire:poll` inside an island when only that region needs periodic updates:

```blade
@island(name: 'urgent-notifications', lazy: true)
    @placeholder
        <x-ui.skeleton-card />
    @endplaceholder

    <section wire:poll.visible.30s>
        {{ $this->urgentNotificationCount }}
    </section>
@endisland
```

Keep polling intervals conservative. For this project, prefer `wire:poll.visible.30s` or slower for urgent panels and avoid second-by-second polling.

### Project Rules

- Do not place `@island` inside `@foreach`, `@forelse`, `@if`, `@unless`, or other Blade control structures. Put the loop or conditional inside the island and expose data through component properties or computed properties.
- Do not rely on local Blade variables inside an island. Islands can access component properties and methods, not template-local variables.
- Do not introduce `@php` in Blade to support an island. Prepare values in the Livewire class, presenter, DTO, service, or class-based Blade component.
- Use `#[Computed]` methods for expensive island data so the calculation runs only when the island renders.
- Keep public properties small: IDs, filters, booleans, short strings, and compact state only.
- For query-heavy island data, use selected columns, eager loading, aggregates, cursor pagination, or load-more slices.
- Avoid mutating the same state from the root component and multiple islands at the same time. Parallel island requests can race; the last response wins.
- Islands are an optimization tool, not a default wrapper for static content.
- Every visible placeholder, button label, empty state, and error inside an island must use translation keys.

### Good rent2gether Candidates

- Listing detail sections below the booking card: reviews summary, similar sleeping places, host hints, compatibility summary.
- Dashboards: urgent notifications, unread messages, host quick stats, pending review requests.
- Messages and notifications: urgent panels, unread badges, load-more conversation slices.
- Search and favorites: below-the-fold recommendation groups and compare summaries.
- Host listing screens: readiness summaries, calendar hints, slow aggregate cards.

### Mistakes To Avoid

- Do not split static content into islands.
- Do not use islands to hide N+1 queries; fix the query first.
- Do not use islands as a substitute for reusable components when the UI is shared across screens.
- Do not combine aggressive polling, large DOM output, and slow database queries inside one island.
- Do not copy official single-file component examples with inline PHP into this project.

## Extended Wire Directives

These directives are allowed when they solve a specific mobile UX or performance problem. Do not add them as decoration, and do not use client-side behavior as a substitute for authorization, validation, privacy, idempotency, or domain services.

### Confirmation And Dirty State

- Use `wire:confirm` only on actions where a browser confirmation is acceptable UX. For richer mobile confirmation, prefer a Flux modal or bottom sheet backed by the same Livewire action.
- Confirmation messages must come from translation keys. Do not hard-code English or Russian copy in `wire:confirm`.
- Use `wire:confirm.prompt` only for rare destructive actions where typed confirmation is worth the friction. Keep the expected token stable and not localized if the match must be exact, but localize the prompt text around it.
- `wire:confirm` does not protect the server. Destructive, paid, access-related, inventory, deposit, cancellation, and privacy actions must still authorize, validate state, and be idempotent.
- Use `wire:dirty` for unsaved-change hints on editable forms, targeted field warnings, and local form state styling.
- Use `wire:dirty wire:target="property"` or `$dirty('property')` when only one field or group should show dirty feedback.
- Use `wire:dirty.class="..."` for bounded visual states such as border or text color. Do not generate unbounded Tailwind classes from user data.
- Keep dirty indicators small, translated, and stable in layout. They are not save guarantees; they only compare client and server state.

### Visibility And Transitions

- Use `wire:show` for small conditional UI that can stay in the DOM and toggle CSS display.
- Pair `wire:show` with `wire:cloak` when initial uninitialized visibility would flash the wrong state.
- Do not use `wire:show` for large hidden filter trees, private data, access details, payment payloads, internal notes, or anything the user should not receive in the DOM.
- Use `wire:transition` only for small state changes where View Transitions improve orientation without slowing old phones. Functionality must remain correct when the browser has no transition support or reduced motion is enabled.
- Give transitions explicit names when more than one region can animate at the same time.
- Use `#[Transition]`, `$this->transition()`, or `$this->skipTransition()` for action-level transition direction or skip behavior. Avoid transitions in booking, payment, access, or emergency flows when motion could slow the task.

### Initialization, Intersections, Polling, And Offline

- Use `wire:init` only for non-critical data that can load after the first render. Do not delay first-screen booking actions, payment checks, access/privacy decisions, or required validation behind `wire:init`.
- Prefer `lazy`, `defer`, and islands over `wire:init` when the delayed region is a whole component or isolated section.
- Use `wire:intersect.once` for one-time viewport side effects such as marking a hint seen, loading a below-the-fold summary, or tracking a safe analytics event.
- Use `wire:intersect` thresholds and margins conservatively so old phones do not fire noisy requests during scroll.
- Use `wire:poll.visible.30s` or slower for urgent panels that genuinely need periodic refresh. Avoid second-by-second polling.
- Scope polling to islands or small child components whenever possible so the whole screen does not re-render.
- Do not poll expensive queries, full search results, large message histories, or notification centers. Use cursor pagination, load-more, and explicit refresh actions instead.
- Use `wire:offline` for small connection banners and disabled-looking states. Offline UI must be translated and should not hide important already-rendered context.
- Offline UI is not a retry queue. Mutating actions still need server-side validation and clear failure messages after reconnection.

### DOM Boundaries And JavaScript Bridges

- Use `wire:ignore` only for DOM owned by a third-party widget or custom JavaScript that Livewire should not morph. Keep a clear synchronization path back to Livewire state.
- Use `wire:ignore.self` when Livewire may update children but must not change the root element's attributes.
- Do not use `wire:ignore` to hide broken Livewire markup, stale state, or authorization problems.
- Use `wire:replace` only when replacing an entire element is safer than morphing, such as small external DOM bridges or browser-managed state that morphing would corrupt.
- Use `wire:ref` and `$refs` only inside component JavaScript that needs a specific DOM element. Clean up listeners and timers with Livewire cleanup hooks.
- In class components, wrap component-specific scripts in `@script`; do not place raw persistent document listeners in Blade.
- Use `wire:text` for small text-only client updates where avoiding a morph is useful. Do not use it for rich HTML, translated markup, sensitive values, or complex formatting that belongs in PHP.

### Sorting And Streaming

- Use `wire:sort` for drag-and-drop order editing only when the order matters to the user and keyboard/mobile alternatives are acceptable.
- Persist sort order in a service/action, re-load models by ID, authorize ownership, validate destination groups, and handle concurrent reorder attempts.
- Use `wire:sort:handle` for dense cards so scrolling remains possible on touch devices. Use `wire:sort:ignore` for controls inside sortable rows.
- Do not use sorting for booking, payment, deposit, access, or legal/dispute ordering where accidental drag could be costly.
- Use `wire:stream` only for lightweight progressive output such as safe generated text, small status logs, or counters. It is not a general replacement for pagination, polling, or queues.
- Prefer `wire:stream.replace` for counters/progress and append mode for genuinely append-only text.
- Do not stream private payloads, access codes, payment/provider data, internal notes, or unbounded content into the DOM.

## Livewire PHP Attributes

Attributes belong in class components under `app/Livewire/...`. They do not change the project's rule that business logic belongs in services/actions and visible strings belong in translation files.

- Use `#[Locked]` on public scalar identifiers that must not be changed from the browser, such as `bookingId`, `propertyId`, `roomId`, and `sleepingPlaceId`. Locked values are still client-visible and are not authorization.
- Use `#[Session]` only for compact, non-sensitive UI preferences such as selected tabs or local filter state. Do not put access details, private messages, payment data, internal notes, or large arrays in session-backed public properties.
- Use `#[Reactive]` for small child props that must update when the parent changes. Avoid making large arrays, model graphs, or frequently changing lists reactive.
- Use `#[Modelable]` for reusable input-like child components with one small model value. Do not model complex forms through many nested child components if a Livewire Form Object is clearer.
- Use `#[On]` for explicit component events with stable names. Keep event payloads small and treat them as untrusted input.
- Dynamic event names must be bounded by trusted IDs or enum-like state; do not build event names from raw user input.
- Use `#[Renderless]` for actions that do not change visible state, such as safe analytics, read markers, or tiny side effects. If the user should see changed state, let the component render.
- Use `#[Async]` or `.async` only for independent idempotent work that does not mutate the same record/workflow as other in-flight actions. Avoid it for booking, payment, deposit, inventory, checkout, access, cancellation, and review publication flows.
- Use `#[Isolate]` only when an independent component request should not be bundled with or block other components. Avoid isolated requests that race over shared state.
- Use `#[Js]` for small browser-only actions. Do not put pricing, availability, authorization, privacy, validation, or workflow decisions in JavaScript actions.
- Use `#[Json]` only for safe, compact, JSON-serializable public state. Remember public state is sent to the browser.
- Use `#[Layout]` and `#[Title]` for full-page Livewire components when it keeps page metadata close to the component. Titles must be translated or resolved from translated metadata.
- Use `#[Lazy]` and `#[Defer]` at class level only when every use of the component should delay loading. Prefer instance-level `lazy` or `defer` for one-off placements.
- Use `#[Transition]` for action-level transition types or skip behavior; keep reduced-motion and low-end device comfort in mind.
- The dedicated `#[Computed]`, `#[Url]`, and `#[Validate]` references reinforce the existing rules in this file: small/safe state, explicit cache invalidation, shareable-only URL state, translated validation, and validation before persistence.

## Blade Directives And Runtime Internals

### Placeholders, Persisted DOM, And Teleport

- Use `@placeholder` inside lazy, defer, or skip islands. For class-based lazy/deferred child components, keep using a `placeholder()` method.
- Placeholder markup must be translated, skeleton-friendly, query-free, and root-compatible with the loaded content.
- Use `@persist` only in layout-level markup that exists across Navigate visits, such as the navigation loading bar or a future media player. Do not persist booking, payment, access, dispute, or form state by accident.
- Persisted markup does not re-render on each Navigate visit, so active navigation should use `data-current` or `wire:current`, not server-side active route checks.
- Use `@teleport` according to the Teleport section above; the dedicated directive page does not change the project's preference for Flux primitives first.

### Morphing, Hydration, And Nesting

- Keep one stable root element per Livewire view and per loop item.
- Use stable `wire:key` values for repeated components, conditional swaps, dynamic form rows, and any list where identity matters.
- Do not rely on morphing to repair invalid HTML, duplicated keys, missing roots, or large conditional DOM swaps.
- Reach for `wire:ignore` or `wire:replace` only after the DOM boundary is intentional and documented.
- Hydration means public properties are serialized between requests. Keep them small, non-sensitive, and easy to validate.
- Never store access codes, exact private addresses, payment/provider payloads, internal notes, private dispute details, or large DTO/model graphs in public properties.
- Nested Livewire components have their own state and do not automatically re-render just because the parent re-renders. Use events, `#[Reactive]`, `#[Modelable]`, or explicit keys only when the dependency is real.
- Pass scalar IDs and compact values into nested components. Let each component authorize and query its own data when it owns an interaction boundary.

### Troubleshooting, Security, CSP, JavaScript, And Advanced Extension Points

- When a Livewire view behaves strangely, check for missing/duplicated `wire:key`, multiple root elements, invalid nesting, stale assets, missing scripts, untracked `wire:navigate` JavaScript, and broad `wire:ignore` first.
- Treat every public property, action parameter, event payload, URL-bound value, and model ID as untrusted. Re-load, authorize, validate, and check current workflow state server-side.
- `#[Locked]`, hidden inputs, disabled buttons, `wire:confirm`, and loading states are UX helpers, not security controls.
- Strict CSP changes must be made deliberately with Livewire and Alpine requirements verified. Do not add inline scripts outside `@script`, and prefer documented hooks/directives.
- Use the Livewire JavaScript APIs for component-local behavior only when HTML directives and Flux components are not enough.
- Register custom JavaScript directives rarely, keep them idempotent, and always clean up listeners.
- Custom synthesizers are advanced infrastructure. Do not create one unless a domain value object truly cannot be handled by normal casts, DTO arrays, or Livewire-supported types.
- Package development and contribution-guide pages are upstream-maintenance references. They do not justify adding app packages, changing dependencies, or moving app code into packages without an explicit architecture decision.
