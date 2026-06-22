# Livewire 4 Reference

This file records Livewire 4 documentation links reviewed for `rent2gether`.
When the user provides a new `https://livewire.laravel.com/docs/4.x/...` link, read it, verify with Laravel Boost or Context7 when useful, and update this file before implementing related Livewire changes.

Project override: official examples may use view-based or single-file components, but this project uses Livewire class components only. Do not copy inline PHP examples into Blade. Put PHP state, computed properties, authorization, validation, and actions in `app/Livewire/...` classes, and keep views under `resources/views/livewire/...`.

## Reviewed Sources

| Source | Reviewed | Project status |
| --- | --- | --- |
| <https://livewire.laravel.com/docs/4.x/islands> | 2026-06-22 | Binding for isolated Livewire update regions and mobile performance work. |
| <https://livewire.laravel.com/docs/4.x/lazy> | 2026-06-22 | Binding for lazy/deferred Livewire components, placeholders, bundling, and lazy-testing rules. |
| <https://livewire.laravel.com/docs/4.x/loading-states> | 2026-06-22 | Binding for `data-loading`, Tailwind loading variants, and `wire:loading` fallback rules. |
| <https://livewire.laravel.com/docs/4.x/validation> | 2026-06-22 | Binding for `#[Validate]`, form objects, real-time validation, custom rules, and validation tests. |
| <https://livewire.laravel.com/docs/4.x/uploads> | 2026-06-22 | Binding for `WithFileUploads`, temporary files, previews, progress, cancellation, configuration, and upload tests. |
| <https://livewire.laravel.com/docs/4.x/pagination> | 2026-06-22 | Binding for `WithPagination`, URL pagination, page resets, named paginators, cursor pagination, and pagination views. |
| <https://livewire.laravel.com/docs/4.x/url> | 2026-06-22 | Binding for `#[Url]`, query aliases, nullable URL state, `except`, `keep`, history behavior, `queryString()`, and trait hooks. |
| <https://livewire.laravel.com/docs/4.x/computed-properties> | 2026-06-22 | Binding for `#[Computed]`, request memoization, memo busting, persisted/global computed cache, and computed-property view access. |
| <https://livewire.laravel.com/docs/4.x/redirecting> | 2026-06-22 | Binding for Livewire action redirects, `redirectRoute()`, `redirectIntended()`, `redirectAction()`, flash messages, and `navigate: true` redirects. |

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

`wire:loading` remains allowed for very simple show/hide loading indicators, especially where an existing component already uses it clearly. Prefer `data-loading` when targeting would become complex, when events cross component boundaries, or when the loading state is mostly styling.

### Project Rules

- Every visible loading label must use translation keys.
- Keep loading feedback subtle on old phones: opacity, disabled pointer behavior, small spinner/icons, skeletons, and stable layout are preferred.
- Avoid deeply nested `in-data-loading:*` selectors when parent and child components can load at the same time; the variant reacts to any loading ancestor.
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
