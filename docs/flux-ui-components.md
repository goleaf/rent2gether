# Flux UI Components Reference

This file contains project-specific Flux UI usage notes extracted from official documentation links provided by the user.

Reviewed sources in this file are authoritative for this project. Do not infer Flux UI props, variants, events, slots, child components, or syntax from memory when a component section is missing. Read the official Flux UI documentation URL provided by the user, then add or update the matching section before implementing related UI changes.

## Project Usage Contract

- Treat provided `https://fluxui.dev/...` URLs as authoritative for this Laravel project.
- Prefer documented Flux UI components over custom Blade/Tailwind markup when a component covers the use case.
- Use only documented Flux UI props, variants, events, slots, child components, bindings, and examples.
- Keep all Flux usage compatible with Laravel Blade, Livewire class components, Tailwind CSS v4, mobile-first layouts, translated UI strings, and the no-Volt/no-Filament/no-Inertia project rules.
- Do not place business logic, queries, money calculations, availability checks, or date math in Blade while composing Flux components.
- Use `wire:model.blur` for normal text fields, `wire:model.change` for selects, checkboxes, radios, toggles, and time choices, and debounced live models only for search/autocomplete behavior already allowed by the project rules.
- Keep Livewire public properties small; pass IDs, compact DTO arrays, and translated labels instead of large datasets.
- When a documented Flux component exists for forms, validation, modals, buttons, inputs, selects, tables, navigation, cards, dropdowns, tabs, or notifications, check this reference before creating app-specific markup.

## Updating This File

When the user provides one or more Flux UI documentation links:

1. Read each URL before editing UI code.
2. Extract only documented behavior from the page.
3. Add or update the matching section.
4. Include the source URL and the review date.
5. Preserve existing project-specific rules unless the official documentation or user explicitly changes them.
6. Do not invent missing props, variants, events, slots, accessibility behavior, or examples.

## Guide: Principles

Source: https://fluxui.dev/docs/principles
Reviewed: 2026-06-20

### Component or guide name

Flux UI Principles.

### Purpose

Flux is a UI system for Blade components, not only a collection of isolated tags. Its documented principles are simplicity, composability, friendly naming, consistent syntax, brevity, native browser behavior, CSS-first behavior, and application-owned spacing.

Use this guide as the baseline for all Flux component decisions in this Laravel, Blade, Livewire, and Tailwind CSS project.

### Basic usage

Prefer the concise documented syntax for normal cases:

```blade
<flux:input wire:model="email" label="{{ __('profile.email') }}" />
```

Use documented composition when the UI needs more control:

```blade
<flux:field>
    <flux:label>{{ __('profile.email') }}</flux:label>
    <flux:input wire:model.blur="email" />
    <flux:error name="email" />
</flux:field>
```

Compose standalone Flux components instead of inventing custom wrappers:

```blade
<flux:dropdown>
    <flux:button>{{ __('common.actions.options') }}</flux:button>

    <flux:menu>
        {{-- Documented menu items go here. --}}
    </flux:menu>
</flux:dropdown>
```

### Props and attributes

- No component-specific prop table on this page.
- The guide documents the project-level pattern that simple components may expose shorthand props while also supporting composable child components.
- Examples shown by the documentation include `wire:model` and `label` on `flux:input`.
- Do not infer extra props from this guide. Use component-specific documentation for actual prop, attribute, and variant names.

### Slots and child components

- Prefer composition with documented child components when customization is needed.
- Examples shown by the documentation include:
  - `flux:field`
  - `flux:label`
  - `flux:error`
  - `flux:dropdown`
  - `flux:button`
  - `flux:navmenu`
  - `flux:menu`
  - `flux:context`
  - `flux:heading`
  - `flux:text`
  - `flux:menu.submenu`
  - `flux:accordion.heading`

### Events, actions, bindings, and Livewire usage

- Flux inputs can bind to Livewire with `wire:model` as shown in the official example.
- For this project, continue to use the stricter local binding rules:
  - `wire:model.blur` for normal text fields.
  - `wire:model.change` for selects, checkboxes, radios, toggles, and time choices.
  - Debounced live bindings only for search and autocomplete fields.
- Use Livewire actions for submit buttons, menu actions, wizard steps, modal actions, and dismiss behavior. Do not move business logic into Blade while composing Flux UI.

### Livewire and Laravel usage

- Use Flux controls like native Livewire inputs: bind with `wire:model`, `wire:model.blur`, `wire:model.change`, or approved debounced search bindings.
- Keep user-facing text translated with Laravel translation keys.
- Use route helpers for internal links and actions.
- Use the concise Flux component when it covers the use case. Switch to the documented composed form only when the local layout, validation placement, or actions require it.
- Keep Flux composition in Blade, but prepare query data, DTO arrays, labels, and state in Livewire classes, services, presenters, or view models.
- For this project, Flux UI must stay compatible with Livewire class components, no Volt, no Filament, no Inertia, and mobile-first layouts.

### Styling, variants, and states

- This guide does not define component variants, sizes, or states. Use component-specific docs for those details.
- Flux styles components; the application supplies contextual spacing and layout.
- Follow the documented pattern: Flux provides component padding and styling, while the app provides margins, gaps, widths, grids, and page layout.
- Prefer Tailwind spacing utilities on wrappers or surrounding layout over overriding Flux internals.
- Let Flux use native browser features and CSS-based interactions instead of adding custom JavaScript.
- Flux documentation specifically calls out native browser features such as popover-backed dropdowns and dialog-backed modals, and CSS selectors such as `:has()`, `:not()`, and `:where()`.

### Accessibility requirements or recommendations

- Prefer Flux modal, dropdown, menu, context, and form primitives over hand-built equivalents because Flux leans on native browser behavior where available.
- Native dialog-backed modal behavior helps with focus management, accessibility, and keyboard navigation.
- Do not replace documented Flux primitives with custom JavaScript widgets unless the required behavior is not covered by Flux documentation.

### Validation, form, modal, table, navigation, or layout behavior

- For simple fields, the guide supports concise input syntax such as `flux:input` with `label`.
- For custom validation placement, use `flux:field`, `flux:label`, `flux:input`, and `flux:error`.
- For dropdown/navigation composition, wrap `flux:button` with `flux:dropdown` and use documented menu primitives.
- For context menus, compose `flux:context` with documented menu components.
- For page layout, Flux handles component styling but not contextual spacing; the app owns layout containers and spacing.

### Project rules

- Start with the simple Flux syntax for common UI.
- Move to documented composition when the UI needs custom field layout, icons, actions, or validation placement.
- Use Flux-native components before building custom Blade/Tailwind replacements.
- Keep Flux syntax brief and consistent. Prefer documented names such as `heading` over local alternatives like `title` when working with Flux child components.
- Do not introduce project-specific compound component names when a documented Flux composition already exists.
- Use browser-native and CSS-first behavior through Flux instead of adding Alpine or custom JavaScript for component behavior Flux already provides.
- Keep visible text translated through `lang` files, even inside compact Flux examples.
- In mobile-first forms, use Flux components with project spacing/layout wrappers rather than large custom form blocks.

### Mistakes to avoid

- Do not create verbose wrapper APIs when Flux already provides concise components.
- Do not add JavaScript for behavior Flux or the browser already handles.
- Do not put `@if` or other Blade control directives inside a Flux component opening tag; use dynamic attributes such as `:disabled="$disabled"`.
- Do not infer undocumented props, slots, variants, sizes, events, or child components from examples in this principles guide.
- Do not use custom markup for dropdowns, modals, menus, fields, or validation when a documented Flux component covers the need.
- Do not expect Flux to solve page-level spacing; set contextual margins, gaps, and layout in the application.

## Guide: Patterns

Source: https://fluxui.dev/docs/patterns
Reviewed: 2026-06-20

### Purpose

Documents common Flux API patterns: props vs forwarded attributes, class merging, shorthand props, data binding, component grouping, root components, and slots.

### Basic usage

```blade
<flux:button variant="primary" icon="check" type="submit">
    {{ __('actions.save') }}
</flux:button>
```

```blade
<flux:button icon="cog-6-tooth" tooltip="{{ __('navigation.settings') }}" />
```

### Props and attributes

- `variant`: visual style on components that support variants.
- `icon` and `icon:trailing`: supported components can render Heroicons without manual slot markup.
- `size`: supported controls can use documented sizes such as `sm` or `xs`.
- `kbd`: supported components can show keyboard shortcut hints.
- `inset`: supported components can apply negative margin for alignment.
- Nested props use prefixes, for example `icon:variant`.
- Use Laravel dynamic prop syntax for explicit false values, for example `:current="false"`.
- Forwarded attributes such as `x-on:*`, `autofocus`, and `class` are applied by Flux to the appropriate rendered element.

### Slots and child components

- Prefer documented props for common cases.
- Use slots when a prop is not enough, for example custom `iconTrailing` content inside `flux:input`.
- Groupable standalone components use `.group`, such as `flux:button.group`, `flux:input.group`, `flux:checkbox.group`, and `flux:radio.group`.
- Components whose children are not standalone use `.item`, such as `flux:accordion.item`, `flux:breadcrumbs.item`, and `flux:autocomplete.item`.
- Some root primitives intentionally use bare names, such as `flux:label`, `flux:error`, and `flux:table`.

### Livewire and Laravel usage

- Add `wire:model` to Flux inputs the same way as native inputs.
- Documented bindable examples include `flux:input`, `flux:checkbox`, `flux:switch`, `flux:textarea`, `flux:select`, `flux:checkbox.group`, `flux:radio.group`, and `flux:tabs`.
- Alpine `x-model` and `x-on:change` can be passed when documented behavior matches native controls.

### Styling, variants, and states

- Component classes merge with Flux classes.
- Class conflicts can require Tailwind `!`, but prefer documented variants, data-attribute overrides, published components, or a project component before leaning on important overrides.
- Flux may split attributes between wrapper and inner elements; for example `class` can apply to a wrapper while `autofocus` applies to the input.

### Project rules

- Use documented props before custom slots or class overrides.
- Use the `icon` prop with documented Heroicon names instead of hand-placing icons.
- Keep class overrides small and local; use app components for repeated compositions.

### Mistakes to avoid

- Do not assume `class` always lands on the inner input.
- Do not rely on undocumented variants or nested prop names.
- Do not put Blade directives inside Flux component opening tags.

## Guide: Theming

Source: https://fluxui.dev/docs/theming
Reviewed: 2026-06-20

### Purpose

Flux theming is based on a base gray palette and an accent color palette managed through Tailwind CSS variables.

### Basic usage

```css
@theme {
    --color-accent: var(--color-red-500);
    --color-accent-content: var(--color-red-600);
    --color-accent-foreground: var(--color-white);
}
```

```blade
<flux:button variant="primary">
    {{ __('actions.continue') }}
</flux:button>
```

### Props and attributes

- `:accent="false"` is documented for opting links, tabs, navbar items, and navlist items out of accent coloring.
- Accent variables:
  - `--color-accent`
  - `--color-accent-content`
  - `--color-accent-foreground`

### Slots and child components

- No component-specific slots on this page.

### Livewire and Laravel usage

- Keep theme changes in `resources/css/app.css`.
- Do not use per-view inline theme hacks.
- Ensure translated content remains readable in both light and dark themes.

### Styling, variants, and states

- Flux uses `zinc` as its default base color.
- If the app changes the base gray, redefine the `--color-zinc-*` variables in `@theme`.
- Accent utilities can be used as `bg-accent` and `text-accent-foreground`.

### Project rules

- Do not introduce a separate Tailwind config file for theming; this project uses Tailwind CSS v4 CSS-first configuration.
- Keep accent changes centralized.
- Prefer the project accent over one-off primary button colors unless a documented status color is semantically needed.

### Mistakes to avoid

- Do not redefine Flux theme variables inside Blade views.
- Do not use hard-to-read accent foreground combinations.
- Do not scatter custom brand colors across components.

## Guide: Dark Mode

Source: https://fluxui.dev/docs/dark-mode
Reviewed: 2026-06-20

### Purpose

Flux supports dark mode and can manage appearance by toggling the `.dark` class on the `html` element.

### Basic usage

```css
@import "tailwindcss";
@import '../../vendor/livewire/flux/dist/flux.css';
@custom-variant dark (&:where(.dark, .dark *));
```

```blade
<flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle" aria-label="{{ __('settings.toggle_dark_mode') }}" />
```

### Props and attributes

- Flux JavaScript utilities:
  - `$flux.appearance = 'light|dark|system'`
  - `$flux.dark = true|false`
- Outside Alpine, the same appearance state can be accessed through `window.Flux`.

### Slots and child components

- Documented examples use `flux:button`, `flux:dropdown`, `flux:menu`, `flux:menu.item`, `flux:switch`, `flux:radio.group`, and `flux:radio`.

### Livewire and Laravel usage

- Keep `@fluxAppearance` in the layout unless the project explicitly takes over all appearance handling.
- Keep `@fluxScripts` in the layout.
- For user settings, pair Flux appearance controls with persisted Laravel user locale/settings only when the product asks for persistence beyond Flux local storage.

### Styling, variants, and states

- Use `dark:` Tailwind variants for non-Flux markup.
- Use Flux appearance utilities for theme controls instead of custom dark-mode JavaScript.
- For compact mobile settings, a segmented `flux:radio.group` or `flux:switch` is documented.

### Project rules

- Preserve the current Flux dark-mode wiring in layouts and CSS.
- Translate all labels and `aria-label` values.
- Keep controls small enough for the 320px mobile target.

### Mistakes to avoid

- Do not remove `@fluxAppearance` without owning all dark-mode behavior.
- Do not write duplicate local-storage appearance code when Flux utilities are enough.
- Do not leave icon-only appearance buttons without accessible labels.

## Guide: Customization

Source: https://fluxui.dev/docs/customization
Reviewed: 2026-06-20

### Purpose

Flux supports customization through Tailwind classes, publishing components, and global data-attribute style overrides.

### Basic usage

```blade
<flux:select class="max-w-md" />
```

### Props and attributes

- No universal customization prop is documented.
- Most component root/inner elements expose `data-flux-*` attributes for targeted styling.

### Slots and child components

- Published components can be customized at the Blade file level after running the documented `php artisan flux:publish` command.

### Livewire and Laravel usage

- Prefer local class overrides for layout, sizing, or spacing.
- Publish Flux components only when project-level customization is unavoidable.
- If a Flux component is published, keep it compatible with Livewire and update tests around the affected UI.

### Styling, variants, and states

- Tailwind classes can be passed directly to Flux components.
- Tailwind important `!` can resolve class conflicts, but a new variant, data-attribute override, or app component is usually cleaner for repeated needs.
- Global overrides can target attributes such as `[data-flux-button]`.

### Project rules

- Use documented variants and props first.
- Use app-specific Blade wrappers for repeated project compositions.
- Keep global overrides rare and documented.

### Mistakes to avoid

- Do not publish all Flux components casually; published files no longer receive automatic package updates.
- Do not solve repeated styling needs with scattered `!` utilities.
- Do not modify vendor files directly.

## Layout: Header

Source: https://fluxui.dev/layouts/header
Reviewed: 2026-06-20

### Purpose

Provides a full-width top navigation layout for applications.

### Basic usage

```blade
<flux:header container class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
    <flux:brand :href="route('home')" :name="config('app.name')" />
    <flux:spacer />
</flux:header>

<flux:main container>
    {{ $slot }}
</flux:main>
```

### Props and attributes

- `flux:header` props:
  - `sticky`: makes the header sticky when scrolling.
  - `container`: constrains header content to container width.
- `flux:sidebar.toggle` attributes:
  - `icon`: icon displayed in the toggle button.
  - `inset`: toggle button positioning, for example `left`.
- `flux:main` props:
  - `container`: constrains main content to container width.

### Slots and child components

- `flux:header` default slot contains branding, navigation, profile controls, sidebar toggle, and related header content.
- `flux:main` default slot contains main page content.
- Header examples compose `flux:brand`, `flux:navbar`, `flux:navbar.item`, `flux:dropdown`, `flux:navmenu`, `flux:profile`, `flux:menu`, `flux:separator`, `flux:spacer`, and mobile `flux:sidebar`.

### Livewire and Laravel usage

- Use named routes for internal `href` values.
- Current nav state should be calculated in PHP or Blade variables and passed via documented `current` props on nav items.
- Keep mobile menus light; do not render huge hidden navigation trees.

### Styling, variants, and states

- Use `container` when the content should align with the page width.
- Use `sticky` only when the page needs sticky navigation.
- Use `lg:hidden` or matching breakpoint utilities consistently with sidebar behavior.

### Project rules

- Use `flux:header` for top app navigation instead of hand-built header wrappers.
- For mobile-first pages, include a documented mobile sidebar toggle only when there is sidebar navigation.
- Keep public marketplace first paint light; avoid overloading the header.

### Mistakes to avoid

- Do not hard-code demo brand names, profile names, or visible labels.
- Do not use raw links for app routes when `route()` is available.
- Do not duplicate desktop nav and mobile nav with divergent route sets.

## Layout: Sidebar

Source: https://fluxui.dev/layouts/sidebar
Reviewed: 2026-06-20

### Purpose

Provides sidebar navigation layouts for applications, including mobile-only and collapsible behavior.

### Basic usage

```blade
<flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    <flux:sidebar.header>
        <flux:sidebar.brand :href="route('home')" :name="config('app.name')" />
        <flux:sidebar.collapse class="lg:hidden" />
    </flux:sidebar.header>

    <flux:sidebar.nav>
        <flux:sidebar.item icon="home" :href="route('home')" current>
            {{ __('navigation.home') }}
        </flux:sidebar.item>
    </flux:sidebar.nav>
</flux:sidebar>
```

### Props and attributes

- `flux:sidebar` props:
  - `sticky`
  - `collapsible`: `"mobile"`, `true`, or `false`.
  - `breakpoint`: pixel or rem value, default `1024`.
  - `stashable`: deprecated; use `collapsible="mobile"`.
  - `persist`: saves desktop collapsed state to localStorage by default; set `false` to disable.
- `flux:sidebar.brand` props:
  - `href`, `logo`, `logo:dark`, `name`.
- `flux:sidebar.collapse` props:
  - `inset`, `tooltip`.
- `flux:sidebar.search` props:
  - `placeholder`.
- `flux:sidebar.item` props:
  - `href`, `icon`, `badge`, `current`, `tooltip`.
- `flux:sidebar.group` props:
  - `heading`, `expandable`, `icon`, `expanded`.
- `flux:sidebar.profile` props:
  - `avatar`, `name`.
- `flux:sidebar.toggle` props:
  - `icon`, `inset`.
- `flux:main` props:
  - `container`.

### Slots and child components

- `flux:sidebar` default slot holds header, nav, spacer, footer nav, and profile content.
- `flux:sidebar.header` default slot holds brand and collapse controls.
- `flux:sidebar.nav` default slot holds navigation items and groups.
- `flux:sidebar.item` default slot is the item text.
- `flux:sidebar.group` default slot holds nested sidebar items.
- `flux:sidebar.spacer` provides flexible spacing.
- `flux:main` default slot holds page content.

### Livewire and Laravel usage

- Keep sidebar entries translated and route-driven.
- Use badges for compact counts only when counts are already eager-loaded or precomputed.
- Do not compute counts or authorization inline in Blade.

### Styling, variants, and states

- Use `collapsible="mobile"` for mobile-only stashed sidebars.
- Use `collapsible` for desktop and mobile collapsible sidebars.
- Align custom `breakpoint` with responsive classes to avoid hidden/toggle drift.
- `current` marks active items.
- `tooltip` helps collapsed-sidebar usability.

### Project rules

- Do not build admin/staff sidebars.
- Use sidebar layouts only for authenticated guest/host navigation that benefits from persistent nav.
- For public mobile search, prefer lighter bottom navigation/drawers unless a sidebar is clearly warranted.

### Mistakes to avoid

- Do not use deprecated `stashable`.
- Do not place full countries/cities lists or large menus in `flux:sidebar.search`.
- Do not let desktop and mobile sidebar breakpoints drift.

## Component: Accordion

Source: https://fluxui.dev/components/accordion
Reviewed: 2026-06-20

### Purpose

Collapses and expands sections of content. Useful for FAQs, rule explanations, feature sections, and mobile progressive disclosure.

### Basic usage

```blade
<flux:accordion transition>
    <flux:accordion.item>
        <flux:accordion.heading>{{ __('listing.rules') }}</flux:accordion.heading>
        <flux:accordion.content>
            {{ __('listing.rules_summary') }}
        </flux:accordion.content>
    </flux:accordion.item>
</flux:accordion>
```

Shorthand:

```blade
<flux:accordion.item :heading="__('listing.rules')">
    {{ __('listing.rules_summary') }}
</flux:accordion.item>
```

### Props and attributes

- `flux:accordion` props:
  - `variant`: `reverse` displays the icon before the heading.
  - `transition`: enables expansion transitions; default `false`.
  - `exclusive`: only one item can be expanded at a time; default `false`.
- `flux:accordion.item` props:
  - `heading`: shorthand for heading content.
  - `expanded`: expanded by default; default `false`.
  - `disabled`: item cannot expand/collapse; default `false`.

### Slots and child components

- `flux:accordion.item`
- `flux:accordion.heading` default slot: heading text.
- `flux:accordion.content` default slot: expanded content.

### Livewire and Laravel usage

- Prepare all displayed content before rendering; do not query inside accordion content.
- Use translated strings for headings and body text.
- Use accordions for mobile disclosure instead of rendering many hidden sections.

### Styling, variants, and states

- Use `transition` for content-heavy mobile sections where smooth expansion helps.
- Use `exclusive` for FAQ-style groups where only one panel should be open.
- Use `expanded` for default-open sections.
- Use `disabled` for sections that are intentionally unavailable.

### Project rules

- Use Flux accordion instead of raw `<details>` and `<summary>`.
- Keep accordion content compact on 320px screens.
- Do not hide huge DOM blocks in accordion content just to defer visual display.

### Mistakes to avoid

- Do not use native `open`; use documented `expanded`.
- Do not hard-code visible FAQ/rule text.
- Do not use accordions to bypass proper step-by-step form design.

## Component: Autocomplete

Source: https://fluxui.dev/components/autocomplete
Reviewed: 2026-06-20

### Purpose

Enhances an input field with suggestions and writes the selected suggestion text directly into the input.

### Basic usage

```blade
<flux:autocomplete wire:model.live.debounce.500ms="citySearch" label="{{ __('search.city') }}">
    @foreach ($citySuggestions as $city)
        <flux:autocomplete.item>{{ $city['name'] }}</flux:autocomplete.item>
    @endforeach
</flux:autocomplete>
```

### Props and attributes

- `wire:model`: binds the input value to a Livewire property.
- `type`: HTML input type; default `text`.
- `label`, `description`, `placeholder`.
- `size`: `sm`, `xs`.
- `variant`: `filled`; default `outline`.
- `disabled`, `readonly`, `invalid`.
- `multiple`: for file inputs.
- `mask`: Alpine mask plugin pattern.
- `icon`, `icon:trailing`.
- `kbd`.
- `clearable`, `copyable`, `viewable`.
- `as`: `button`; default `input`.
- `container:class`: classes for the autocomplete container.
- `input:class`: classes for the inner input.
- `flux:autocomplete.item` prop:
  - `disabled`.

### Slots and child components

- `flux:autocomplete.item`
- `icon`, `icon:leading`, and `icon:trailing` slots.

### Livewire and Laravel usage

- Use it when the stored value is the text entered/selected.
- For country/city search, feed compact, paginated, local SQLite-backed suggestions.
- Use debounced live binding only for autocomplete/search fields.
- If the UI must show a label but store an ID, use a combobox/select-style component instead of autocomplete.

### Styling, variants, and states

- Use `container:class` for dropdown/list constraints such as max height.
- Use `input:class` only when the inner input needs direct styling.
- Use `invalid` with validation state when needed.

### Project rules

- Never load full country/city lists into autocomplete.
- Normal search must use offline imported geo data, not live external API calls.
- Keep suggestion DTOs small.

### Mistakes to avoid

- Do not use autocomplete to store hidden IDs.
- Do not pass undocumented `value` props to autocomplete items.
- Do not render thousands of suggestion items.

## Component: Avatar

Source: https://fluxui.dev/components/avatar
Reviewed: 2026-06-20

### Purpose

Displays a user image, generated initials, icon, or grouped avatars.

### Basic usage

```blade
<flux:avatar :name="$userName" :src="$avatarUrl" />
```

```blade
<flux:avatar.group>
    <flux:avatar :name="$guestName" circle size="sm" />
    <flux:avatar :name="$hostName" circle size="sm" />
</flux:avatar.group>
```

### Props and attributes

- `name`: used for display initials.
- `src`: avatar image URL.
- `initials`: custom initials; overrides name-generated initials.
- `alt`: image alternative text; defaults to `name` when provided.
- `size`: `xs` (24px), `sm` (32px), default 40px, `lg` (48px). The page also documents `xl` in examples.
- `color`: `red`, `orange`, `amber`, `yellow`, `lime`, `green`, `emerald`, `teal`, `cyan`, `sky`, `blue`, `indigo`, `violet`, `purple`, `fuchsia`, `pink`, `rose`, `auto`; default system colors.
- `color:seed`: stable seed for `color="auto"`.
- `circle`: fully circular avatar.
- `icon`, `icon:variant` (`outline`, `solid`; default `solid`).
- `tooltip`: string or true to use `name`.
- `tooltip:position`: `top`, `right`, `bottom`, `left`; default `top`.
- `badge`: string, boolean, or slot content.
- `badge:color`: same color options as `color`.
- `badge:circle`.
- `badge:position`: `top left`, `top right`, `bottom left`, `bottom right`; default `bottom right`.
- `badge:variant`: `solid`, `outline`; default `solid`.
- `as`: `button`, `div`; default `div`.
- `href`: renders the avatar as a link.
- `flux:avatar.group` supports `class` for group styling such as ring color customization.

### Slots and child components

- `flux:avatar` default slot overrides initials.
- `badge` slot supports custom badge content.
- `flux:avatar.group` default slot contains multiple `flux:avatar` components.

### Livewire and Laravel usage

- Use presenter/DTO values for display name, avatar URL, initials, and profile links.
- Use `color="auto"` with `color:seed` set from a stable ID when no uploaded avatar exists.
- Use `as="button"` only for real actions and `href` only for real profile links.

### Styling, variants, and states

- Use `circle` for user profile imagery.
- Use documented badge props for online/status/count indicators.
- Use group `class` only for background-specific ring adjustments.

### Project rules

- Do not reveal private profile data in public cards; pass privacy-filtered avatar/name DTOs.
- Always provide a meaningful `name` or `alt` equivalent for user identity display.
- Keep remote image usage behind stored, trusted URLs.

### Mistakes to avoid

- Do not hard-code demo image services.
- Do not use badge color as the only status signal when text/status is needed.
- Do not use avatars as buttons without accessible action context.

## Component: Badge

Source: https://fluxui.dev/components/badge
Reviewed: 2026-06-20

### Purpose

Highlights compact information such as status, category, count, role, or availability.

### Basic usage

```blade
<flux:badge color="lime">
    {{ __('availability.available') }}
</flux:badge>
```

```blade
<flux:badge variant="solid" color="red" icon="exclamation-triangle">
    {{ __('booking.status.cancelled') }}
</flux:badge>
```

### Props and attributes

- `color`: documented Tailwind color names; default `zinc`.
- `size`: `sm`, `lg`.
- `rounded`: fully rounded badge.
- `variant`: `solid`; deprecated `pill` should be replaced with `rounded`.
- `icon`, `icon:trailing`.
- `icon:variant`: `outline`, `solid`, `mini`, `micro`; default `mini`.
- `as`: `button`; default `div`.
- `inset`: negative margins for `top`, `bottom`, `left`, `right`, or combinations.
- `flux:badge.close` props:
  - `icon`: default `x-mark`.
  - `icon:variant`: `outline`, `solid`, `mini`, `micro`; default `mini`.

### Slots and child components

- Default slot contains badge text/content.
- `flux:badge.close` appends a close affordance.

### Livewire and Laravel usage

- Use translated enum/status labels.
- If a badge is clickable, use `as="button"` and a clear Livewire action.
- Keep counts precomputed; do not call aggregates in Blade loops.

### Styling, variants, and states

- Use `solid` for important or high-contrast status indicators.
- Use `rounded` for pill-shaped badges.
- Use `inset="top bottom"` when badges sit inline with headings or text.

### Project rules

- Use badges for booking, verification, profile, room, and availability status chips.
- Pair danger/warning badges with friendly translated explanations where the status needs action.

### Mistakes to avoid

- Do not use deprecated `variant="pill"`; use `rounded`.
- Do not use badges as the only way to communicate complex errors.
- Do not invent colors beyond documented Tailwind color names.

## Component: Brand

Source: https://fluxui.dev/components/brand
Reviewed: 2026-06-20

### Purpose

Displays the application logo and name consistently across headers and sidebars.

### Basic usage

```blade
<flux:brand :href="route('home')" :name="config('app.name')" />
```

```blade
<flux:brand :href="route('home')" :name="config('app.name')">
    <x-slot name="logo" class="bg-accent text-accent-foreground">
        R
    </x-slot>
</flux:brand>
```

### Props and attributes

- `name`: company/application name.
- `logo`: URL for logo image, or provide slot content.
- `alt`: alternative text for logo.
- `href`: target URL; default `/`.

### Slots and child components

- `logo`: custom logo content, such as image, SVG, icon, or styled text.

### Livewire and Laravel usage

- Use `config('app.name')` or translated product name from a presenter when needed.
- Use named routes for internal links.
- Keep brand visible enough in first viewport without turning pages into marketing hero layouts.

### Styling, variants, and states

- Custom logo slot can use accent theme utilities.
- For dark-mode logo changes in sidebar layouts, use documented `logo:dark` on `flux:sidebar.brand`; base `flux:brand` examples use separate dark/light instances.

### Project rules

- Use Flux brand in app header/sidebar shells.
- Do not hard-code demo brand names or external demo logo URLs.

### Mistakes to avoid

- Do not omit `alt` when using image-only branding unless the name is also visible.
- Do not use raw HTML brand blocks when Flux brand covers the need.

## Component: Button

Source: https://fluxui.dev/components/button
Reviewed: 2026-06-20

### Purpose

Provides composable buttons, icon buttons, links styled as buttons, grouped buttons, and Livewire-aware loading states.

### Basic usage

```blade
<flux:button type="submit" variant="primary">
    {{ __('actions.save') }}
</flux:button>
```

```blade
<flux:button wire:click="save" icon="check">
    {{ __('actions.save_changes') }}
</flux:button>
```

### Props and attributes

- `as`: `button`, `a`, `div`; default `button`.
- `href`: renders/uses anchor behavior when linking.
- `type`: `button`, `submit`; default `button`.
- `variant`: `outline`, `primary`, `filled`, `danger`, `ghost`, `subtle`; default `outline`.
- `size`: `base`, `sm`, `xs`; default `base`.
- `icon`, `icon:trailing`.
- `icon:variant`: `outline`, `solid`, `mini`, `micro`; default `micro`.
- `square`: equal width/height, useful for icon buttons.
- `align`: `start`, `center`, `end`; default `center`.
- `inset`: negative margins for `top`, `bottom`, `left`, `right`, or combinations.
- `loading`: shows spinner and disables the button for `wire:click` or `type="submit"`; default `true`; set `:loading="false"` to disable.
- `tooltip`, `tooltip:position` (`top`, `bottom`, `left`, `right`; default `top`), `tooltip:kbd`, `kbd`.
- `class`: additional CSS classes, commonly `w-full`.
- Attribute: `data-flux-button`.

### Slots and child components

- Default slot contains button text/content.
- `flux:button.group` default slot groups multiple `flux:button` components.

### Livewire and Laravel usage

- Buttons with `wire:click` or submit type automatically show loading behavior and disable pointer events during network requests.
- Use `type="submit"` inside forms, and let Livewire handle submit state.
- Use `href` for navigation, preferably with named routes.

### Styling, variants, and states

- Use `primary` sparingly, mostly for form submission or one main action.
- Use `danger` for destructive actions.
- Use `ghost`/`subtle` for secondary icon or dismiss actions.
- Use `square` for non-icon-only equal-size buttons; icon-only buttons square automatically.
- Use `inset` with ghost/subtle buttons for alignment next to text.

### Project rules

- Use Flux buttons for all visible app actions unless a more specific Flux control exists.
- Provide translated labels or accessible labels for icon-only buttons.
- Keep mobile tap targets comfortable.

### Mistakes to avoid

- Do not use primary for every action.
- Do not hand-roll loading spinners for normal Livewire button requests.
- Do not use `href` buttons for mutations.

## Component: Breadcrumbs

Source: https://fluxui.dev/components/breadcrumbs
Reviewed: 2026-06-20

### Purpose

Shows users their location within the app hierarchy and provides compact navigation back to parent pages.

### Basic usage

```blade
<flux:breadcrumbs>
    <flux:breadcrumbs.item :href="route('home')" icon="home" />
    <flux:breadcrumbs.item :href="route('properties.index')">{{ __('navigation.properties') }}</flux:breadcrumbs.item>
    <flux:breadcrumbs.item>{{ $propertyName }}</flux:breadcrumbs.item>
</flux:breadcrumbs>
```

### Props and attributes

- `flux:breadcrumbs.item` props:
  - `href`: link URL; omitted renders non-clickable text.
  - `icon`: icon before item text.
  - `icon:variant`: `outline`, `solid`, `mini`, `micro`; default `mini`.
  - `separator`: separator icon name; default `chevron-right`; example uses `slash`.

### Slots and child components

- `flux:breadcrumbs` default slot contains breadcrumb items.
- `flux:breadcrumbs.item` default slot contains label or custom content.
- Ellipsis dropdown examples compose `flux:dropdown`, `flux:button`, `flux:navmenu`, and `flux:navmenu.item`.

### Livewire and Laravel usage

- Build breadcrumb arrays in the Livewire class/presenter, not in Blade with queries.
- Use named routes for every internal `href`.
- Last item should normally be non-clickable current context.

### Styling, variants, and states

- Use icon-only home item when space is tight.
- Use `separator="slash"` only when slash separators match the page style.
- Use ellipsis/dropdown for long breadcrumb trails.

### Project rules

- Breadcrumbs are useful on host/property/room/sleeping-place flows where hierarchy matters.
- Keep mobile breadcrumbs short; collapse long trails.

### Mistakes to avoid

- Do not expose private object names in public breadcrumbs.
- Do not hard-code route paths.
- Do not show long unwrapped breadcrumbs on 320px screens.

## Component: Calendar

Source: https://fluxui.dev/components/calendar
Reviewed: 2026-06-20

### Purpose

Provides date selection for single dates, multiple dates, and ranges. Useful for scheduling and booking interfaces.

### Basic usage

```blade
<flux:calendar wire:model.change="checkInDate" min="today" />
```

```blade
<flux:calendar mode="range" wire:model.live="range" min="today" />
```

### Props and attributes

- `wire:model`: binds the selected date(s) to a Livewire property.
- `value`: selected date(s): single `Y-m-d`, multiple comma-separated `Y-m-d`, range `Y-m-d/Y-m-d`.
- `mode`: `single`, `multiple`, `range`; default `single`.
- `min`, `max`: date string or `today`.
- `unavailable`: comma-separated `Y-m-d` dates that cannot be selected.
- `size`: `base`, `xs`, `sm`, `lg`, `xl`, `2xl`; default `base`.
- `start-day`: `0` through `6`; default based on user locale.
- `months`: number of months; default `1`, or `2` in range mode.
- `min-range`, `max-range`.
- `open-to`, `force-open-to`.
- `navigation`: `false` hides month navigation; default `true`.
- `static`: non-interactive display-only; default `false`.
- `multiple`: enables multiple selection; default `false`.
- `week-numbers`, `selectable-header`, `with-today`, `with-inputs`.
- `locale`: locale string, for example `fr`, `en-US`, `ja-JP`.
- Attribute: `data-flux-calendar`.

### Slots and child components

- No slots are documented for `flux:calendar` on this page.
- `Flux\DateRange` object is documented for range mode.

### Livewire and Laravel usage

- For single dates, Livewire properties can be Carbon instances or `Y-m-d` strings.
- For multiple dates, use an array of `Y-m-d` strings.
- For ranges, use an associative array with `start`/`end`, or preferably `Flux\DateRange` when appropriate.
- The docs recommend `wire:model.live` when binding a range property to a `Flux\DateRange` object.
- Persisting `DateRange` in session is documented with Livewire `#[Session]`.

### Styling, variants, and states

- Use `fixed-weeks` to prevent layout shifts between months.
- Use `selectable-header` for quicker month/year navigation.
- Use `with-today` for a shortcut to the current date.
- Use `static` with `:navigation="false"` for display-only mini calendars.
- Use `locale` or default browser locale; this project should coordinate with Laravel locale.

### Project rules

- Calendar UI must remain a compact selector; booking availability, overlap, turnover, and price calculations stay in services/actions.
- Do not load full calendars into Livewire public properties.
- Use indexed availability data to produce compact unavailable/reason DTOs.
- Use project date math: overnight rentals use `[check_in_date, check_out_date)`.

### Mistakes to avoid

- Do not trust the calendar UI as the source of booking validation.
- Do not query availability inside Blade.
- Do not use inclusive checkout billing logic for overnight stays.
- Do not render huge hidden calendars for every sleeping place.

## Component: Callout

Source: https://fluxui.dev/components/callout
Reviewed: 2026-06-20

### Purpose

Highlights important information and guides users toward key actions.

### Basic usage

```blade
<flux:callout icon="information-circle" variant="secondary">
    <flux:callout.heading>{{ __('booking.notice_title') }}</flux:callout.heading>
    <flux:callout.text>{{ __('booking.notice_body') }}</flux:callout.text>
</flux:callout>
```

With actions:

```blade
<flux:callout icon="clock" inline>
    <flux:callout.heading>{{ __('booking.payment_deadline') }}</flux:callout.heading>
    <x-slot name="actions">
        <flux:button :href="route('bookings.pay', $booking)">{{ __('actions.pay_now') }}</flux:button>
    </x-slot>
</flux:callout>
```

### Props and attributes

- `flux:callout` props:
  - `icon`
  - `icon:variant`
  - `variant`: `secondary`, `success`, `warning`, `danger`; default `secondary`.
  - `color`: documented Tailwind color name.
  - `inline`: actions appear inline; default `false`.
  - `heading`: shorthand for `flux:callout.heading`.
  - `text`: shorthand for `flux:callout.text`.
- `flux:callout.heading` props:
  - `icon`: moves icon inside heading.
  - `icon:variant`.
- `flux:callout.link` props:
  - `href`
  - `external`: opens in new tab; default `false`.

### Slots and child components

- `flux:callout` slots:
  - `icon`: custom icon.
  - `actions`: buttons or links inside callout.
  - `controls`: top-right controls, such as a close button.
- `flux:callout.heading` slots:
  - default heading content.
  - `icon` custom heading icon.
- `flux:callout.text` default slot.
- `flux:callout.link`.

### Livewire and Laravel usage

- Use callouts for validation summaries, booking notices, payment deadlines, verification prompts, and availability explanations.
- Dismiss behavior is intentionally app-specific; wire it to Alpine, Livewire state, or persisted user preferences based on the product need.
- Use translated, friendly copy.

### Styling, variants, and states

- Use `success`, `warning`, and `danger` for semantic status.
- Use `inline` when actions should sit beside compact text.
- Use `color` for custom tone when the semantic variants are not specific enough.
- Place icons on the root for standard layout or on `flux:callout.heading` for compact layout.

### Project rules

- Prefer callouts over custom alert boxes.
- Keep scary states calm and actionable.
- Never introduce support/admin workflows just because a callout mentions support-like reserved fields.

### Mistakes to avoid

- Do not assume callouts dismiss themselves; implement or omit dismissal intentionally.
- Do not hard-code dates, prices, or warning text in Blade.
- Do not use danger callouts for normal informational friction.

## Component: Card

Source: https://fluxui.dev/components/card
Reviewed: 2026-06-20

### Purpose

Provides a bordered/padded container for related content such as forms, alerts, compact summaries, or lists.

### Basic usage

```blade
<flux:card class="space-y-6">
    <flux:heading size="lg">{{ __('profile.title') }}</flux:heading>
    <flux:text>{{ __('profile.summary') }}</flux:text>
</flux:card>
```

### Props and attributes

- `size="sm"` is documented in the small-card example for compact content.
- `class`: additional CSS classes; documented common uses include spacing, width control, and `p-0`.
- Attribute: `data-flux-card`.

### Slots and child components

- Default slot can contain headings, text, forms, buttons, and other components.
- Examples compose `flux:heading`, `flux:text`, `flux:input`, `flux:field`, `flux:label`, `flux:error`, `flux:link`, `flux:button`, `flux:icon`, and `flux:spacer`.

### Livewire and Laravel usage

- Use cards for repeated items, compact summaries, and form containers where a framed surface is genuinely useful.
- Keep data preloaded; never query from inside a card Blade template.
- For clickable cards, wrap with a semantic anchor and add an accessible label when needed.

### Styling, variants, and states

- Use `class="space-y-6"` or similar utilities for internal spacing.
- Use `size="sm"` for compact notifications, alerts, or summaries.
- Use hover styles on linked cards only when the card is actually interactive.

### Project rules

- Do not put cards inside cards.
- Do not turn every page section into a card; use cards for individual repeated items, modals, and genuinely framed tools.
- Keep mobile card content short and scannable.

### Mistakes to avoid

- Do not use cards as generic page wrappers.
- Do not hard-code visible form labels or action text.
- Do not place heavy galleries, maps, or large hidden filter sections inside cards.

## Component: Carousel

Source: https://fluxui.dev/components/carousel
Reviewed: 2026-06-20

### Purpose

Provides a horizontally scrolling row of slides with scroll snap behavior and edge-aware controls. Useful for compact media galleries, testimonial-like content, or small curated listing rows when a carousel is truly appropriate.

### Basic usage

```blade
<flux:carousel track:class="rounded-lg overflow-hidden">
    @foreach ($slides as $slide)
        <flux:carousel.slide class="w-full">
            <img
                src="{{ $slide['image_url'] }}"
                alt="{{ $slide['alt'] }}"
                class="aspect-16/9 rounded-lg object-cover"
            >
        </flux:carousel.slide>
    @endforeach
</flux:carousel>
```

With indicators and no arrows:

```blade
<flux:carousel indicators :arrows="false">
    @foreach ($slides as $slide)
        <flux:carousel.slide class="w-full">
            {{ $slide['content'] }}
        </flux:carousel.slide>
    @endforeach
</flux:carousel>
```

External controls:

```blade
<flux:carousel.controls name="popular-stays" />

<flux:carousel name="popular-stays" :arrows="false" track:class="px-6 scroll-px-6">
    @foreach ($stays as $stay)
        <flux:carousel.slide class="w-4/5 sm:w-1/2 md:w-1/3">
            {{ $stay['title'] }}
        </flux:carousel.slide>
    @endforeach
</flux:carousel>
```

### Props and attributes

- `flux:carousel` props:
  - `arrows`: shows previous/next controls; default `true`.
  - `arrows:position`: `inside`, `overlap`, `outside`; default `inside`.
  - `arrows:class`: classes applied to built-in arrow controls.
  - `autoplay`: automatically advances slides, pauses on hover/focus/manual interaction, and is disabled when reduced motion is preferred.
  - `autoplay:interval`: milliseconds between autoplay advances; default `5000`.
  - `indicators`: shows slide indicators below the carousel; default `false`.
  - `disabled`: disables controls and indicators.
  - `fade`: fades scrollable track edges when additional slides overflow.
  - `snap`: `proximity`, `mandatory`; default `proximity`.
  - `scroll`: `smooth`, `instant`; default `smooth`.
  - `advance`: `slide`, `page`; default `slide`.
  - `name`: unique name used to connect external controls.
  - `track:class`: classes applied to the scrollable track.
- `flux:carousel.slide` props:
  - `class`: slide classes, including width classes such as `w-full`, `w-4/5`, or `md:w-1/3`.
- `flux:carousel.controls` props:
  - `name`: target carousel name for external controls.

### Slots and child components

- `flux:carousel.slide` goes inside `flux:carousel`.
- `flux:carousel.controls` can live outside the carousel and targets a carousel by shared `name`.
- Slides can contain images, headings, text, links, cards, or compact listing content, but the data must be prepared before rendering.

### Livewire and Laravel usage

- Use compact DTO arrays for slides; do not query media, prices, ratings, or availability inside slide Blade markup.
- Use translated labels, captions, and accessible `alt` text.
- Use named routes for any links inside slides.
- For listing carousels, preload only the needed card fields and media thumbnails.
- Do not use carousel state as a source of truth for booking or search state.

### Styling, variants, and states

- Use width classes on `flux:carousel.slide` to control visible slide count.
- Use `track:class` for track padding, scroll padding, rounded clipping, or overflow handling.
- Use `indicators` when direct slide navigation is more appropriate than arrows.
- Use `:arrows="false"` when indicators or external controls should be the only navigation.
- Use `autoplay autoplay:interval="..."` sparingly; autoplay pauses on hover, focus, or manual control and respects reduced-motion preference.
- Use `fade` to hint that more slides are available.
- Use `advance="page"` when controls should move by visible pages instead of single slides.

### Project rules

- Do not use carousels for critical booking information, price transparency, rules, or validation messages.
- Keep carousel slide counts small on mobile and slow 3G.
- Use carousels for listing/media previews only when they improve scanning; prefer static first-image cards when simpler.
- Do not load full galleries on initial search pages.
- Keep images optimized, lazy where appropriate, and sized with stable aspect ratios.

### Mistakes to avoid

- Do not hard-code demo image URLs or visible text.
- Do not omit `alt` text for meaningful images.
- Do not render huge slide lists or hidden gallery payloads.
- Do not enable autoplay for essential content users must read.
- Do not use external controls without matching the same documented `name`.

## Component: Chart

Source: https://fluxui.dev/components/chart
Reviewed: 2026-06-20

### Purpose

Provides lightweight, zero-dependency charts for Livewire applications. Flux charts are assembled from composable chart components so the project can build line, area, bar, grouped bar, stacked bar, sparkline, dashboard stat, tooltip, summary, and legend layouts without adding a separate JavaScript chart library.

### Basic usage

```blade
<flux:chart :value="$chartData" class="aspect-[3/1]">
    <flux:chart.svg>
        <flux:chart.line field="visitors" class="text-pink-500 dark:text-pink-400" />

        <flux:chart.axis axis="x" field="date">
            <flux:chart.axis.line />
            <flux:chart.axis.tick />
        </flux:chart.axis>

        <flux:chart.axis axis="y">
            <flux:chart.axis.grid />
            <flux:chart.axis.tick />
        </flux:chart.axis>

        <flux:chart.cursor />
    </flux:chart.svg>

    <flux:chart.tooltip>
        <flux:chart.tooltip.heading field="date" :format="['year' => 'numeric', 'month' => 'numeric', 'day' => 'numeric']" />
        <flux:chart.tooltip.value field="visitors" :label="__('analytics.visitors')" />
    </flux:chart.tooltip>
</flux:chart>
```

Livewire-bound data:

```blade
<flux:chart wire:model="data" class="aspect-[3/1]">
    <flux:chart.svg>
        <flux:chart.bar field="revenue" class="text-blue-500" radius="0" width="85%" />
    </flux:chart.svg>
</flux:chart>
```

Sparkline:

```blade
<flux:chart :value="$trend" class="w-[5rem] aspect-[3/1]">
    <flux:chart.svg gutter="0">
        <flux:chart.line class="text-green-500 dark:text-green-400" />
    </flux:chart.svg>
</flux:chart>
```

### Props and attributes

- `flux:chart` props:
  - `wire:model`: binds the chart to a Livewire property containing chart data.
  - `value`: array of data points when not using `wire:model`; each point should be an associative array with named fields. Simple charts can also use a single array of values.
  - `class`: chart container classes, commonly `aspect-3/1`, `aspect-[3/1]`, or a fixed height.
  - Attribute: `data-flux-chart`.
- `flux:chart.svg`:
  - Required inside `flux:chart` to render chart SVG content.
  - `gutter`: controls chart padding; accepts one to four values like CSS padding shorthand. Default padding is `8px`; use `gutter="0"` for sparklines.
- `flux:chart.line` props:
  - `field`: data field to plot on the y-axis; required when plotting associative data.
  - `curve`: `smooth` (default) or `none`.
  - `class`: line styling, commonly `text-{color}`.
- `flux:chart.area` props:
  - `field`: data field to plot on the y-axis; required when plotting associative data.
  - `curve`: `smooth` (default) or `none`.
  - `class`: area styling, commonly translucent fill/text color utilities.
- `flux:chart.point` props:
  - `field`: data field to plot points for; required.
  - `class`: point styling; SVG attributes such as `r` may be used in documented examples.
- `flux:chart.bar` is documented in examples with `field`, `class`, `radius`, and `width`.
- `flux:chart.group` groups multiple `flux:chart.bar` components for grouped bar charts.
- `flux:chart.stack` stacks multiple `flux:chart.bar` components for stacked bar charts and is documented with `width`.
- `flux:chart.axis` props:
  - `axis`: `x` or `y`; required.
  - `field`: data field for x-axis labels.
  - `scale`: `categorical`, `linear`, or `time`.
  - `format`: date/number formatting options.
  - `tick-count`: target number of ticks.
  - `tick-start`: `auto`, `min`, `0`, or a number.
  - `tick-end`: `auto`, `max`, or a number.
  - `tick-values`: explicit tick values.
  - `tick-prefix`, `tick-suffix`.
- `flux:chart.axis.mark` props:
  - `position`: `top`, `bottom`, `left`, `right`.
  - `class`: tick mark styling; SVG attributes can customize size and stroke.
- `flux:chart.axis.line`, `flux:chart.axis.grid`, and `flux:chart.zero-line` support `class` and SVG line styling attributes.
- `flux:chart.axis.tick` props:
  - `format`: date/number formatting options.
  - `class`: tick label styling; SVG text attributes can customize label position.
- `flux:chart.tooltip` props:
  - `field`
  - `format`
- `flux:chart.tooltip.heading` props:
  - `field`
  - `format`
- `flux:chart.tooltip.value` props:
  - `field`
  - `format`
  - `label` is used in documented examples.
  - `prefix` is used in documented examples.
- `flux:chart.cursor` supports `class`; `type="area"` appears in a documented bar chart example.
- `flux:chart.summary` default slot contains summary content.
- `flux:chart.summary.value` props:
  - `field`
  - `fallback`
  - `format`
- `flux:chart.legend` props:
  - `field`
  - `label`
  - `format`
  - default slot can contain arbitrary content, including `flux:chart.legend.indicator`.

### Slots and child components

- `flux:chart` default slot typically contains `flux:chart.svg`, optional `flux:chart.tooltip`, optional `flux:chart.summary`, optional `flux:chart.viewport`, and optional legend markup.
- `flux:chart.svg` contains visualization pieces such as `flux:chart.line`, `flux:chart.area`, `flux:chart.point`, `flux:chart.bar`, `flux:chart.group`, `flux:chart.stack`, axes, cursor, and zero line.
- Use `flux:chart.viewport` around `flux:chart.svg` when rendering siblings such as legends or summaries outside the chart SVG.
- `flux:chart.axis` can contain `flux:chart.axis.line`, `flux:chart.axis.grid`, `flux:chart.axis.mark`, and `flux:chart.axis.tick`.
- `flux:chart.tooltip` can contain `flux:chart.tooltip.heading` and multiple `flux:chart.tooltip.value` components.
- `flux:chart.summary` can contain `flux:chart.summary.value`.
- `flux:chart.legend` can contain `flux:chart.legend.indicator`.

### Livewire and Laravel usage

- Prepare chart data in an action, service, query object, computed property, or presenter before rendering.
- Use compact arrays of named fields, for example `['date' => '2026-06-20', 'visitors' => 267]`.
- Use `wire:model` only for intentionally Livewire-bound chart data; otherwise pass a compact `:value`.
- Do not store large analytics datasets in Livewire public properties.
- Use selected columns, aggregate queries outside loops, and cached/pre-aggregated data for dashboard-style charts.
- Labels, headings, tooltip labels, summaries, empty-state text, and surrounding chart copy must use translation keys.
- For money/date values, calculate business meaning in services; use Chart `:format` only for display formatting.

### Styling, variants, and states

- Set stable dimensions with `aspect-[3/1]`, `aspect-3/1`, fixed height, or another responsive constraint.
- Use `gutter="0"` for tiny sparklines; increase gutter only when labels/strokes need room.
- Use `curve="none"` when straight segments better represent the data.
- Use `flux:chart.axis.grid` for readable chart grids.
- Use `flux:chart.cursor` and `flux:chart.tooltip` for hover detail when the chart is interactive.
- Use legends for multiple data series; wrap the SVG in `flux:chart.viewport` when the legend sits outside the chart area.
- Formatting uses browser `Intl.NumberFormat` and `Intl.DateTimeFormat` options through `:format`.

### Project rules

- Do not use charts for admin, support, finance-staff, or moderation dashboards.
- Guest/host charts must be mobile-first, lightweight, and useful on 320px screens.
- Analytics charts must use precomputed or indexed Eloquent data; no raw SQL and no queries in Blade.
- Do not expose private host/guest metrics in public charts.
- Provide textual summaries or nearby labels so key information is not available only through hover tooltips.
- Avoid chart-heavy first screens on slow 3G; lazy-load non-critical analytics where appropriate.

### Mistakes to avoid

- Do not add Chart.js, ApexCharts, or another chart library when Flux Chart covers the need.
- Do not pass huge datasets into `wire:model` or `:value`.
- Do not calculate chart totals, prices, booking availability, or date math in Blade.
- Do not rely on hover-only tooltip data for essential mobile information.
- Do not invent undocumented chart subcomponents or props.

## Component: Checkbox

Source: https://fluxui.dev/components/checkbox
Reviewed: 2026-06-20

### Purpose

Select one or multiple options from a set. Use checkboxes for boolean acknowledgements, multi-select preferences, amenity/rule filters, grouped feature choices, and select-all style bulk choices.

### Basic usage

Single checkbox with label and validation:

```blade
<flux:field variant="inline">
    <flux:checkbox wire:model.change="acceptedRules" />
    <flux:label>{{ __('booking.accept_rules') }}</flux:label>
    <flux:error name="acceptedRules" />
</flux:field>
```

Checkbox group:

```blade
<flux:checkbox.group wire:model.change="notifications" :label="__('profile.notifications')">
    <flux:checkbox :label="__('profile.notifications_push')" value="push" />
    <flux:checkbox :label="__('profile.notifications_email')" value="email" />
    <flux:checkbox :label="__('profile.notifications_app')" value="app" />
    <flux:checkbox :label="__('profile.notifications_sms')" value="sms" />
</flux:checkbox.group>
```

Checkbox cards:

```blade
<flux:checkbox.group wire:model.change="stayPreferences" :label="__('booking.stay_preferences')" variant="cards" class="max-sm:flex-col">
    <flux:checkbox
        value="quiet_area"
        :label="__('search.area_quiet')"
        :description="__('search.area_quiet_description')"
    />

    <flux:checkbox
        value="near_metro"
        :label="__('search.near_metro')"
        :description="__('search.near_metro_description')"
    />
</flux:checkbox.group>
```

Custom card content:

```blade
<flux:checkbox.group wire:model.change="ruleChoices" :label="__('host.rules')">
    <flux:checkbox value="no_smoking">
        <flux:checkbox.indicator />

        <div class="flex-1">
            <flux:heading class="leading-4">{{ __('rules.no_smoking') }}</flux:heading>
            <flux:text size="sm" class="mt-2">{{ __('rules.no_smoking_help') }}</flux:text>
        </div>
    </flux:checkbox>
</flux:checkbox.group>
```

Check-all:

```blade
<flux:checkbox.group wire:model.change="selectedAmenities">
    <flux:checkbox.all :label="__('common.select_all')" />
    <flux:checkbox value="wifi" :label="__('amenities.wifi')" />
    <flux:checkbox value="locker" :label="__('amenities.locker')" />
    <flux:checkbox value="laundry" :label="__('amenities.laundry')" />
</flux:checkbox.group>
```

### Props and attributes

- `flux:checkbox` props:
  - `wire:model`: binds the checkbox to a Livewire property.
  - `label`: label text displayed next to the checkbox; when provided, Flux wraps the checkbox in a structure with an adjacent label.
  - `description`: help text displayed below the checkbox; when used with `label`, it appears between the label and checkbox.
  - `value`: value associated with the checkbox when used in a group; checked values are included in the array returned by the group's `wire:model`.
  - `checked`: sets the checkbox to be checked by default.
  - `indeterminate`: sets the checkbox to an indeterminate dash state; useful for select-all when only some children are selected.
  - `disabled`: prevents interaction.
  - `invalid`: applies error styling.
- `flux:checkbox` attributes:
  - `data-flux-checkbox`: applied to the root element.
  - `data-checked`: applied when checked.
  - `data-indeterminate`: applied when indeterminate.
- `flux:checkbox.group` props:
  - `wire:model`: binds the group to a Livewire property; the value is an array of selected checkbox values.
  - `label`: label displayed above the group; when provided, Flux wraps the group in a `flux:field` with an adjacent `flux:label`.
  - `description`: help text below the group label.
  - `variant`: `default`, `cards` (Pro), `pills` (Pro), or `buttons` (Pro).
  - `disabled`: prevents interaction with all checkboxes in the group.
  - `invalid`: applies error styling to all checkboxes in the group.
- `flux:checkbox.all` props:
  - `label`
  - `description`
  - `disabled`
- `icon` is used in documented card and button examples on individual `flux:checkbox` items.

### Slots and child components

- `flux:checkbox.group` default slot contains grouped content, usually `flux:checkbox`, `flux:checkbox.all`, and optional supporting elements.
- `flux:checkbox` supports custom card content through its default slot.
- Use `flux:checkbox.indicator` inside custom checkbox card content to render the checkbox indicator.
- Use `flux:fieldset`, `flux:legend`, and `flux:description` for horizontally arranged related checkboxes when that structure is clearer than `flux:checkbox.group`.

### Livewire and Laravel usage

- Official examples use `wire:model`; in this project, prefer `wire:model.change` for checkboxes so updates happen on change instead of live typing-style updates.
- The documentation allows `wire:model` on `flux:checkbox.group` or on individual `flux:checkbox` components; use group-level binding for one multi-select set and individual bindings for independent booleans.
- Bind single boolean choices to boolean Livewire properties.
- Bind checkbox groups to small arrays of selected values.
- Keep option lists compact in Livewire public properties; load large filter vocabularies from cached lookup data or paginated/lazy UI.
- Validate server-side. Use `flux:error` when the checkbox is wrapped in `flux:field`, or pass `invalid` when rendering lower-level invalid states directly.
- Use translated labels and descriptions. Do not hard-code visible checkbox text in Blade or Livewire components.
- Use stable enum/string values for group `value` attributes; do not use translated labels as submitted values.

### Styling, variants, and states

- `checked` marks a checkbox checked by default.
- `disabled` prevents interaction.
- `indeterminate` represents a partial selection state.
- `invalid` applies error styling.
- Group variants are `default`, `cards` (Pro), `pills` (Pro), and `buttons` (Pro).
- Use `variant="cards"` for bordered card choices. Add `class="max-sm:flex-col"` to swap to a vertical mobile layout, or `class="flex-col"` for always-vertical cards.
- Use `variant="pills"` for compact tag-like multi-select filters.
- Use `variant="buttons"` for toolbar-like feature toggles.
- Cards may include documented icons on checkbox items.

### Project rules

- Prefer `flux:checkbox` and `flux:checkbox.group` over custom checkbox markup.
- Always provide an accessible label through `label`, `flux:label`, or grouped `flux:legend`/`flux:description` structure.
- Use checkboxes for multiple selections or independent boolean acknowledgements. Use `flux:radio` when exactly one option must be selected, and `flux:switch` for a single on/off setting that behaves like a toggle.
- For search filters, prefer `variant="pills"` or compact grouped checkboxes inside progressive drawers/bottom sheets.
- For host setup or booking rules, prefer clear groups with labels, descriptions, validation, and translated help text.
- Keep mobile tap targets comfortable and avoid rendering huge hidden checkbox lists.
- Do not use check-all controls for destructive or privacy-sensitive actions without a confirmation step.

### Mistakes to avoid

- Do not place large country, city, amenity, or rule catalogs directly into a checkbox group on first render.
- Do not use translated labels as checkbox values.
- Do not use `checked` as the source of truth after Livewire owns the state; initialize the Livewire property instead.
- Do not invent variants beyond `default`, `cards`, `pills`, and `buttons`.
- Do not build custom card checkbox UI when `variant="cards"` or the documented custom-content slot covers the need.
- Do not hard-code checkbox labels, descriptions, or validation text.

## Component: Color Picker

Source: https://fluxui.dev/components/color-picker
Reviewed: 2026-06-20

### Purpose

Flux Pro color picker lets users choose a CSS color from a swatch palette, saturation/value area, hue slider, optional alpha slider, typed/pasted color value, or browser dropper. Use it for host-facing theme, brand, listing accent, or visual preference settings where a color value is truly user-configurable.

### Basic usage

Basic Livewire-bound picker:

```blade
<flux:color-picker
    wire:model="themeColor"
    :label="__('settings.theme_color')"
    :description="__('settings.theme_color_help')"
/>
```

Format and alpha-aware values:

```blade
<flux:color-picker wire:model="brandColor" format="hex" />
<flux:color-picker wire:model="overlayColor" format="rgba" />
```

Button trigger:

```blade
<flux:color-picker wire:model="markerColor" type="button" />
```

Custom swatches with accessible labels:

```blade
<flux:color-picker
    wire:model="brandColor"
    :swatches="[
        ['#ef4444', __('colors.red')],
        ['#22c55e', __('colors.green')],
        ['#3b82f6', __('colors.blue')],
    ]"
/>
```

Custom popover layout:

```blade
<flux:color-picker wire:model="accentColor">
    <div class="flex flex-col gap-3">
        <flux:color-picker.input placeholder="#000000" />
        <flux:separator />
        <flux:color-picker.area />
        <flux:color-picker.slider channel="hue" />
    </div>
</flux:color-picker>
```

### Props and attributes

- `wire:model`: binds the picker to a Livewire property. The value is a CSS color string in the configured format.
- `value`: initial value as any CSS color string; display is auto-normalized to the configured format.
- `name`: generates a hidden form input for native form submission when `wire:model` is not used.
- `format`: `hex` (default), `hexa`, `rgb`, `rgba`, `hsl`, or `hsla`. Alpha-aware formats automatically render an alpha slider in the popover.
- `type`: `input` (default) or `button`. The input trigger lets users type or paste a color; the button trigger shows the value and opens the popover on click.
- `placeholder`: placeholder for the input trigger; default is format-specific, such as `#000000` for `hex`.
- `size`: `sm` or `xs`; default is base.
- `:swatches`: array of hex strings, array of `[value, label]` pairs, or `false` to hide the default swatch grid.
- `dropper`: renders a screen color-picker button using the browser EyeDropper API and is automatically disabled when unsupported; default `false`.
- `clearable`: renders a clear button on the trigger when a value is set; default `false`.
- `copyable`: renders a clipboard-copy button on the trigger; only available with the default input trigger; default `false`.
- `label`: field label forwarded to `flux:with-field`.
- `description`: field description forwarded to `flux:with-field`.
- `disabled`: disables the picker; default `false`.
- `invalid`: marks the trigger invalid and is auto-detected from the field error bag when bound with `wire:model`.

### Slots and child components

- `flux:color-picker` default slot can contain a custom popover layout.
- `flux:color-picker.area` renders the saturation/value area and supports `class`.
- `flux:color-picker.slider` renders a channel slider with `channel="hue"` (default) or `channel="alpha"`.
- `flux:color-picker.input` renders the text input and supports `placeholder`.
- `flux:color-picker.swatches` renders the swatches container and supports `class`.
- `flux:color-picker.swatch` supports:
  - `value`: CSS color string, for example `#ef4444`.
  - `label`: human-readable label used as the aria-label and hover tooltip; defaults to `value`.
- `flux:color-picker.dropper` supports `class` and is automatically disabled in unsupported browsers.

### Livewire and Laravel usage

- Store selected colors as strings in a documented format. Prefer `format="hex"` for simple opaque theme colors and `rgba`/`hsla` only when alpha is required.
- Validate color values server-side before saving. The picker normalizes display, but validation still belongs in Laravel/Livewire.
- Use `wire:model` as documented for Livewire-owned settings, or `name` for native form submission without Livewire binding.
- Keep labels, descriptions, surrounding help text, and swatch labels translated.
- Use `[value, label]` swatch pairs when custom swatches need accessible names.
- Keep user-selected colors out of pricing, availability, booking status, and other business rules; they are presentation settings only.

### Styling, variants, and states

- Formats are `hex`, `hexa`, `rgb`, `rgba`, `hsl`, and `hsla`.
- Alpha-aware formats are `hexa`, `rgba`, and `hsla`; they automatically show an alpha slider.
- Sizes are base, `sm`, and `xs`.
- Trigger types are `input` and `button`.
- Use `:swatches="false"` when the default swatch grid is not useful.
- Use `dropper` only as progressive enhancement because browser support is optional.
- Use `clearable` when no color is a valid state.
- Use `copyable` only with the default input trigger.
- Use `disabled` to prevent interaction and `invalid` for error styling.

### Project rules

- Prefer `flux:color-picker` over custom color inputs when users choose colors.
- Do not use color picker on core booking, payment, availability, identity, or policy flows unless color choice is actually the feature.
- Keep color choices constrained with project swatches when the value affects branding or listing display.
- Check chosen colors for contrast before using them as text, badge, or button colors in guest-facing UI.
- Do not let host-selected colors override Flux semantic states for danger, success, warning, validation, or booking status.
- On mobile, prefer the default input trigger for editable values and `type="button"` for compact swatch-style controls.

### Mistakes to avoid

- Do not add another JavaScript color picker library when Flux Color Picker covers the need.
- Do not rely on `dropper` as the only way to choose a color.
- Do not use `copyable` with `type="button"`; the documentation says copyable is only available with the default input trigger.
- Do not store translated color names as the saved value; store the CSS color string.
- Do not accept arbitrary user colors into CSS without validation and escaping through normal Blade output.
- Do not invent undocumented formats, sizes, trigger types, or child components.

## Component: Command

Source: https://fluxui.dev/components/command
Reviewed: 2026-06-20

### Purpose

Flux Pro Command provides a searchable list of commands. Use it for quick-access command palettes, compact action search, and shortcuts to frequently used guest or host actions.

### Basic usage

Inline command palette:

```blade
<flux:command>
    <flux:command.input :placeholder="__('command.search')" />

    <flux:command.items>
        <flux:command.item wire:click="createBookingRequest" icon="document-plus">
            {{ __('command.create_booking_request') }}
        </flux:command.item>

        <flux:command.item wire:click="openTrips" icon="book-open" kbd="T">
            {{ __('command.open_trips') }}
        </flux:command.item>

        <flux:command.item wire:click="openSettings" icon="cog-6-tooth" kbd="S">
            {{ __('command.settings') }}
        </flux:command.item>
    </flux:command.items>
</flux:command>
```

As a modal:

```blade
<flux:modal.trigger name="search" shortcut="cmd.k">
    <flux:input as="button" :placeholder="__('command.search')" icon="magnifying-glass" kbd="⌘K" />
</flux:modal.trigger>

<flux:modal name="search" variant="bare" class="w-full max-w-[30rem] my-[12vh] max-h-screen overflow-y-hidden">
    <flux:command class="border-none shadow-lg inline-flex flex-col max-h-[76vh]">
        <flux:command.input :placeholder="__('command.search')" closable />

        <flux:command.items>
            <flux:command.item wire:click="openSavedSearches" icon="book-open">
                {{ __('command.saved_searches') }}
            </flux:command.item>

            <flux:command.item wire:click="openFavorites" icon="newspaper">
                {{ __('command.favorites') }}
            </flux:command.item>
        </flux:command.items>
    </flux:command>
</flux:modal>
```

### Props and attributes

- `flux:command`:
  - Root command palette component that wraps the input and items.
  - Attribute: `data-flux-command`, applied to the root element.
- `flux:command.input` props:
  - `clearable`: displays a clear button when the input has content.
  - `closable`: displays a close button to dismiss the command palette.
  - `icon`: icon displayed at the start of the input; default is `magnifying-glass`.
  - `placeholder`: placeholder text displayed when the input is empty.
- `flux:command.items`:
  - Container for command items.
  - No props available.
- `flux:command.item` props:
  - `icon`: icon displayed at the start of the item.
  - `icon:variant`: `outline` (default), `solid`, `mini`, or `micro`.
  - `kbd`: keyboard shortcut hint displayed at the end of the item.
  - Attribute: `data-flux-command-item`, applied to the item element.

### Slots and child components

- `flux:command` contains `flux:command.input` and `flux:command.items`.
- `flux:command.items` contains one or more `flux:command.item` children.
- `flux:command.item` default slot is the visible command label.
- The documented modal pattern uses `flux:modal.trigger`, `flux:input as="button"`, `flux:modal name="search" variant="bare"`, and a `flux:command` inside the modal.

### Livewire and Laravel usage

- Use `wire:click` on `flux:command.item` for Livewire actions, as shown in the official examples.
- Keep command actions small, explicit, and authorized server-side.
- Use translated placeholders and command labels. Do not hard-code visible command text.
- Use command palettes for quick navigation or action discovery, not for core booking forms, payment confirmation, or destructive workflows.
- Keep command item lists compact. For searchable data such as cities, properties, or guests, use documented autocomplete/search components and server-backed pagination instead of a huge command item list.
- Only show commands the current user is allowed to run; do not rely on hiding alone for authorization.

### Styling, variants, and states

- `flux:command.input` can be `clearable` and/or `closable`.
- `flux:command.input` uses `magnifying-glass` as the default icon unless an `icon` is provided.
- `flux:command.item` supports icon variants `outline`, `solid`, `mini`, and `micro`.
- `kbd` displays a keyboard shortcut hint; it is display-only guidance and must match any real shortcut behavior implemented elsewhere.
- The documented modal layout constrains width and height with classes such as `max-w-[30rem]`, `max-h-screen`, and `max-h-[76vh]`.

### Project rules

- Prefer `flux:command` over custom command-palette markup when building quick action search.
- On mobile, command palettes must fit 320px screens and avoid tall unbounded lists.
- Use `closable` for modal command inputs so mobile users have an obvious way out.
- Use icons only from documented Flux/Heroicons names already verified in this project.
- Do not expose admin, support, finance, moderation, or staff commands because those surfaces are prohibited in this project.
- Treat command palettes as convenience shortcuts; every important command must still have a discoverable normal UI path.

### Mistakes to avoid

- Do not invent props for `flux:command`, `flux:command.items`, or `flux:command.item`.
- Do not put huge search result datasets directly inside `flux:command.items`.
- Do not use command palettes for destructive actions without a confirmation flow.
- Do not show `kbd` hints that are not actually wired or meaningful for the current platform.
- Do not hard-code command labels, placeholders, or empty-state text.
- Do not rely on client-visible command filtering as authorization.

## Component: Context

Source: https://fluxui.dev/components/context
Reviewed: 2026-06-20

### Purpose

Flux Pro Context creates dropdown menus that open when right-clicking a designated area. It composes with `flux:menu`; the Context page points to the Dropdown documentation for detailed menu behavior.

### Basic usage

```blade
<flux:context>
    <flux:card class="border-dashed border-2 px-16">
        <flux:text>{{ __('context.right_click') }}</flux:text>
    </flux:card>

    <flux:menu>
        <flux:menu.item wire:click="createPost" icon="plus">
            {{ __('context.new_post') }}
        </flux:menu.item>

        <flux:menu.separator />

        <flux:menu.item wire:click="deletePost" variant="danger" icon="trash">
            {{ __('context.delete') }}
        </flux:menu.item>
    </flux:menu>
</flux:context>
```

Context menu with documented positioning props:

```blade
<flux:context position="bottom end" gap="4" offset="0 8" detail="listing-card">
    <flux:card>
        <flux:text>{{ __('listings.context_area') }}</flux:text>
    </flux:card>

    <flux:menu>
        <flux:menu.item wire:click="favorite" icon="plus">
            {{ __('listings.favorite') }}
        </flux:menu.item>
    </flux:menu>
</flux:context>
```

### Props and attributes

- `wire:model`: binds the context menu's state to a Livewire property.
- `position`: controls the menu position relative to the click position. Format is `[vertical] [horizontal]`.
  - Vertical options: `top`, `bottom` (default).
  - Horizontal options: `start`, `center`, `end` (default).
- `gap`: distance in pixels between the menu and click position. Default is `4`.
- `offset`: additional offset in pixels along both axes. Format is `[x] [y]`.
- `target`: ID of an external element to use as the menu when the menu must live outside the context element's DOM tree.
- `detail`: custom value stored in the menu's `data-detail` attribute for styling or behavior based on the source of the context menu.
- `disabled`: prevents the context menu from being shown on right-click.

### Slots and child components

- `flux:context` default slot uses a strict order:
  - The first child element is the trigger area that shows the menu when right-clicked.
  - The second child element should be a `flux:menu` that appears as the context menu.
- The official example uses `flux:menu`, `flux:menu.item`, `flux:menu.separator`, `flux:menu.submenu`, `flux:menu.radio.group`, `flux:menu.radio`, and `flux:menu.checkbox`.
- Check the Dropdown component documentation before extending menu behavior beyond the Context page examples.

### Livewire and Laravel usage

- Use `wire:click` on menu items for Livewire actions when the context menu performs server-side behavior.
- Authorize every action server-side. Context menus are convenience UI, not an authorization boundary.
- Keep menu labels, submenu headings, and any trigger-area text translated.
- Use `disabled` when a contextual menu is temporarily unavailable instead of rendering a misleading active menu.
- Use `detail` only for small styling or behavior identifiers; do not store sensitive data in visible attributes.
- Keep action payloads as IDs or compact values and resolve models server-side.

### Styling, variants, and states

- Default position is effectively `bottom end`.
- Use `position` for menu placement and `gap`/`offset` for spacing.
- Use `target` when the menu must be outside the trigger area's DOM tree.
- Use `detail` for source-specific styling or behavior via `data-detail`.
- Use `disabled` to prevent showing the menu on right-click.
- Menu styling and variants come from the composed `flux:menu` components, not from `flux:context` itself.

### Project rules

- Use `flux:context` only as a secondary shortcut, never as the only way to access an important action.
- Because this project is mobile-first, every context-menu action must also be reachable through visible buttons, menus, or detail screens.
- Avoid context menus on primary guest booking, payment, cancellation, identity verification, or complaint flows.
- Use context menus sparingly for host/listing management shortcuts where pointer devices are likely.
- Do not include admin, staff, support, finance, or moderation actions.
- Destructive context-menu actions must route through confirmation and policy checks.

### Mistakes to avoid

- Do not assume right-click menus are discoverable or available on mobile devices.
- Do not put business logic in the Blade menu; call Livewire actions or routes that delegate to actions/services.
- Do not use `detail` to expose private model data.
- Do not invent extra `flux:context` props beyond the documented set.
- Do not use a custom context-menu implementation when `flux:context` plus `flux:menu` covers the behavior.
- Do not rely on hiding context menu items as authorization.

## Component: Composer

Source: https://fluxui.dev/components/composer
Reviewed: 2026-06-20

### Purpose

Flux Pro Composer is a configurable message input with support for action buttons and rich text. It is intended for chat interfaces, AI prompts, and message-like input flows.

### Basic usage

Basic message composer:

```blade
<form wire:submit="send">
    <flux:composer
        wire:model="prompt"
        :label="__('messages.prompt')"
        label:sr-only
        :placeholder="__('messages.prompt_placeholder')"
    >
        <x-slot name="actionsLeading">
            <flux:button size="sm" variant="subtle" icon="paper-clip" />
            <flux:button size="sm" variant="subtle" icon="slash" />
            <flux:button size="sm" variant="subtle" icon="adjustments-horizontal" />
        </x-slot>

        <x-slot name="actionsTrailing">
            <flux:button size="sm" variant="filled" icon="microphone" />
            <flux:button type="submit" size="sm" variant="primary" icon="paper-airplane" />
        </x-slot>
    </flux:composer>
</form>
```

Inline compact layout:

```blade
<flux:composer
    wire:model="prompt"
    rows="1"
    inline
    :label="__('messages.prompt')"
    label:sr-only
    :placeholder="__('messages.prompt_placeholder')"
>
    <x-slot name="actionsLeading">
        <flux:button size="sm" variant="ghost" icon="plus" />
    </x-slot>

    <x-slot name="actionsTrailing">
        <flux:button type="submit" size="sm" variant="primary" icon="paper-airplane" />
    </x-slot>
</flux:composer>
```

Rich text input slot:

```blade
<flux:composer
    wire:model="prompt"
    rows="3"
    :label="__('messages.prompt')"
    label:sr-only
    :placeholder="__('messages.prompt_placeholder')"
>
    <x-slot name="input">
        <flux:editor variant="borderless" toolbar="bold italic bullet ordered | link | align" />
    </x-slot>

    <x-slot name="actionsTrailing">
        <flux:button type="submit" size="sm" variant="primary" icon="paper-airplane" />
    </x-slot>
</flux:composer>
```

### Props and attributes

- `wire:model`: binds the composer to a Livewire property.
- `name`: name attribute for the composer; used for validation error detection.
- `placeholder`: placeholder text displayed when the input is empty.
- `label`: label text for the composer. When provided, wraps the composer in a `flux:field` with an adjacent `flux:label`.
- `label:sr-only`: visually hides the label while keeping it available to screen readers.
- `description`: help text displayed near the composer.
- `description:sr-only`: visually hides the description while keeping it available to screen readers.
- `rows`: number of visible text lines for the input area. Default is `2`.
- `max-rows`: maximum number of rows the input can expand to as content grows.
- `inline`: displays action buttons alongside the input in a single row.
- `submit`: keyboard behavior for form submission. Options are `cmd+enter` (default) and `enter`.
- `disabled`: prevents user interaction with the composer.
- `invalid`: applies error styling to the composer when true.
- `variant="input"` is documented for rendering the composer with border radiuses that match other form inputs.
- Attribute: `data-flux-composer`, applied to the root element.

### Slots and child components

- `input`: custom input content; use this to replace the default textarea with a rich text editor.
- `header`: content displayed above the input area, useful for file previews or uploads.
- `footer`: content displayed below the input area.
- `actionsLeading`: buttons or actions displayed at the start of the action bar.
- `actionsTrailing`: buttons or actions displayed at the end of the action bar, typically including the submit button.
- The rich text example uses `flux:editor variant="borderless"` inside the `input` slot.
- The documented action examples use `flux:button` with `size="sm"` and variants such as `subtle`, `ghost`, `filled`, and `primary`.

### Livewire and Laravel usage

- Wrap Composer in a form with `wire:submit` for submit actions, as shown in the official examples.
- Validate message input server-side. Validation errors automatically apply invalid styling when paired with `name` or `wire:model` and `label`.
- Use `label` with `label:sr-only` when the visual layout should be compact but the input still needs an accessible label.
- Keep placeholders, labels, descriptions, button text, attachment labels, and validation messages translated.
- Do not use `wire:model.live` for long guest/host messages; submit through the form or update intentionally.
- Keep public Livewire properties small. Do not store full attachment files, large previews, or long conversation histories in the Composer-bound property.
- Use the `header` slot for compact file previews only when upload behavior is implemented with normal Laravel/Livewire validation.

### Styling, variants, and states

- `inline` places action buttons alongside the input for a compact single-row layout.
- `variant="input"` matches other form-input border radiuses.
- `rows` and `max-rows` control initial and maximum input height.
- Default keyboard submit behavior is `Ctrl`/`Cmd` + `Enter`; `submit="enter"` submits on Enter alone.
- Use `disabled` to prevent interaction.
- Use `invalid` for manual error styling when needed.
- `header`, `footer`, `actionsLeading`, and `actionsTrailing` slots allow layout composition without custom wrapper components.

### Project rules

- Prefer `flux:composer` over custom textarea/action-bar markup for chat, guest-host messages, AI prompt, and similar message inputs.
- Keep Composer mobile-first: one primary submit action, minimal leading actions, and no crowded toolbar on 320px screens.
- Use `submit="enter"` only where Enter-to-send is explicitly desired and safe; otherwise keep the default `cmd+enter` behavior.
- Do not use Composer for booking forms, payment forms, search filters, or structured data collection where normal Flux inputs/selects/checkboxes are clearer.
- Rich text composer usage must be deliberate. Plain text is preferred for booking messages, complaints, host responses, and support-adjacent notes unless rich formatting is explicitly required.
- Attachments in the `header` slot must respect upload validation, size limits, localization, and mobile performance rules.

### Mistakes to avoid

- Do not build a custom message composer when `flux:composer` covers the input/action layout.
- Do not hide labels without `label:sr-only`; keep accessible labels present.
- Do not use `submit="enter"` for long-form text where users expect Enter to create a new line.
- Do not put pricing, availability, booking validation, or business calculations in Composer Blade slots.
- Do not render large attachment previews or message history inside the Composer component.
- Do not invent undocumented Composer props, slots, variants, or submit modes.

## Component: Date Picker

Source: https://fluxui.dev/components/date-picker
Reviewed: 2026-06-20

### Purpose

Flux Pro Date Picker lets users select single dates or date ranges through a calendar overlay. It is suitable for filtering data, scheduling events, booking date choices, and range-based dashboards. The documentation explicitly recommends normal date inputs instead of date pickers for far-future or past events such as birthdates.

### Basic usage

Single date:

```blade
<flux:date-picker
    wire:model="checkInDate"
    :label="__('booking.check_in_date')"
    :placeholder="__('booking.select_check_in_date')"
/>
```

Initial value:

```blade
<flux:date-picker value="2026-06-20" />
```

Input trigger:

```blade
<flux:date-picker type="input" />
```

Range picker:

```blade
<flux:date-picker mode="range" wire:model="stayRange" />
```

Initial range:

```blade
<flux:date-picker mode="range" value="2026-06-20/2026-06-27" />
```

Range with separate inputs:

```blade
<flux:date-picker mode="range">
    <x-slot name="trigger">
        <div class="flex flex-col sm:flex-row gap-6 sm:gap-4">
            <flux:date-picker.input variant="custom" :label="__('booking.start_date')" />
            <flux:date-picker.input variant="custom" :label="__('booking.end_date')" />
        </div>
    </x-slot>
</flux:date-picker>
```

Presets:

```blade
<flux:date-picker mode="range" with-presets />

<flux:date-picker
    mode="range"
    presets="today yesterday thisWeek last7Days thisMonth yearToDate allTime custom"
/>
```

Unavailable dates:

```blade
<flux:date-picker unavailable="2026-06-19,2026-06-21" />
```

### Props and attributes

- `wire:model`: binds the date picker to a Livewire property.
- `value`: selected date or dates. Format depends on `mode`: single date is `Y-m-d`; range is `Y-m-d/Y-m-d`.
- `type`: trigger type. Options: `button` (default), `input`.
- `mode`: selection mode. Options: `single` (default), `range`.
- `min-range`: minimum number of days selectable in range mode.
- `max-range`: maximum number of days selectable in range mode.
- `min`: earliest selectable date. Can be a date string or `today`.
- `max`: latest selectable date. Can be a date string or `today`.
- `unavailable`: comma-separated list of unavailable dates in `Y-m-d` format.
- `open-to`: date the picker opens to when there is no selected date.
- `force-open-to`: forces the picker to open to `open-to` regardless of the selected date. Default `false`.
- `months`: number of months to display. Default is `1` in single mode and `2` in range mode.
- `label`: label text displayed above the date picker; when provided, wraps the component in `flux:field` with `flux:label`.
- `description`: help text below the date picker; when provided with `label`, appears between label and picker within the field wrapper.
- `description:trailing`: displays the description below the date picker instead of above it.
- `badge`: badge text displayed at the end of `flux:label` when `label` is provided.
- `placeholder`: placeholder text shown when no date is selected. Default depends on `mode`.
- `size`: calendar day cell size. Options: `sm`, `default`, `lg`, `xl`, `2xl`.
- `start-day`: calendar first day of week. Options: `0` (Sunday) through `6` (Saturday). Default is based on the user's locale.
- `week-numbers`: displays week numbers.
- `selectable-header`: displays month and year dropdowns for quick navigation.
- `with-today`: displays a button to navigate to or select today's date.
- `with-inputs`: displays date inputs at the top of the calendar. Options: `custom` (recommended), `native` (default). The docs note `custom` will become the default in a future version.
- `with-confirmation`: requires confirmation before applying selected dates.
- `with-presets`: displays preset date ranges; use with `presets` to customize options.
- `presets`: space-separated list of preset date ranges. Default: `today yesterday thisWeek last7Days thisMonth yearToDate allTime`.
- `clearable`: displays a clear button when a date is selected.
- `disabled`: prevents user interaction.
- `invalid`: applies error styling.
- `locale`: sets the picker locale, such as `fr`, `en-US`, or `ja-JP`. By default, Date Picker uses the browser locale.
- Attribute: `data-flux-date-picker`, applied to the root element.

### Slots and child components

- `trigger`: custom trigger element that opens the date picker, usually `flux:date-picker.input` or `flux:date-picker.button`.
- `flux:date-picker.input` props:
  - `variant`: `custom` (recommended) or `native` (default).
  - `label`
  - `description`
  - `placeholder`
  - `clearable`
  - `disabled`
  - `invalid`
  - `size`: `sm` or `xs`.
- `flux:date-picker.button` props:
  - `placeholder`
  - `size`: `sm` or `xs`.
  - `clearable`
  - `disabled`
  - `invalid`

### DateRange object

- For range selection, Flux provides `Flux\DateRange`, a specialized object that extends `CarbonPeriod`.
- Range values can also be an associative array of `Y-m-d` strings:
  - `start`
  - `end`
  - with presets, optional `preset`
- The documentation recommends `Flux\DateRange` for range selection because it provides useful functionality.
- Useful instance methods documented:
  - `$range->start()`
  - `$range->end()`
  - `$range->days()`
  - `$range->preset()`
  - `$range->toArray()`
- Static constructors documented in the reference:
  - `DateRange::today()`
  - `DateRange::yesterday()`
  - `DateRange::thisWeek()`
  - `DateRange::lastWeek()`
  - `DateRange::last7Days()`
  - `DateRange::last30Days()`
  - `DateRange::thisMonth()`
  - `DateRange::lastMonth()`
  - `DateRange::thisQuarter()`
  - `DateRange::lastQuarter()`
  - `DateRange::thisYear()`
  - `DateRange::lastYear()`
  - `DateRange::yearToDate()`
  - `DateRange::tomorrow()`
  - `DateRange::nextWeek()`
  - `DateRange::next7Days()`
  - `DateRange::next30Days()`
  - `DateRange::nextMonth()`
  - `DateRange::nextQuarter()`
  - `DateRange::nextYear()`
  - `DateRange::allTime()`
- The docs also show constructing with `new DateRange($start, $end)`, persisting via Livewire's `#[Session]`, and using the range anywhere Eloquent utilities accept a `CarbonPeriod`.

### Presets

- `with-presets` enables preset range choices.
- `presets` accepts a space-separated list and controls which presets appear and in what order.
- Available preset keys documented:
  - `today`
  - `yesterday`
  - `thisWeek`
  - `lastWeek`
  - `last7Days`
  - `thisMonth`
  - `lastMonth`
  - `thisQuarter`
  - `lastQuarter`
  - `thisYear`
  - `lastYear`
  - `last14Days`
  - `last30Days`
  - `last3Months`
  - `last6Months`
  - `yearToDate`
  - `tomorrow`
  - `nextWeek`
  - `next7Days`
  - `nextMonth`
  - `nextQuarter`
  - `nextYear`
  - `next14Days`
  - `next30Days`
  - `next3Months`
  - `next6Months`
  - `allTime`
  - `custom`
- When using the `allTime` preset in the picker, specify a `min` date so the preset has a start date.
- When `custom` is included, a user-selected range that does not match another preset automatically switches to the custom preset.

### Livewire and Laravel usage

- Official examples use `wire:model` for single dates and ranges. Single dates can be accessed as a `Carbon` instance or a `Y-m-d` string.
- For `mode="range"`, bind to an array with `start` and `end`, or use `Flux\DateRange` when range helpers/presets are useful.
- Keep date math in services/actions. Blade and Livewire views must not calculate stay nights, payable days, availability, cleaning gaps, cancellation windows, or pricing.
- Validate all selected dates server-side before quoting, holding, requesting, or booking.
- Use `min`, `max`, `min-range`, `max-range`, and `unavailable` only as UI constraints; booking availability must still be checked transactionally server-side.
- Use `locale` when the picker needs to match the active Laravel locale instead of the browser locale.
- Prefer normal date inputs for birthdates or far-past/far-future dates, as recommended by the Flux docs.
- Do not load full sleeping-place calendars into Livewire public properties. Pass compact unavailable date lists only when small enough for the current UI.

### Styling, variants, and states

- Trigger types: `button` (default), `input`.
- Modes: `single` (default), `range`.
- Calendar day sizes: `sm`, `default`, `lg`, `xl`, `2xl`.
- Input/button trigger sizes: `sm`, `xs`.
- Use `fixed-weeks` to keep month height consistent and avoid layout shifts.
- Use `selectable-header` for faster month/year navigation.
- Use `with-today` for a current-date shortcut.
- Use `week-numbers` only when week numbers are useful to the user.
- Use `with-confirmation` when applying the date immediately would be risky or expensive.
- Use `clearable` only when empty date selection is valid.

### Project rules

- Prefer `flux:date-picker` over custom calendar overlays for booking/search date selection.
- Booking date selection must preserve this project's `[check_in_date, check_out_date)` logic. Date Picker collects user input; services derive nights, stay days, calendar days, availability, price, deadlines, and reminders.
- For guest booking ranges, keep unavailable dates compact and derived from indexed sleeping-place availability services.
- For mobile booking flows, prefer clear labels, separate check-in/check-out displays when needed, and `with-confirmation` for changes that trigger expensive recalculation.
- For dashboards or reports, `Flux\DateRange` and presets are appropriate when backed by indexed Eloquent filters.
- Do not use `allTime` on large datasets unless the query deliberately removes or changes date constraints and remains indexed/performance-safe.
- Date picker labels, descriptions, placeholder text, badges, validation messages, and surrounding explanations must use translation keys.

### Mistakes to avoid

- Do not use Date Picker for birthdates or far-past/far-future dates; use date inputs instead.
- Do not treat `unavailable`, `min`, or `max` as security or booking protection.
- Do not calculate booking nights, pricing, or cancellation windows in Blade.
- Do not pass huge unavailable-date lists or full calendars into Livewire public properties.
- Do not use translated date labels as persisted values; persist normalized dates or typed date objects.
- Do not invent undocumented Date Picker props, presets, child components, or `DateRange` methods.

## Component: Dropdown

Source: https://fluxui.dev/components/dropdown
Reviewed: 2026-06-20

### Purpose

Flux Dropdown creates composable dropdown menus for navigation links, action menus, grouped commands, keyboard-navigable menus, checkbox/radio choices, and nested submenus.

### Basic usage

Action menu:

```blade
<flux:dropdown>
    <flux:button icon:trailing="chevron-down">
        {{ __('actions.options') }}
    </flux:button>

    <flux:menu>
        <flux:menu.item icon="eye">
            {{ __('actions.view') }}
        </flux:menu.item>

        <flux:menu.separator />

        <flux:menu.item variant="danger" icon="trash">
            {{ __('actions.delete') }}
        </flux:menu.item>
    </flux:menu>
</flux:dropdown>
```

Navigation menu:

```blade
<flux:dropdown position="bottom" align="end">
    <flux:profile
        :avatar="$userAvatarUrl"
        :name="$userDisplayName"
    />

    <flux:navmenu>
        <flux:navmenu.item :href="route('profile.show')" icon="user">
            {{ __('navigation.profile') }}
        </flux:navmenu.item>

        <flux:navmenu.item :href="route('trips.index')" icon="briefcase">
            {{ __('navigation.trips') }}
        </flux:navmenu.item>
    </flux:navmenu>
</flux:dropdown>
```

Checkbox filter menu:

```blade
<flux:dropdown align="end">
    <flux:button icon:trailing="chevron-down">
        {{ __('filters.status') }}
    </flux:button>

    <flux:menu keep-open>
        <flux:menu.checkbox wire:model.change="filters.available" keep-open>
            {{ __('sleeping_places.status.available') }}
        </flux:menu.checkbox>

        <flux:menu.checkbox wire:model.change="filters.requestOnly" keep-open>
            {{ __('sleeping_places.status.request_only') }}
        </flux:menu.checkbox>
    </flux:menu>
</flux:dropdown>
```

Radio sort menu:

```blade
<flux:dropdown align="end">
    <flux:button icon:trailing="chevron-down">
        {{ __('filters.sort_by') }}
    </flux:button>

    <flux:menu>
        <flux:menu.radio.group wire:model.change="sortBy">
            <flux:menu.radio>
                {{ __('filters.sort.latest') }}
            </flux:menu.radio>

            <flux:menu.radio>
                {{ __('filters.sort.price_low_to_high') }}
            </flux:menu.radio>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
```

Grouped menu with submenu:

```blade
<flux:dropdown align="end">
    <flux:button icon:trailing="chevron-down">
        {{ __('actions.options') }}
    </flux:button>

    <flux:menu>
        <flux:menu.group :heading="__('navigation.account')">
            <flux:menu.item icon="user">
                {{ __('navigation.profile') }}
            </flux:menu.item>
        </flux:menu.group>

        <flux:menu.submenu :heading="__('filters.sort_by')">
            <flux:menu.radio.group wire:model.change="sortBy">
                <flux:menu.radio>{{ __('filters.sort.name') }}</flux:menu.radio>
                <flux:menu.radio>{{ __('filters.sort.date') }}</flux:menu.radio>
            </flux:menu.radio.group>
        </flux:menu.submenu>
    </flux:menu>
</flux:dropdown>
```

### Props and attributes

`flux:dropdown` props:

- `position`: menu position. Options: `top`, `right`, `bottom` (default), `left`.
- `align`: menu alignment. Options: `start` (default), `center`, `end`.
- `offset`: pixel offset from the trigger element. Default: `0`.
- `gap`: pixel gap between trigger and menu. Default: `4`.
- Attribute: `data-flux-dropdown`, applied to the root element.

`flux:menu` props:

- `keep-open`: prevents the menu from closing when any item inside it is clicked.
- Slot: `default`, containing menu items, separators, and submenus.
- Attribute: `data-flux-menu`, applied to the root element.

`flux:menu.item` props:

- `icon`: icon name displayed at the start of the item.
- `icon:trailing`: icon name displayed at the end of the item.
- `icon:variant`: icon style. Options: `outline`, `solid`, `mini`, `micro`.
- `kbd`: keyboard shortcut hint shown at the end of the item.
- `suffix`: text displayed at the end of the item.
- `variant`: visual style. Options: `default`, `danger`.
- `disabled`: prevents interaction with the menu item.
- `keep-open`: prevents the menu from closing when this item is selected.
- Attributes: `data-flux-menu-item`; `data-active` when hovered or active.

`flux:menu.submenu` props:

- `heading`: submenu heading text.
- `icon`: icon name displayed at the start of the submenu.
- `icon:trailing`: icon name displayed at the end of the submenu.
- `icon:variant`: icon style. Options: `outline`, `solid`, `mini`, `micro`.
- `keep-open`: prevents the submenu from closing when an item inside it is selected.
- Slot: `default`, containing submenu items such as checkboxes or radios.

`flux:menu.checkbox.group` props:

- `wire:model`: binds the checkbox group to a Livewire property.
- Slot: `default`, containing checkboxes.

`flux:menu.checkbox` props:

- `wire:model`: binds the checkbox to a Livewire property.
- `checked`: checked by default.
- `disabled`: prevents interaction with the checkbox.
- `keep-open`: prevents the menu from closing when this checkbox is selected.
- Attributes: `data-active` when hovered or active; `data-checked` when checked.

`flux:menu.radio.group` props:

- `wire:model`: binds the radio group to a Livewire property.
- `keep-open`: prevents the menu from closing when any radio button in the group is selected.
- Slot: `default`, containing radio buttons.

`flux:menu.radio` props:

- `checked`: selected by default.
- `disabled`: prevents interaction with the radio button.
- `keep-open`: prevents the menu from closing when this radio is selected.
- Attributes: `data-active` when hovered or active; `data-checked` when selected.

`flux:menu.separator`:

- Renders a separator line between related menu items.

### Slots and child components

- `flux:dropdown` wraps one trigger element followed by menu content.
- Common triggers shown in the docs include `flux:button` and `flux:profile`.
- Use `flux:navmenu` with `flux:navmenu.item` for simple link collections.
- Use `flux:menu` for action menus that need keyboard navigation, submenus, checkboxes, or radios.
- Use `flux:menu.group` with a `heading` to group related items under a visible heading.
- Use `flux:menu.separator` to visually separate item groups.
- Use `flux:menu.submenu` for nested menu choices.
- Use `flux:menu.checkbox.group` and `flux:menu.checkbox` for multi-select menu choices.
- Use `flux:menu.radio.group` and `flux:menu.radio` for single-choice menu choices.

### Livewire and Laravel usage

- Official examples support `wire:model` on `flux:menu.checkbox`, `flux:menu.checkbox.group`, and `flux:menu.radio.group`.
- In this project, prefer `wire:model.change` for checkbox and radio dropdown state so updates happen on selection instead of live typing-style behavior.
- Use menu item actions only for UI commands that already have server-side authorization and validation in Livewire actions, policies, or services.
- For navigation menus, use named routes through `route()` and keep locale-aware routing intact.
- Dropdown labels, headings, menu item text, suffixes, keyboard hint explanations, and surrounding text must use translation keys.
- Do not put booking, availability, pricing, or permission decisions in Blade menu markup. Prepare booleans, disabled states, counts, and route targets in the Livewire class, DTO, or presenter.

### Styling, variants, and states

- Positioning: use `position` with `top`, `right`, `bottom`, or `left`.
- Alignment: use `align` with `start`, `center`, or `end`.
- Spacing: use `offset` and `gap` with pixel values.
- Destructive actions: use `variant="danger"` on `flux:menu.item`.
- Disabled menu entries: use `disabled` on items, checkboxes, or radios.
- Keyboard hints: use `kbd` on `flux:menu.item` for real shortcuts.
- Icons: use `icon`, `icon:trailing`, and `icon:variant` only with documented values.
- Keep-open behavior can be applied to `flux:menu`, `flux:menu.item`, `flux:menu.checkbox`, `flux:menu.radio`, `flux:menu.radio.group`, or `flux:menu.submenu`.

### Project rules

- Prefer `flux:dropdown` over custom Alpine/Tailwind dropdown markup for compact action menus, profile menus, sorting menus, and small filter menus.
- Use `flux:navmenu` inside Dropdown for simple navigation link lists; use `flux:menu` for interactive action menus.
- Keep dropdowns short on mobile. For complex search filters, use the project's bottom-sheet/drawer pattern instead of stacking many dropdown submenus.
- Keep destructive actions visibly separated with `flux:menu.separator` and `variant="danger"`, and route the final action through confirmation when needed.
- For status or sort filters, bind compact scalar/boolean state to Livewire and keep query work in computed properties, actions, or scoped Eloquent queries.
- Do not create admin, staff, support, finance, or moderation workflows just because a dropdown menu can contain those actions.
- All Dropdown trigger labels, item labels, group headings, and submenu headings must use translation keys.

### Mistakes to avoid

- Do not build custom dropdown markup when documented Flux Dropdown, Menu, or Navmenu usage fits.
- Do not use `flux:navmenu` for action menus that need keyboard navigation, submenus, checkboxes, or radios; use `flux:menu`.
- Do not use `flux:menu` for a simple collection of navigation links when `flux:navmenu` is enough.
- Do not put large search/filter interfaces inside nested dropdowns on mobile.
- Do not use `keep-open` by default; use it deliberately for multi-select filters or item-specific interactions.
- Do not show `kbd` hints unless the shortcut actually exists.
- Do not rely on disabled menu items or hidden dropdown items for authorization.
- Do not invent undocumented Dropdown, Menu, Menu Item, Checkbox, Radio, Submenu, or Separator props.

## Component: Editor

Source: https://fluxui.dev/components/editor
Reviewed: 2026-06-20

### Purpose

Flux Pro Editor is a basic rich text editor built with ProseMirror and Tiptap. It supports accessible toolbar controls, common rich text shortcuts, markdown-style input triggers, localization of internal aria-label/tooltip copy, and optional Tiptap extension customization. Because it uses large external dependencies, the editor JavaScript is not included in the core Flux bundle and is loaded on demand when `flux:editor` is used.

### Basic usage

Simple rich text editor:

```blade
<flux:editor
    wire:model.blur="content"
    :label="__('editor.release_notes')"
    :description="__('editor.release_notes_help')"
/>
```

Initial value without Livewire binding:

```blade
<flux:editor
    :value="$initialHtml"
    :label="__('editor.description')"
/>
```

Custom toolbar order:

```blade
<flux:editor
    wire:model.blur="content"
    toolbar="heading | bold italic underline | bullet ordered blockquote | link ~ undo redo"
/>
```

Custom editor layout:

```blade
<flux:editor wire:model.blur="content">
    <flux:editor.toolbar>
        <flux:editor.heading />
        <flux:editor.separator />
        <flux:editor.bold />
        <flux:editor.italic />
        <flux:editor.strike />
        <flux:editor.separator />
        <flux:editor.bullet />
        <flux:editor.ordered />
        <flux:editor.blockquote />
        <flux:editor.separator />
        <flux:editor.link />
        <flux:editor.separator />
        <flux:editor.align />
    </flux:editor.toolbar>

    <flux:editor.content />
</flux:editor>
```

Editor toolbar with an action dropdown:

```blade
<flux:editor wire:model.blur="content">
    <flux:editor.toolbar>
        <flux:editor.heading />
        <flux:editor.separator />
        <flux:editor.bold />
        <flux:editor.italic />
        <flux:editor.strike />
        <flux:editor.separator />
        <flux:editor.bullet />
        <flux:editor.ordered />
        <flux:editor.blockquote />
        <flux:editor.separator />
        <flux:editor.link />
        <flux:editor.spacer />

        <flux:dropdown align="end" offset="-15">
            <flux:editor.button icon="ellipsis-horizontal" :tooltip="__('actions.more')" />

            <flux:menu>
                <flux:menu.item wire:click="preview" icon="arrow-top-right-on-square">
                    {{ __('actions.preview') }}
                </flux:menu.item>

                <flux:menu.item wire:click="export" icon="arrow-down-tray">
                    {{ __('actions.export') }}
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:editor.toolbar>

    <flux:editor.content />
</flux:editor>
```

Height customization:

```blade
<flux:editor class="**:data-[slot=content]:min-h-[100px]!" />
```

Custom toolbar item file:

```text
resources/views/flux/editor/copy.blade.php
```

Then reference it by name in `toolbar`:

```blade
<flux:editor toolbar="heading | bold italic | align ~ copy" />
```

### Props and attributes

`flux:editor` props:

- `wire:model`: binds the editor content to a Livewire property.
- `value`: initial content when not using `wire:model`.
- `label`: label text displayed above the editor; when provided, wraps the editor in `flux:field` with `flux:label`.
- `description`: help text displayed below the editor; when provided with `label`, appears between the label and editor inside the field wrapper.
- `description:trailing`: displays the description below the editor instead of above it.
- `badge`: badge text displayed at the end of `flux:label` when `label` is provided.
- `placeholder`: placeholder text displayed when the editor is empty.
- `toolbar`: space-separated list of toolbar items. Use `|` for a separator and `~` for a spacer. Default toolbar includes `heading`, `bold`, `italic`, `strike`, `bullet`, `ordered`, `blockquote`, `link`, and `align`.
- `disabled`: prevents user interaction with the editor.
- `invalid`: applies error styling to the editor.
- Slot: `default`, containing editor content and toolbar components. If omitted, the standard toolbar and empty content area are used.
- Attribute: `data-flux-editor`, applied to the root element.

`flux:editor.toolbar` props:

- `items`: space-separated toolbar item list. Use `|` for a separator and `~` for a spacer. If not provided, the default toolbar is displayed.
- Slot: `default`, containing toolbar items, separators, and spacers for a fully custom toolbar.

`flux:editor.button` props:

- `icon`: icon name displayed in the button.
- `iconVariant`: icon style. Options: `mini`, `micro`, `outline`. Default is `mini` without slot content and `micro` with slot content.
- `tooltip`: tooltip text displayed on hover.
- `disabled`: prevents interaction with the button.
- Slot: `default`, content shown inside the button. When provided with `icon`, the icon appears before this content.

`flux:editor.content`:

- Container for editable content, typically used when creating a custom editor layout.
- Slot: `default`, initial HTML content for the editor. The editor processes and manages this content.

### Slots and child components

- `flux:editor.toolbar`: container for editor toolbar items.
- `flux:editor.content`: editable content container.
- `flux:editor.button`: custom toolbar button.
- Toolbar separator token: `|` in the `toolbar` or `items` string.
- Toolbar spacer token: `~` in the `toolbar` or `items` string.
- Custom toolbar item files can be placed in `resources/views/flux/editor` and referenced by filename in toolbar configuration.
- Toolbar item components documented:
  - `flux:editor.heading`
  - `flux:editor.bold`
  - `flux:editor.italic`
  - `flux:editor.strike`
  - `flux:editor.underline`
  - `flux:editor.bullet`
  - `flux:editor.ordered`
  - `flux:editor.blockquote`
  - `flux:editor.code`
  - `flux:editor.link`
  - `flux:editor.align`
  - `flux:editor.undo`
  - `flux:editor.redo`
  - `flux:editor.separator`
  - `flux:editor.spacer`

### Toolbar items

Toolbar keys documented for the `toolbar` prop:

- `heading`
- `bold`
- `italic`
- `strike`
- `underline`
- `bullet`
- `ordered`
- `blockquote`
- `subscript`
- `superscript`
- `highlight`
- `link`
- `code`
- `undo`
- `redo`

Toolbar item reference descriptions documented:

- `heading`: heading level selector.
- `bold`: bold formatting.
- `italic`: italic formatting.
- `strike`: strikethrough formatting.
- `underline`: underline formatting.
- `bullet`: bulleted list.
- `ordered`: numbered list.
- `blockquote`: block quote formatting.
- `code`: code block formatting.
- `link`: link insertion.
- `align`: text alignment options.
- `undo`: undo last action.
- `redo`: redo last action.

### Shortcut keys

Flux Editor uses Tiptap's default shortcut keys.

- Apply paragraph style: `Ctrl+Alt+0` / `Cmd+Alt+0`.
- Apply heading levels 1, 2, 3: `Ctrl+Alt+1`, `Ctrl+Alt+2`, `Ctrl+Alt+3` / `Cmd+Alt+1`, `Cmd+Alt+2`, `Cmd+Alt+3`.
- Bold: `Ctrl+B` / `Cmd+B`.
- Italic: `Ctrl+I` / `Cmd+I`.
- Underline: `Ctrl+U` / `Cmd+U`.
- Strikethrough: `Ctrl+Shift+X` / `Cmd+Shift+X`.
- Bullet list: `Ctrl+Shift+8` / `Cmd+Shift+8`.
- Ordered list: `Ctrl+Shift+7` / `Cmd+Shift+7`.
- Blockquote: `Ctrl+Shift+B` / `Cmd+Shift+B`.
- Code: `Ctrl+E` / `Cmd+E`.
- Highlight: `Ctrl+Shift+H` / `Cmd+Shift+H`.
- Align left: `Ctrl+Shift+L` / `Cmd+Shift+L`.
- Align center: `Ctrl+Shift+E` / `Cmd+Shift+E`.
- Align right: `Ctrl+Shift+R` / `Cmd+Shift+R`.
- Paste without formatting: `Ctrl+Shift+V` / `Cmd+Shift+V`.
- Add a line break: `Ctrl+Shift+Enter` / `Cmd+Shift+Enter`.
- Undo: `Ctrl+Z` / `Cmd+Z`.
- Redo: `Ctrl+Shift+Z` / `Cmd+Shift+Z`.

### Markdown syntax

Flux Editor is not a markdown editor, but it supports markdown notation to trigger rich text styling while authoring.

- `#`: heading level 1.
- `##`: heading level 2.
- `###`: heading level 3.
- `**`: bold.
- `*`: italic.
- `~~`: strikethrough.
- `-`: bullet list.
- `1.`: ordered list.
- `>`: blockquote.
- `` ` ``: inline code.
- Three backticks: code block.
- Three backticks followed by a language token: code block with `class="language-?"`.
- `---`: horizontal rule.

### Localization

To localize the editor's aria-label and tooltip copy, register these keys in the app's language files:

- `Rich text editor`
- `Formatting`
- `Text`
- `Heading 1`
- `Heading 2`
- `Heading 3`
- `Styles`
- `Bold`
- `Italic`
- `Underline`
- `Strikethrough`
- `Subscript`
- `Superscript`
- `Highlight`
- `Code`
- `Bullet list`
- `Ordered list`
- `Blockquote`
- `Insert link`
- `Unlink`
- `Align`
- `Left`
- `Center`
- `Right`
- `Undo`
- `Redo`

### Extensions

- Built-in Tiptap extensions documented:
  - `Highlight`
  - `Link`
  - `Placeholder`
  - `StarterKit`
  - `Superscript`
  - `Subscript`
  - `TextAlign`
  - `Underline`
- Customization starts by listening for the `flux:editor` event in the layout `<head>` or `app.js`.
- `e.detail.registerExtensions([...])` registers extensions; if an extension already exists, it is replaced.
- `e.detail.disableExtension('underline')` disables a built-in extension by name.
- `e.detail.init(({ editor }) => { ... })` gives access to the Tiptap instance during Tiptap's `beforeCreate` event.
- Documented Tiptap instance events shown in examples: `create`, `update`, `selectionUpdate`, `transaction`, `focus`, `blur`, `destroy`, `drop`, `paste`, and `contentError`.

### Livewire and Laravel usage

- Use `wire:model.blur` for normal editor fields in this project so large HTML content does not sync on every keystroke.
- Use `wire:model` only when immediate syncing is genuinely needed and the content is small enough for Livewire performance.
- Validate rich text server-side. Treat Editor output as user-generated HTML unless the content is trusted.
- Sanitize or otherwise constrain stored/rendered HTML before displaying it back to other users.
- Use normal `flux:textarea` or `flux:composer` for plain booking messages, host responses, support-adjacent comments, and short structured text where rich formatting is unnecessary.
- Do not put booking calculations, availability checks, price logic, or authorization decisions in editor toolbar Blade.
- Keep editor Livewire public properties small enough for the current form; do not load large documents into public properties without a deliberate workflow.
- Use translation keys for labels, descriptions, placeholders, badge text, custom toolbar tooltips, and custom toolbar menu items.

### Styling, variants, and states

- Default editor content area has minimum height `200px` and maximum height `500px`.
- Use Tailwind utilities targeting `data-slot="content"` for custom content height and overflow behavior.
- Use `disabled` for read-only disabled editor state.
- Use `invalid` for validation/error styling.
- Use toolbar separators and spacers to keep toolbar groups readable.
- On mobile, keep toolbar items minimal and use a dropdown for secondary actions only when those actions are important.

### Project rules

- Prefer `flux:editor` only when rich text is required, such as host listing descriptions, house rules, formatted help content, or release/admin-authored style content if that surface is later approved.
- Do not use Editor for simple messages, booking comments, search filters, payment notes, date/time comments, or short plain-text fields.
- Rich text content must have a storage/rendering policy before it is exposed to guests or hosts.
- Keep the default toolbar reduced for mobile forms; avoid overwhelming 320px screens with dense formatting controls.
- Register Flux Editor localization keys for both `en` and `ru` before shipping Editor UI.
- Because Editor loads additional JavaScript on demand, avoid putting it on first-load search screens or high-frequency mobile workflows unless rich text is truly required.
- Custom toolbar item Blade files belong in `resources/views/flux/editor` and must follow this project's localization and no-`@php` Blade rules.

### Mistakes to avoid

- Do not use Editor when `flux:textarea` or `flux:composer` is the simpler documented component.
- Do not sync rich text with live keystroke updates by default.
- Do not store or render untrusted rich HTML without a sanitization or allowlist strategy.
- Do not assume Editor is a markdown editor; markdown notation only triggers rich text styling.
- Do not add Tiptap extensions casually. Document the reason, asset impact, and sanitization/rendering implications.
- Do not forget Flux Editor's own localization keys for aria labels and tooltips.
- Do not invent undocumented Editor props, toolbar keys, toolbar child components, extension APIs, or shortcut behavior.

## Component: Field

Source: https://fluxui.dev/components/field
Reviewed: 2026-06-20

### Purpose

Flux Field encapsulates form controls with labels, descriptions, and validation errors. It provides explicit long-form composition for custom layouts and shorthand `label` / `description` props on Flux form controls for common cases.

### Basic usage

Long-form field:

```blade
<flux:field>
    <flux:label>{{ __('auth.email') }}</flux:label>
    <flux:input wire:model.blur="email" type="email" />
    <flux:error name="email" />
</flux:field>
```

Shorthand form control props:

```blade
<flux:input
    wire:model.blur="email"
    type="email"
    :label="__('auth.email')"
/>
```

Trailing description:

```blade
<flux:field>
    <flux:label>{{ __('auth.password') }}</flux:label>
    <flux:input wire:model.blur="password" type="password" />
    <flux:error name="password" />
    <flux:description>{{ __('auth.password_requirements') }}</flux:description>
</flux:field>
```

Shorthand trailing description:

```blade
<flux:input
    wire:model.blur="password"
    type="password"
    :label="__('auth.password')"
    description:trailing="{{ __('auth.password_requirements') }}"
/>
```

Label badge:

```blade
<flux:field>
    <flux:label :badge="__('forms.required')">
        {{ __('auth.email') }}
    </flux:label>

    <flux:input wire:model.blur="email" type="email" required />
    <flux:error name="email" />
</flux:field>
```

Split mobile-aware layout:

```blade
<div class="grid gap-4 sm:grid-cols-2">
    <flux:input
        wire:model.blur="firstName"
        :label="__('profile.first_name')"
    />

    <flux:input
        wire:model.blur="lastName"
        :label="__('profile.last_name')"
    />
</div>
```

Fieldset:

```blade
<flux:fieldset>
    <flux:legend>{{ __('profile.address') }}</flux:legend>

    <div class="space-y-6">
        <flux:input
            wire:model.blur="street"
            :label="__('profile.street_address')"
        />

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:input
                wire:model.blur="city"
                :label="__('profile.city')"
            />

            <flux:input
                wire:model.blur="postalCode"
                :label="__('profile.postal_code')"
            />
        </div>
    </div>
</flux:fieldset>
```

### Props and attributes

`flux:field` props:

- `variant`: visual style variant. Options: `block`, `inline`. Default: `block`.
- Slot: `default`, containing form controls and their associated labels, descriptions, and error messages.
- Attribute: `data-flux-field`, applied to the root element.

`flux:label` props and slots:

- `badge`: optional text displayed as a badge, such as required or optional status.
- Slot: `default`, label text content.
- Slot: `trailing`, optional text displayed at the end of the label.

`flux:description` slots:

- Slot: `default`, descriptive text content.

`flux:error` props and slots:

- `name`: field name to display validation errors for.
- `message`: optional custom error message content.
- `bag`: error bag to read from. Default: `default`.
- `deep`: whether to look for validation errors in nested array fields such as `fields.*`. Default: `true`. If `false`, only exact field-name errors are shown.
- `icon`: icon displayed inline with the error message. Default: `exclamation-triangle`. Set to `false` to hide the icon.
- Slot: `default`, optional custom error message content.

`flux:fieldset` props and slots:

- `legend`: fieldset heading text.
- `description`: optional fieldset description text.
- Slot: `default`, grouped form fields and their associated legend.

`flux:legend` slots:

- Slot: `default`, fieldset heading text.

### Slots and child components

- Use `flux:field` to wrap a single form control with `flux:label`, the control, optional `flux:error`, and optional `flux:description`.
- Use `flux:label` for visible labels, badges, and optional trailing text.
- Use `flux:description` for help text. In long-form trailing layouts, place it after `flux:error`.
- Use `flux:error` for validation feedback linked to the relevant field name.
- Use `flux:fieldset` and `flux:legend` to group related fields.
- Flux form controls can accept shorthand `label` and `description` props. Under the hood, Flux wraps them in a field with an error component automatically.
- Use `description:trailing` shorthand when the description should appear directly below the input.

### Livewire and Laravel usage

- Use Field around Flux inputs, selects, textareas, checkboxes, radios, switches, date pickers, color pickers, and other form controls when the shorthand props are not enough.
- Prefer shorthand `:label` and `:description` props for simple single-control fields.
- Use the long-form `flux:field` composition when custom label badges, custom error bags, trailing descriptions, inline variants, or unusual child ordering is needed.
- Bind text inputs with `wire:model.blur`, selects/checkboxes/radios with `wire:model.change`, and search/autocomplete inputs with documented debounce patterns from this project.
- Validation must stay server-side in Livewire actions, form objects, form requests, or services. Field only renders labels, descriptions, and errors.
- Use `flux:error name="..."` with the exact validation key, including nested keys when needed.
- For form objects or nested arrays, keep the error `name` aligned with the actual validation path and use `deep="false"` only when exact-match error display is required.
- All label, description, badge, legend, placeholder, and custom error copy must use translation keys.

### Styling, variants, and states

- `flux:field` supports `variant="block"` and `variant="inline"`.
- Use badges for context like required or optional only when that status is not otherwise obvious.
- Use split layouts by wrapping multiple shorthand Flux form controls in responsive grid markup.
- Use `flux:fieldset` for grouped fields and `flux:legend` for the group heading.
- Keep mobile form fields stacked by default; only split fields at larger breakpoints such as `sm:grid-cols-2`.
- Use `flux:error :icon="false"` only when removing the default error icon improves a specific compact layout.

### Project rules

- Prefer Flux Field patterns over custom label/error/description markup for all new forms.
- Use shorthand field props for normal fields to keep Blade concise.
- Use explicit `flux:field` when a field needs a badge, custom trailing description, custom error bag, nested error behavior, or custom layout.
- For booking, listing, profile, search, and host setup forms, keep labels short, descriptions useful, and errors friendly.
- Every visible field label, fieldset legend, description, badge, and error message must be translated for `en` and `ru`.
- Do not perform business logic, pricing, availability checks, or query work inside Field markup.
- Use `flux:fieldset` to group related sections such as address, rules, amenities, verification, guest details, and host preferences.

### Mistakes to avoid

- Do not hand-roll repeated label/input/error markup when `flux:field` or shorthand field props fit.
- Do not omit labels for visible form controls. If a future component supports screen-reader-only labels, use its documented API rather than hiding text manually.
- Do not put long help text above compact mobile controls when `description:trailing` is clearer.
- Do not use badges as the only validation or required-field signal.
- Do not mismatch `flux:error name` with the Livewire validation key.
- Do not rely on Field for validation, authorization, or sanitization. It is presentation structure.
- Do not invent undocumented Field, Label, Description, Error, Fieldset, or Legend props.

## Component: File Upload

Source: https://fluxui.dev/components/file-upload
Reviewed: 2026-06-20

### Purpose

Flux Pro File Upload provides drag-and-drop file selection, file previews, upload progress, disabled states, custom upload surfaces, and Livewire file upload integration.

### Basic usage

Multiple image upload with dropzone:

```blade
<flux:file-upload
    wire:model="photos"
    multiple
    :label="__('media.upload_files')"
>
    <flux:file-upload.dropzone
        :heading="__('media.drop_files_or_browse')"
        :text="__('media.image_upload_limits')"
    />
</flux:file-upload>
```

Inline dropzone:

```blade
<flux:file-upload
    wire:model="photos"
    multiple
    :label="__('media.upload_files')"
>
    <flux:file-upload.dropzone
        :heading="__('media.drop_files_or_browse_short')"
        :text="__('media.image_upload_limits')"
        inline
    />
</flux:file-upload>
```

Dropzone with progress indicator:

```blade
<flux:file-upload
    wire:model="photos"
    multiple
    :label="__('media.upload_files')"
>
    <flux:file-upload.dropzone
        :heading="__('media.drop_files_or_browse_short')"
        :text="__('media.image_upload_limits')"
        with-progress
        inline
    />
</flux:file-upload>
```

Disabled upload:

```blade
<flux:file-upload
    wire:model="photos"
    multiple
    :label="__('media.upload_files')"
    disabled
>
    <flux:file-upload.dropzone
        :heading="__('media.drop_files_or_browse_short')"
        :text="__('media.image_upload_limits')"
        inline
    />
</flux:file-upload>
```

Uploaded file item:

```blade
<flux:file-item
    :heading="$photo->getClientOriginalName()"
    :image="$photo->temporaryUrl()"
    :size="$photo->getSize()"
>
    <x-slot name="actions">
        <flux:file-item.remove
            wire:click="removePhoto"
            :aria-label="__('media.remove_file', ['name' => $photo->getClientOriginalName()])"
        />
    </x-slot>
</flux:file-item>
```

Single-file Livewire component pattern:

```php
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadPhoto extends Component
{
    use WithFileUploads;

    #[Validate('image|max:10240')]
    public $photo = null;

    public function removePhoto(): void
    {
        $this->photo?->delete();
        $this->photo = null;
    }

    public function save(): void
    {
        $this->photo->store(path: 'photos');
    }
}
```

Multiple-file Livewire component pattern:

```php
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadPhotos extends Component
{
    use WithFileUploads;

    #[Validate(['photos.*' => 'image|max:1024'])]
    public array $photos = [];

    public function removePhoto(int $index): void
    {
        $photo = $this->photos[$index] ?? null;
        $photo?->delete();

        unset($this->photos[$index]);
        $this->photos = array_values($this->photos);
    }

    public function save(): void
    {
        foreach ($this->photos as $photo) {
            $photo->store(path: 'photos');
        }
    }
}
```

### Props and attributes

`flux:file-upload` props:

- `name`: input name attribute for form submissions.
- `multiple`: allows selecting and uploading multiple files. Default: `false`.
- `label`: field label displayed above the upload area.
- `description`: helper text displayed below the field.
- `error`: error message displayed for validation failures.
- `disabled`: prevents user interaction with the upload. Default: `false`.
- Livewire directive: `wire:model`, binds uploads to a Livewire property.
- Data attribute: `data-dragging`, added when files are dragged over the component.
- Data attribute: `data-loading`, added while files are uploading.

`flux:file-upload.dropzone` props:

- `heading`: main text displayed in the dropzone.
- `text`: supporting text displayed below the heading, such as file type and size restrictions.
- `icon`: icon name displayed in the dropzone. Default: `cloud-arrow-up`.
- `inline`: displays a compact horizontal layout. Default: `false`.
- `with-progress`: shows a progress bar during uploads instead of only a loading spinner. Requires `text`. Default: `false`.
- CSS variable: `--flux-file-upload-progress`, upload progress percentage available when using `with-progress`.
- CSS variable: `--flux-file-upload-progress-as-string`, quoted upload progress string available when using `with-progress`.
- Data attribute: `data-dragging`, present when files are dragged over the dropzone.
- Data attribute: `data-loading`, present while files are uploading.

`flux:file-item` props:

- `heading`: file name or title.
- `text`: additional text. If not provided, it is automatically calculated from `size`.
- `image`: URL or path to a preview image.
- `size`: file size in bytes; automatically formatted to `B`, `KB`, `MB`, or `GB`.
- `icon`: icon displayed when no image is provided. Default: `document`.
- `invalid`: displays the file item in an error state. Default: `false`.
- Slot: `actions`, for action buttons such as remove, download, or preview.

`flux:file-item.remove` directives and attributes:

- `wire:click`: triggers a Livewire method to remove the file.
- `aria-label`: accessible label for the remove button.

### Slots and child components

- `flux:file-upload` wraps upload behavior and can contain `flux:file-upload.dropzone` or custom uploader markup.
- `flux:file-upload.dropzone` provides the documented visual drag/drop and browse surface.
- Custom uploader content can be placed inside `flux:file-upload`; the wrapper still handles drag/drop, file selection, and upload progress.
- `flux:file-item` displays uploaded file details and optional preview image.
- `flux:file-item` has an `actions` slot for buttons such as `flux:file-item.remove`.
- `flux:file-item.remove` is the documented pre-styled remove button for file items.

### Livewire and Laravel usage

- Use `wire:model` to bind uploads to Livewire properties.
- Use Livewire's `WithFileUploads` trait in upload components.
- For single uploads, bind to one property and show one `flux:file-item` preview when a temporary file exists.
- For multiple uploads, bind to an array property and add the `multiple` attribute.
- Validate uploaded files with Livewire/Laravel validation rules before storing them.
- Use `temporaryUrl()` for temporary image previews when appropriate.
- Use `getClientOriginalName()` for display headings and accessible remove labels.
- Use `getSize()` for `flux:file-item :size`, letting Flux format bytes.
- Remove temporary uploads by calling the Livewire removal method through `flux:file-item.remove wire:click`.
- Store files through Laravel's uploaded-file storage APIs such as `store()`, `storeAs()`, `storePublicly()`, or `storePubliclyAs()` according to the application's storage policy.
- Use Laravel file upload tests with fake storage and fake uploaded files for upload workflows.

### Styling, variants, and states

- Use `inline` on `flux:file-upload.dropzone` for compact horizontal upload UI.
- Use `with-progress` when users need upload progress feedback; the `text` prop is required.
- Use `disabled` on `flux:file-upload` when uploading is temporarily unavailable.
- Use `data-dragging` and `data-loading` through Tailwind's `in-data-dragging:` and `in-data-loading:` prefixes for custom uploader feedback.
- Use `invalid` on `flux:file-item` to show a file item in an error state.
- Use `image` for image previews and `icon` for non-image files.
- Use the documented CSS progress variables only when customizing progress UI.

### Project rules

- Prefer `flux:file-upload` over custom upload controls for listing photos, room photos, sleeping-place photos, verification documents, complaints, and profile/avatar uploads when a rich upload surface is needed.
- Use `flux:input type="file"` only for very simple native file inputs where drag/drop, previews, file items, and progress are unnecessary.
- Upload components must use `WithFileUploads` and server-side validation.
- Keep upload UI mobile-first: compact text, clear size/type limits, inline dropzones where vertical space matters, and progress feedback for slow 3G.
- Do not keep large permanent media collections in Livewire public properties. Keep temporary uploads and compact DTO/file metadata only.
- Use translated labels, dropzone headings, helper text, remove labels, validation errors, and action text.
- Validate file type, size, count, image dimensions when relevant, ownership, and authorization before storing or attaching uploaded files.
- Store files through services/actions when upload handling affects listing media, booking evidence, verification documents, or complaints.
- Do not expose admin, staff, support, finance, or moderation workflows just because upload UI can handle documents.

### Mistakes to avoid

- Do not build custom drag/drop upload UI when `flux:file-upload` and `flux:file-upload.dropzone` fit.
- Do not use File Upload without Livewire `WithFileUploads` in Livewire upload components.
- Do not rely on accept text or helper copy as validation; validate uploads server-side.
- Do not omit `aria-label` on `flux:file-item.remove`.
- Do not use `with-progress` without the required `text` prop.
- Do not store files before authorization and validation pass.
- Do not load large stored galleries into Livewire public properties just to render file items.
- Do not leave temporary upload arrays sparse after removing an item; reindex multiple-upload arrays.
- Do not invent undocumented File Upload, Dropzone, File Item, or Remove props.

## Component: Heading

Source: https://fluxui.dev/components/heading
Reviewed: 2026-06-20

### Purpose

Flux Heading provides consistent heading typography for pages, sections, cards, panels, forms, and data summaries. It separates visual size from semantic HTML heading level.

### Basic usage

Basic heading with supporting text:

```blade
<flux:heading>{{ __('profile.user_profile') }}</flux:heading>
<flux:text class="mt-2">{{ __('profile.public_information_hint') }}</flux:text>
```

Heading sizes:

```blade
<flux:heading>{{ __('headings.default') }}</flux:heading>
<flux:heading size="lg">{{ __('headings.large') }}</flux:heading>
<flux:heading size="xl">{{ __('headings.extra_large') }}</flux:heading>
```

Semantic heading level:

```blade
<flux:heading level="1" size="xl">
    {{ __('booking.create_listing') }}
</flux:heading>

<flux:heading level="2" size="lg">
    {{ __('booking.sleeping_place_details') }}
</flux:heading>

<flux:text class="mt-2">
    {{ __('booking.sleeping_place_details_help') }}
</flux:text>
```

Leading subheading pattern:

```blade
<div>
    <flux:text>{{ __('stats.year_to_date') }}</flux:text>
    <flux:heading size="xl" class="mb-1">
        {{ $formattedRevenue }}
    </flux:heading>

    <div class="flex items-center gap-2">
        <flux:icon.arrow-trending-up variant="micro" class="text-green-600 dark:text-green-500" />
        <span class="text-sm text-green-600 dark:text-green-500">{{ $formattedGrowth }}</span>
    </div>
</div>
```

### Props and attributes

`flux:heading` props:

- `size`: visual heading size. Options: `base`, `lg`, `xl`. Default: `base`.
- `level`: HTML heading level. Options: `1`, `2`, `3`, `4`. If omitted, the component renders as a `div`.
- `accent`: when `true`, applies accent color styling to the heading.

Related `flux:text` prop documented on the Heading page:

- `size`: text size. Options: `sm`, `base`, `lg`, `xl`. Default: `base`.

### Slots and child components

- `flux:heading` uses its default slot for heading text or inline content.
- `flux:text` is commonly paired below or above a heading for supporting copy or leading subheading text.
- Icons and small inline values can be composed around headings, as shown in the leading subheading example.

### Livewire and Laravel usage

- Heading content should normally be static translated text or precomputed display values from a Livewire class, DTO, or presenter.
- Use translation keys for visible heading and supporting text.
- Use `level` when the heading should participate in the page's semantic document outline.
- Do not use Heading to compute or format values. Prepare formatted values before rendering.

### Styling, variants, and states

- Use `size="base"` for compact panel/card headings and small sections.
- Use `size="lg"` for important section headings or modal-like surfaces.
- Use `size="xl"` for page-level headings, dashboard/stat emphasis, or sparse top-level content.
- Use `accent` sparingly when official accent styling helps establish hierarchy.
- Use Tailwind utility classes for spacing around Heading, such as `mt-*`, `mb-*`, or layout classes.

### Project rules

- Prefer `flux:heading` over raw `h1`-`h4` markup when building Flux UI views.
- Always set `level` for page and section headings that should be semantic HTML headings.
- Keep mobile headings restrained. Avoid oversized heading-heavy layouts on 320px screens unless the screen is truly a first-viewport hero or primary dashboard summary.
- Pair headings with `flux:text` for short supporting copy, not long explanatory blocks.
- Keep heading copy short, calm, and translated in `en` and `ru`.
- Do not use Heading as a substitute for buttons, labels, badges, or form legends.

### Mistakes to avoid

- Do not rely on `size` to define document structure; use `level` for semantic heading level.
- Do not omit `level` on meaningful page or section headings just because the visual style is correct.
- Do not skip heading levels in a way that harms accessibility.
- Do not use raw heading markup when `flux:heading` fits the UI.
- Do not put business logic, queries, or formatting calculations in heading Blade markup.
- Do not invent undocumented Heading props, sizes, levels, or accent variants.

## Component: Icon

Source: https://fluxui.dev/components/icon
Reviewed: 2026-06-20

### Purpose

Flux Icon provides consistent icon rendering through Heroicons, plus a Flux loading spinner, dynamic icon names, Lucide import support, and custom icon file support.

### Basic usage

Heroicon by component name:

```blade
<flux:icon.bolt />
```

Icon variants:

```blade
<flux:icon.bolt />
<flux:icon.bolt variant="solid" />
<flux:icon.bolt variant="mini" />
<flux:icon.bolt variant="micro" />
```

Size and color with Tailwind utilities:

```blade
<flux:icon.bolt class="size-8" />
<flux:icon.bolt variant="solid" class="text-amber-500 dark:text-amber-300" />
```

Loading spinner:

```blade
<flux:icon.loading />
```

Dynamic icon:

```blade
<flux:icon name="bolt" />
```

Lucide icon import:

```bash
php artisan flux:icon crown grip-vertical github
```

Imported Lucide usage:

```blade
<flux:icon.crown />
<flux:icon.grip-vertical />
<flux:icon.github />
```

Icon prop on supported components:

```blade
<flux:button icon="magnifying-glass">
    {{ __('actions.search') }}
</flux:button>

<flux:input
    wire:model.live.debounce.500ms="search"
    icon="magnifying-glass"
    :placeholder="__('search.placeholder')"
/>

<flux:button icon:trailing="chevron-down">
    {{ __('actions.more') }}
</flux:button>
```

### Props and attributes

`flux:icon.*` props and behavior:

- `variant`: visual style. Options: `outline` (default), `solid`, `mini`, `micro`.
- Class: `size-*`, controls icon size using Tailwind size utilities, such as `size-8` or `size-12`.
- Class: `text-*`, controls icon color using Tailwind text color utilities, such as `text-blue-500`.
- Attribute: `data-flux-icon`, applied to the root SVG element.

`flux:icon` dynamic component:

- `name`: icon name to render dynamically.

Icon sizes:

- `outline`: `24x24` pixels, default.
- `solid`: `24x24` pixels.
- `mini`: `20x20` pixels.
- `micro`: `16x16` pixels.

### Slots and child components

- Icons are rendered as component names such as `flux:icon.bolt`.
- `flux:icon.loading` is a special Flux loading spinner icon and is not part of Heroicons.
- `flux:icon` with `name` supports dynamic icon names.
- Custom icons can be created as Blade files in `resources/views/flux/icon`, for example `resources/views/flux/icon/wink.blade.php`, then used as `flux:icon.wink`.
- Flux components that document icon props can accept `icon` and sometimes `icon:trailing`; examples from Flux patterns include buttons, inputs, tabs, badges, breadcrumbs items, navlist items, navbar items, and menu items.

### Livewire and Laravel usage

- Prefer documented icon props on Flux components over manually placing icon components inside slots when the target component supports icon props.
- Use `flux:icon.loading` for loading indicators when an icon-level spinner is needed.
- Use dynamic `flux:icon name="..."` only when icon names come from trusted, whitelisted application state.
- Use `php artisan flux:icon` to import Lucide icons when Heroicons does not contain the needed icon.
- Do not guess icon names. Check Heroicons first, then Lucide when needed.
- Custom icon Blade files belong in `resources/views/flux/icon`, but this project's no-`@php` Blade rule means custom icon files need explicit review before implementation because the official Flux template uses Blade PHP helpers.

### Styling, variants, and states

- Prefer the default sizes for each variant. The Flux docs warn against tweaking icon sizes because each variant is designed for its default size.
- Use `outline` for standard 24px outline icons.
- Use `solid` for filled 24px icons.
- Use `mini` for compact 20px filled icons.
- Use `micro` for dense 16px filled icons.
- Use Tailwind `text-*` utilities for icon color.
- Use Tailwind `size-*` utilities only when a deliberate design need outweighs the default size guidance.

### Project rules

- Prefer Heroicons through Flux icon components before importing Lucide or creating custom icons.
- Prefer component-level `icon`, `icon:trailing`, and `icon:variant` props when documented on the target Flux component.
- Icon-only interactive controls must have accessible labels through the documented component API or an explicit `aria-label`.
- Keep icons supportive, not decorative clutter, especially on 320px mobile screens.
- Do not use icons as the only way to communicate booking, payment, verification, safety, or availability state; pair them with translated text or accessible labels.
- Import Lucide icons intentionally with `php artisan flux:icon <names>` and keep imported icon names exact.
- Avoid custom icons unless Heroicons and Lucide cannot cover the need and the custom icon is worth maintaining.

### Mistakes to avoid

- Do not manually paste SVGs into Blade when `flux:icon.*`, imported Lucide icons, or documented icon props fit.
- Do not invent Heroicons or Lucide icon names.
- Do not use dynamic icon names from user input.
- Do not resize every icon with `size-*`; keep documented variant defaults unless there is a clear design reason.
- Do not use icons alone for critical meaning or interactive controls without accessible labels.
- Do not create custom icon Blade files with `@php` in this project without an explicit override.
- Do not invent undocumented Icon props, variants, sizes, or import behavior.

## Component: Input

Source: https://fluxui.dev/components/input
Reviewed: 2026-06-20

### Purpose

Flux Input captures user data through text-like form controls. It supports Flux Field shorthand, browser input types, file inputs, compact sizes, readonly/disabled/invalid states, masks, icons, built-in clear/copy/password-view buttons, keyboard hints, button-style inputs, and grouped prefixes/suffixes.

### Basic usage

Long-form field:

```blade
<flux:field>
    <flux:label>{{ __('profile.username') }}</flux:label>
    <flux:description>{{ __('profile.username_help') }}</flux:description>
    <flux:input wire:model.blur="username" />
    <flux:error name="username" />
</flux:field>
```

Shorthand field:

```blade
<flux:input
    wire:model.blur="username"
    :label="__('profile.username')"
    :description="__('profile.username_help')"
/>
```

Input types:

```blade
<flux:input wire:model.blur="email" type="email" :label="__('auth.email')" />
<flux:input wire:model.blur="password" type="password" :label="__('auth.password')" />
<flux:input wire:model.blur="date" type="date" max="2999-12-31" :label="__('forms.date')" />
```

Simple native file input:

```blade
<flux:input type="file" wire:model="logo" :label="__('media.logo')" />
<flux:input type="file" wire:model="attachments" :label="__('media.attachments')" multiple />
```

Compact input:

```blade
<flux:input
    wire:model.live.debounce.500ms="filter"
    size="sm"
    :placeholder="__('filters.filter_by')"
/>
```

Readonly filled input:

```blade
<flux:input
    readonly
    variant="filled"
    :value="$publicApiKey"
/>
```

Input mask:

```blade
<flux:input
    wire:model.blur="phone"
    mask="(999) 999-9999"
    :label="__('profile.phone')"
/>
```

Icons and loading icon:

```blade
<flux:input
    wire:model.live.debounce.500ms="search"
    icon="magnifying-glass"
    :placeholder="__('search.placeholder')"
/>

<flux:input
    wire:model.blur="cardNumber"
    icon:trailing="credit-card"
    :placeholder="__('payments.card_number_placeholder')"
/>

<flux:input
    icon:trailing="loading"
    :placeholder="__('search.transactions')"
/>
```

Icon button slot:

```blade
<flux:input
    wire:model.live.debounce.500ms="search"
    :placeholder="__('search.orders')"
>
    <x-slot name="iconTrailing">
        <flux:button
            type="button"
            size="sm"
            variant="subtle"
            icon="x-mark"
            class="-mr-1"
            :aria-label="__('actions.clear')"
            wire:click="clearSearch"
        />
    </x-slot>
</flux:input>
```

Clearable, copyable, and viewable:

```blade
<flux:input wire:model.live.debounce.500ms="search" :placeholder="__('search.orders')" clearable />
<flux:input wire:model.blur="password" type="password" :label="__('auth.password')" viewable />
<flux:input icon="key" :value="$publicApiKey" readonly copyable />
```

Keyboard hint:

```blade
<flux:input
    as="button"
    icon="magnifying-glass"
    :placeholder="__('search.placeholder')"
    kbd="⌘K"
/>
```

Input group with prefix:

```blade
<flux:field>
    <flux:label>{{ __('profile.website') }}</flux:label>

    <flux:input.group>
        <flux:input.group.prefix>https://</flux:input.group.prefix>
        <flux:input wire:model.blur="website" :placeholder="__('profile.website_placeholder')" />
    </flux:input.group>

    <flux:error name="website" />
</flux:field>
```

Input group with button:

```blade
<flux:input.group>
    <flux:input wire:model.blur="postTitle" :placeholder="__('posts.title')" />
    <flux:button icon="plus">{{ __('posts.new') }}</flux:button>
</flux:input.group>
```

Class targeting:

```blade
<flux:input class="max-w-xs" input:class="font-mono" />
```

### Props and attributes

`flux:input` props and attributes:

- `wire:model`: binds the input to a Livewire property.
- `label`: label text displayed above the input; wraps the input in `flux:field` with `flux:label`.
- `description`: help text displayed above the input when used with `label`.
- `description:trailing`: help text displayed below the input when used with `label`.
- `placeholder`: placeholder text shown when the input is empty.
- `size`: input size. Options: `sm`, `xs`.
- `variant`: visual style. Options: `filled`. Default: `outline`.
- `disabled`: prevents user interaction.
- `readonly`: makes the input read-only.
- `invalid`: applies error styling.
- `multiple`: for file inputs, allows selecting multiple files.
- `mask`: Alpine mask plugin pattern, such as `99/99/9999`.
- `mask:dynamic`: Alpine mask plugin dynamic pattern, such as `$money($input)`.
- `icon`: icon name displayed at the start of the input.
- `icon:trailing`: icon name displayed at the end of the input.
- `kbd`: keyboard shortcut hint displayed at the end of the input.
- `clearable`: displays a clear button when the input has content.
- `copyable`: displays a copy button to copy the input's content; docs note this is HTTPS only.
- `viewable`: for password inputs, displays a show/hide password toggle.
- `as`: render the input as a different element. Options: `button`. Default: `input`.
- `input:class`: CSS classes applied directly to the input element instead of the wrapper.
- Attribute: `data-flux-input`, applied to the root element.
- Standard browser input attributes are supported in examples, including `type`, `max`, and `multiple`.

`flux:input.group`:

- Slot: `default`, containing input group content, typically an input and prefix/suffix elements.

`flux:input.group.prefix`:

- Slot: `default`, content displayed before the input, such as icons, text, or buttons.

`flux:input.group.suffix`:

- Slot: `default`, content displayed after the input, such as icons, text, or buttons.

### Slots and child components

- `icon`: custom content displayed at the start of the input.
- `icon:leading`: custom content displayed at the start of the input.
- `icon:trailing`: custom content displayed at the end of the input, such as buttons.
- Official examples use `<x-slot name="iconTrailing">` for trailing button content.
- Use `flux:input.group` with `flux:input.group.prefix` and `flux:input.group.suffix` for text prefixes, suffixes, select prefixes, and attached buttons.
- When an input group needs a label and validation error, wrap the group in `flux:field`.

### Livewire and Laravel usage

- Use `wire:model.blur` for normal text, email, password, phone, URL, and numeric-like input fields.
- Use `wire:model.live.debounce.500ms` or `wire:model.live.debounce.750ms` only for search/autocomplete inputs.
- Use `wire:model` for file inputs and pair upload handling with Livewire's `WithFileUploads`.
- Keep validation server-side in Livewire actions, form objects, form requests, or services.
- Use `label`, `description`, `description:trailing`, and `placeholder` with translation keys.
- Use `input:class` when the class needs to apply to the underlying input element; plain `class` targets the wrapper.
- Use `readonly variant="filled"` for locked values such as generated keys or immutable fields during submission.
- Use `copyable` only for values safe to expose and copy in the current context.
- Use masks only as formatting aids; they do not replace server-side validation or normalization.
- For grouped inputs, keep the `flux:error name` aligned with the Livewire validation key.

### Styling, variants, and states

- Size options: `sm`, `xs`.
- Variant option: `filled`; default visual style is `outline`.
- State props: `disabled`, `readonly`, `invalid`.
- Utility targeting: `class` applies to the wrapper; `input:class` applies directly to the input.
- Use `icon` and `icon:trailing` for simple leading/trailing icons.
- Use `clearable`, `copyable`, and `viewable` for the documented common input button behaviors.
- Use `as="button"` for input-looking triggers, such as command/search launchers.
- Use input groups for attached buttons, selects, prefixes, or suffixes.

### Project rules

- Prefer `flux:input` over raw `<input>` markup for text-like Flux forms.
- Prefer Flux shorthand field props for simple labeled fields; use `flux:field` around input groups.
- Use `flux:file-upload` for rich upload UX; use `flux:input type="file"` only for simple native file uploads.
- Keep mobile inputs large enough to tap and avoid crowded icon/action combinations on 320px screens.
- Do not use live typing updates except for search/autocomplete.
- Do not calculate booking dates, pricing, availability, fees, or business rules in input Blade markup.
- For phone, money, date, or URL fields, normalize and validate server-side even when an input mask or type is used.
- All labels, descriptions, placeholders, keyboard hint context, and button labels must use translations.

### Mistakes to avoid

- Do not hand-roll input wrappers, icons, clear buttons, copy buttons, or password toggles when Flux Input documents those features.
- Do not put a labeled `flux:input.group` directly in a form without wrapping it in `flux:field`.
- Do not use `class` when the style must target the actual input element; use `input:class`.
- Do not rely on `mask`, `type`, `disabled`, `readonly`, or `invalid` for validation or authorization.
- Do not use `copyable` for secrets or sensitive user data unless the product explicitly allows copying.
- Do not show `kbd` hints unless the shortcut actually exists.
- Do not invent undocumented Input, Input Group, Prefix, Suffix, slot, or state props.

## Component: Modal

Source: https://fluxui.dev/components/modal
Reviewed: 2026-06-20

### Purpose

Flux Modal displays content in a layer above the main page. It is documented for confirmations, alerts, and forms, and also supports flyout-style long-form dialogs.

### Basic usage

Named modal with a trigger:

```blade
<flux:modal.trigger name="edit-profile">
    <flux:button>{{ __('profile.edit') }}</flux:button>
</flux:modal.trigger>

<flux:modal name="edit-profile" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('profile.update_heading') }}</flux:heading>
            <flux:text class="mt-2">{{ __('profile.update_description') }}</flux:text>
        </div>

        <flux:input wire:model.blur="name" :label="__('profile.name')" />
        <flux:input wire:model.blur="dateOfBirth" type="date" :label="__('profile.date_of_birth')" />

        <div class="flex">
            <flux:spacer />
            <flux:button type="submit" variant="primary">{{ __('actions.save_changes') }}</flux:button>
        </div>
    </div>
</flux:modal>
```

Unique modal names inside loops:

```blade
@foreach ($users as $user)
    <flux:modal :name="'edit-profile-'.$user->id">
        ...
    </flux:modal>
@endforeach
```

Confirmation modal:

```blade
<flux:modal.trigger name="delete-profile">
    <flux:button variant="danger">{{ __('actions.delete') }}</flux:button>
</flux:modal.trigger>

<flux:modal name="delete-profile" class="min-w-[22rem]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('profile.delete_heading') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('profile.delete_warning') }}
            </flux:text>
        </div>

        <div class="flex gap-2">
            <flux:spacer />

            <flux:modal.close>
                <flux:button variant="ghost">{{ __('actions.cancel') }}</flux:button>
            </flux:modal.close>

            <flux:button type="submit" variant="danger">{{ __('actions.delete') }}</flux:button>
        </div>
    </div>
</flux:modal>
```

Flyout modal:

```blade
<flux:modal.trigger name="edit-profile">
    <flux:button>{{ __('profile.edit') }}</flux:button>
</flux:modal.trigger>

<flux:modal name="edit-profile" flyout>
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('profile.update_heading') }}</flux:heading>
            <flux:text class="mt-2">{{ __('profile.update_description') }}</flux:text>
        </div>

        <flux:input wire:model.blur="name" :label="__('profile.name')" />
        <flux:input wire:model.blur="dateOfBirth" type="date" :label="__('profile.date_of_birth')" />

        <div class="flex">
            <flux:spacer />
            <flux:button type="submit" variant="primary">{{ __('actions.save_changes') }}</flux:button>
        </div>
    </div>
</flux:modal>
```

Flyout positioning and floating variant:

```blade
<flux:modal name="edit-profile" flyout position="left">
    ...
</flux:modal>

<flux:modal name="edit-profile" flyout variant="floating" class="md:w-lg">
    <div class="space-y-6">
        <flux:heading size="lg">{{ __('profile.update_heading') }}</flux:heading>
        <flux:subheading>{{ __('profile.update_description') }}</flux:subheading>
        <flux:input wire:model.blur="name" :label="__('profile.name')" />
        <flux:input wire:model.blur="dateOfBirth" type="date" :label="__('profile.date_of_birth')" />
    </div>

    <x-slot name="footer" class="flex items-center justify-end gap-2">
        <flux:modal.close>
            <flux:button variant="filled">{{ __('actions.cancel') }}</flux:button>
        </flux:modal.close>

        <flux:button type="submit" variant="primary">{{ __('actions.save_changes') }}</flux:button>
    </x-slot>
</flux:modal>
```

Disable outside-click dismissal:

```blade
<flux:modal name="required-confirmation" :dismissible="false">
    ...
</flux:modal>
```

Long content:

```blade
<flux:modal name="terms" scroll="body">
    ...
</flux:modal>
```

Livewire state binding:

```blade
<flux:modal wire:model.self="showConfirmModal">
    ...
</flux:modal>
```

```php
use Livewire\Component;

class ShowPost extends Component
{
    public bool $showConfirmModal = false;

    public function delete(): void
    {
        $this->showConfirmModal = true;
    }
}
```

Livewire modal methods:

```blade
<flux:modal name="confirm">
    ...
</flux:modal>
```

```php
use Flux\Flux;
use Livewire\Component;

class ShowPost extends Component
{
    public function delete(): void
    {
        Flux::modal('confirm')->show();
        Flux::modal('confirm')->close();

        $this->modal('confirm')->show();
        $this->modal('confirm')->close();

        Flux::modals()->close();
    }
}
```

Alpine and browser control methods:

```blade
<flux:button x-on:click="$flux.modal('confirm').show()">
    {{ __('actions.open') }}
</flux:button>

<flux:button x-on:click="$flux.modal('confirm').close()">
    {{ __('actions.close') }}
</flux:button>

<flux:button x-on:click="$flux.modals().close()">
    {{ __('actions.close_all') }}
</flux:button>
```

Close and cancel events:

```blade
<flux:modal name="edit-profile" @close="modalClosed">
    ...
</flux:modal>

<flux:modal name="edit-profile" @cancel="modalCancelled">
    ...
</flux:modal>
```

The docs also allow `wire:close`, `wire:cancel`, `x-on:close`, and `x-on:cancel`.

### Props and attributes

`flux:modal` props and attributes:

- `name`: unique identifier for the modal. Required when using triggers.
- `flyout`: when true, the modal opens as a flyout.
- `variant`: visual style. Options: `default`, `floating`, `bare`; docs note legacy `flyout`.
- `position`: flyout direction. Options: `right` (default), `left`, `bottom`.
- `scroll`: scrolling behavior for long content. Option: `body`, which allows the viewport to scroll instead of clipping overflow.
- `dismissible`: when false, prevents closing the modal by clicking outside. Default: `true`.
- `closable`: when false, hides the close button. Default: `true`.
- `wire:model`: optional Livewire property binding for the modal open state.
- `class`: accepts width utilities; docs show common usage such as `md:w-96`, `min-w-[22rem]`, `md:w-lg`, and modal-specific sizing classes.

`flux:modal.trigger` props:

- `name`: name of the modal to trigger. Must match the modal's `name`.
- `shortcut`: keyboard shortcut to open the modal, such as `cmd.k`.

`flux:modal.close`:

- No documented props in the reference.

### Slots and child components

- `flux:modal` default slot: modal content.
- `flux:modal.trigger` default slot: trigger element, usually a `flux:button`.
- `flux:modal.close` default slot: close trigger element, usually a `flux:button`.
- Official examples show a named `footer` slot on a floating flyout, using `<x-slot name="footer">`.
- Modal content commonly composes `flux:heading`, `flux:subheading`, `flux:text`, `flux:input`, `flux:button`, `flux:spacer`, and `flux:modal.close`.

### Livewire and Laravel usage

- Prefer `flux:modal.trigger` and a matching `flux:modal name` for simple Blade-controlled modals.
- Generate unique modal names when rendering modals inside loops, such as `:name="'edit-profile-'.$user->id"`.
- Use `wire:model.self` when binding a Livewire property to a modal so nested input events do not interfere with modal state.
- Use `Flux::modal('name')->show()` and `Flux::modal('name')->close()` when a Livewire action needs to control a modal anywhere on the page.
- Use `$this->modal('name')->show()` and `$this->modal('name')->close()` when controlling a modal within the current Livewire component scope.
- Use `Flux::modals()->close()` when a Livewire action must close all modals on the page.
- Keep modal public properties compact: booleans, IDs, or short form state only.
- Validate all modal form submissions server-side in Livewire actions, form objects, form requests, services, or actions.
- Use `@close`, `wire:close`, or `x-on:close` when logic must run after any modal close.
- Use `@cancel`, `wire:cancel`, or `x-on:cancel` when logic must run only after outside-click or escape cancellation.
- For required confirmations, use `:dismissible="false"` only when accidental outside-click closure would be unsafe.

### Styling, variants, and states

- Default modal: centered layer above the page.
- Flyout modal: use the `flyout` prop for a more anchored, long-form dialog.
- Flyout positions: `right` default, `left`, `bottom`.
- Variants: `default`, `floating`, `bare`; docs mention legacy `flyout`.
- Width and sizing are controlled with classes on `flux:modal`; docs show `md:w-96`, `min-w-[22rem]`, and `md:w-lg`.
- Use `scroll="body"` for long content so the viewport scrolls instead of clipping overflow.
- Use `closable="false"` to hide the close button only when the flow provides another clear documented escape or completion path.
- Use `dismissible="false"` to disable outside-click dismissal when the action requires deliberate completion or cancellation.

### Project rules

- Prefer Flux Modal over custom modal markup for confirmations, alerts, forms, flyouts, and bottom-sheet-like dialogs.
- Use modal content sparingly on 320px screens; prefer short steps, flyouts, or bottom-positioned flyouts for longer mobile flows when documented behavior fits.
- Do not place huge lists, galleries, maps, or hidden filter trees inside modals by default; lazy-load heavy secondary UI.
- Every visible modal title, description, input label, placeholder, button label, and warning must use translation keys.
- For booking, pricing, availability, payments, and destructive actions, put the business logic in Livewire actions, services, or action classes, not in modal Blade markup.
- Use `flux:modal.close` around cancel buttons when the only behavior is closing the modal.
- Use `wire:click` or form submission for actions that must validate, authorize, persist, or recalculate before closing.
- Use unique modal names for repeated cards, search results, bookings, sleeping places, and host lists.
- Prefer `wire:model.self` over plain `wire:model` for modal open state in this project.

### Mistakes to avoid

- Do not create custom Tailwind/Alpine modal wrappers when Flux Modal covers the behavior.
- Do not reuse one static modal name inside a loop.
- Do not bind modal state with plain `wire:model` when nested inputs can dispatch events; use `wire:model.self`.
- Do not use undocumented modal props, positions, events, slots, or variants.
- Do not hide the close button or disable outside dismissal without providing a clear translated cancel/close path.
- Do not put destructive actions directly in Blade; call a Livewire action that validates and authorizes.
- Do not use modal shortcuts unless the shortcut is actually wired and discoverable.
- Do not put hard-coded visible text in modal examples or project Blade views.

## Component: Navbar

Source: https://fluxui.dev/components/navbar
Reviewed: 2026-06-20

### Purpose

Flux Navbar arranges navigation links horizontally or vertically. The component page documents horizontal `flux:navbar`, vertical `flux:navlist`, navbar items, navlist items, navlist groups, current-page detection, icons, badges, dropdown navigation, and collapsible groups.

### Basic usage

Horizontal navigation:

```blade
<flux:navbar>
    <flux:navbar.item :href="route('home')">{{ __('navigation.home') }}</flux:navbar.item>
    <flux:navbar.item :href="route('features')">{{ __('navigation.features') }}</flux:navbar.item>
    <flux:navbar.item :href="route('pricing')">{{ __('navigation.pricing') }}</flux:navbar.item>
    <flux:navbar.item :href="route('about')">{{ __('navigation.about') }}</flux:navbar.item>
</flux:navbar>
```

Current page control:

```blade
<flux:navbar.item :href="route('home')" current>{{ __('navigation.home') }}</flux:navbar.item>
<flux:navbar.item :href="route('home')" :current="false">{{ __('navigation.home') }}</flux:navbar.item>
<flux:navbar.item :href="route('home')" :current="request()->routeIs('home')">{{ __('navigation.home') }}</flux:navbar.item>
```

Icons:

```blade
<flux:navbar>
    <flux:navbar.item :href="route('home')" icon="home">{{ __('navigation.home') }}</flux:navbar.item>
    <flux:navbar.item :href="route('features')" icon="puzzle-piece">{{ __('navigation.features') }}</flux:navbar.item>
    <flux:navbar.item :href="route('pricing')" icon="currency-dollar">{{ __('navigation.pricing') }}</flux:navbar.item>
    <flux:navbar.item :href="route('profile')" icon="user">{{ __('navigation.profile') }}</flux:navbar.item>
</flux:navbar>
```

Badges:

```blade
<flux:navbar>
    <flux:navbar.item :href="route('home')">{{ __('navigation.home') }}</flux:navbar.item>
    <flux:navbar.item :href="route('inbox')" :badge="$unreadCount">{{ __('navigation.inbox') }}</flux:navbar.item>
    <flux:navbar.item :href="route('contacts')">{{ __('navigation.contacts') }}</flux:navbar.item>
    <flux:navbar.item :href="route('calendar')" :badge="__('plans.pro')" badge:color="lime">
        {{ __('navigation.calendar') }}
    </flux:navbar.item>
</flux:navbar>
```

Dropdown navigation:

```blade
<flux:navbar>
    <flux:navbar.item :href="route('dashboard')">{{ __('navigation.dashboard') }}</flux:navbar.item>
    <flux:navbar.item :href="route('transactions.index')">{{ __('navigation.transactions') }}</flux:navbar.item>

    <flux:dropdown>
        <flux:navbar.item icon:trailing="chevron-down">{{ __('navigation.account') }}</flux:navbar.item>

        <flux:navmenu>
            <flux:navmenu.item :href="route('profile')">{{ __('navigation.profile') }}</flux:navmenu.item>
            <flux:navmenu.item :href="route('settings')">{{ __('navigation.settings') }}</flux:navmenu.item>
            <flux:navmenu.item :href="route('billing')">{{ __('navigation.billing') }}</flux:navmenu.item>
        </flux:navmenu>
    </flux:dropdown>
</flux:navbar>
```

Vertical navlist:

```blade
<flux:navlist class="w-64">
    <flux:navlist.item :href="route('home')" icon="home">{{ __('navigation.home') }}</flux:navlist.item>
    <flux:navlist.item :href="route('features')" icon="puzzle-piece">{{ __('navigation.features') }}</flux:navlist.item>
    <flux:navlist.item :href="route('pricing')" icon="currency-dollar">{{ __('navigation.pricing') }}</flux:navlist.item>
    <flux:navlist.item :href="route('profile')" icon="user">{{ __('navigation.profile') }}</flux:navlist.item>
</flux:navlist>
```

Grouped navlist:

```blade
<flux:navlist>
    <flux:navlist.group :heading="__('navigation.account')" class="mt-4">
        <flux:navlist.item :href="route('profile')">{{ __('navigation.profile') }}</flux:navlist.item>
        <flux:navlist.item :href="route('settings')">{{ __('navigation.settings') }}</flux:navlist.item>
        <flux:navlist.item :href="route('billing')">{{ __('navigation.billing') }}</flux:navlist.item>
    </flux:navlist.group>
</flux:navlist>
```

Collapsible navlist group:

```blade
<flux:navlist class="w-64">
    <flux:navlist.item :href="route('dashboard')" icon="home">{{ __('navigation.dashboard') }}</flux:navlist.item>
    <flux:navlist.item :href="route('transactions.index')" icon="list-bullet">{{ __('navigation.transactions') }}</flux:navlist.item>

    <flux:navlist.group :heading="__('navigation.account')" expandable :expanded="false">
        <flux:navlist.item :href="route('profile')">{{ __('navigation.profile') }}</flux:navlist.item>
        <flux:navlist.item :href="route('settings')">{{ __('navigation.settings') }}</flux:navlist.item>
        <flux:navlist.item :href="route('billing')">{{ __('navigation.billing') }}</flux:navlist.item>
    </flux:navlist.group>
</flux:navlist>
```

Navlist badges:

```blade
<flux:navlist class="w-64">
    <flux:navlist.item :href="route('home')" icon="home">{{ __('navigation.home') }}</flux:navlist.item>
    <flux:navlist.item :href="route('inbox')" icon="envelope" :badge="$unreadCount">{{ __('navigation.inbox') }}</flux:navlist.item>
    <flux:navlist.item :href="route('contacts')" icon="user-group">{{ __('navigation.contacts') }}</flux:navlist.item>
    <flux:navlist.item :href="route('calendar')" icon="calendar-days" :badge="__('plans.pro')" badge:color="lime">
        {{ __('navigation.calendar') }}
    </flux:navlist.item>
</flux:navlist>
```

### Props and attributes

`flux:navbar`:

- Slot: `default`, the navigation items.
- Attribute: `data-flux-navbar`, applied to the root element for styling and identification.

`flux:navbar.item` props and attributes:

- `href`: URL the item links to.
- `current`: if true, applies active styling to the item. Auto-detected from the current URL when not specified.
- `icon`: icon name displayed at the start of the item.
- `icon:trailing`: icon name displayed at the end of the item.
- `badge`: trailing badge content. Can be a string, boolean, or slot.
- `badge:color`: badge color. Uses the same color options as the Badge component `color` prop.
- `badge:variant`: badge variant. Options: `solid`, `outline`. Default: `solid`.
- Attribute: `data-current`, applied when the item is active/current.

`flux:navlist`:

- Slot: `default`, the navigation items and groups.
- Attribute: `data-flux-navlist`, applied to the root element for styling and identification.

`flux:navlist.item` props and attributes:

- `href`: URL the item links to.
- `current`: if true, applies active styling to the item. Auto-detected from the current URL when not specified.
- `icon`: icon name displayed at the start of the item.
- `badge`: badge content. Can be a string, boolean, or slot.
- `badge:color`: badge color. Uses the same color options as the Badge component `color` prop.
- `badge:variant`: badge variant. Options: `solid`, `outline`. Default: `solid`.
- Attribute: `data-current`, applied when the item is active/current.

`flux:navlist.group` props:

- `heading`: text displayed as the group heading.
- `expandable`: if true, makes the group collapsible.
- `expanded`: if true, expands the group by default when expandable.
- Slot: `default`, the group's navigation items.

### Slots and child components

- `flux:navbar` contains `flux:navbar.item` children and may contain `flux:dropdown` for grouped navigation.
- `flux:navbar.item` default slot is the visible item label.
- `flux:dropdown` can wrap a `flux:navbar.item` trigger and a `flux:navmenu`.
- `flux:navmenu` can contain `flux:navmenu.item` links for dropdown navigation.
- `flux:navlist` contains `flux:navlist.item` and `flux:navlist.group` children.
- `flux:navlist.item` default slot is the visible item label.
- `flux:navlist.group` contains related `flux:navlist.item` children.

### Livewire and Laravel usage

- Use named routes for `href` values with `route(...)`; keep locale-aware route generation consistent with the project routing layer.
- Let Flux auto-detect active items from `href` for simple links.
- Use the `current` prop when active state needs explicit Laravel route logic, such as `:current="request()->routeIs('...')"`.
- Use `wire:navigate` only when the route is internal and it improves perceived speed without breaking localization, auth, validation, or browser behavior.
- Keep badge values as compact scalar data prepared by the Livewire class, presenter, or view model.
- Do not run count queries or relationship access inside navigation Blade; precompute counts with eager loading, `withCount`, cached model methods, or small DTOs.
- Use `flux:dropdown` with `flux:navmenu` for compact grouped navigation rather than packing many horizontal items into a mobile header.
- Use `flux:navlist` for vertical navigation or sidebar-style secondary navigation.

### Styling, variants, and states

- `flux:navbar` is a horizontal navigation container.
- `flux:navlist` is a vertical navigation container.
- Current items receive `data-current`.
- Navbar items support leading `icon`, trailing `icon:trailing`, `badge`, `badge:color`, and `badge:variant`.
- Navlist items support leading `icon`, `badge`, `badge:color`, and `badge:variant`.
- Navlist groups support collapsible behavior with `expandable` and default open state with `expanded`.
- Use width utilities such as `class="w-64"` on `flux:navlist` when a fixed sidebar width is appropriate.

### Project rules

- Prefer Flux Navbar/Navlist over custom navigation link markup when building horizontal, sidebar, or grouped navigation.
- Every visible navigation label, group heading, and textual badge must use translation keys.
- Keep mobile navigation small and scannable; use dropdowns, navlists, sidebars, or progressive disclosure instead of overloading the top bar.
- Do not create admin, staff, support, moderation, finance, cleaner, helper, or property-manager navigation sections unless explicitly requested.
- Use documented Heroicon names only; verify icons before adding them.
- Use badges only for meaningful compact status/count signals, not decorative noise.
- Keep current state deterministic for localized routes by passing `current` when URL auto-detection is not enough.
- Use the already documented Header and Sidebar layout rules when Navbar/Navlist appears inside a full app shell.

### Mistakes to avoid

- Do not build custom Tailwind navbars or sidebars when `flux:navbar`, `flux:navlist`, or documented Header/Sidebar layouts solve the need.
- Do not use hard-coded visible link labels or group headings.
- Do not place query logic, aggregate calls, or relationship traversal in navigation Blade to compute badges.
- Do not invent Navbar/Navlist props beyond `href`, `current`, `icon`, `icon:trailing`, `badge`, `badge:color`, `badge:variant`, `heading`, `expandable`, and `expanded`.
- Do not assume `icon:trailing` is documented for `flux:navlist.item`; the docs list it for `flux:navbar.item`.
- Do not use `badge:color` values that are not valid Badge component colors.
- Do not rely only on visual active state; route authorization and access control must remain server-side.

## Component: OTP Input

Source: https://fluxui.dev/components/otp-input
Reviewed: 2026-06-20

### Purpose

Flux OTP Input captures one-time passwords with a series of individual input fields. It supports fixed-length codes, automatic submission when filled, numeric/alphanumeric/alpha modes, private masked input, browser one-time-code autocomplete, custom input layouts, separators, and grouped input segments.

### Basic usage

Simple one-time password input:

```blade
<flux:otp wire:model="code" length="6" />
```

Verification form:

```blade
<flux:card>
    <form wire:submit="verify" class="space-y-8">
        <div class="max-w-64 mx-auto space-y-2">
            <flux:heading size="lg" class="text-center">{{ __('auth.verify_account') }}</flux:heading>
            <flux:text class="text-center">{{ __('auth.enter_one_time_password') }}</flux:text>
        </div>

        <flux:otp
            wire:model="code"
            length="6"
            :label="__('auth.otp_code')"
            label:sr-only
            :error:icon="false"
            error:class="text-center"
            class="mx-auto"
        />

        <div class="space-y-4">
            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('actions.verify') }}
            </flux:button>

            <flux:button wire:click="resend" class="w-full">
                {{ __('auth.resend_code') }}
            </flux:button>
        </div>
    </form>
</flux:card>
```

Autosubmit:

```blade
<form wire:submit="verify" class="space-y-8">
    <div class="max-w-64 mx-auto space-y-2">
        <flux:heading size="lg" class="text-center">{{ __('auth.verify_account') }}</flux:heading>
        <flux:text class="text-center">{{ __('auth.enter_one_time_password') }}</flux:text>
    </div>

    <div class="space-y-6">
        <flux:otp wire:model="code" length="6" submit="auto" class="mx-auto" />
    </div>
</form>
```

Alphanumeric or alpha codes:

```blade
<flux:otp
    wire:model="licenseKey"
    length="10"
    mode="alphanumeric"
    :label="__('licenses.key')"
    description:trailing="{{ __('licenses.key_help') }}"
/>
```

Private masked input:

```blade
<flux:otp wire:model="pin" length="4" private :label="__('auth.pin_code')" />
```

Custom inputs with a separator:

```blade
<flux:otp wire:model="code">
    <flux:otp.input />
    <flux:otp.input />
    <flux:otp.input />
    <flux:otp.separator />
    <flux:otp.input />
    <flux:otp.input />
    <flux:otp.input />
</flux:otp>
```

Grouped inputs:

```blade
<flux:otp wire:model="code">
    <flux:otp.group>
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
    </flux:otp.group>
</flux:otp>
```

Grouped inputs with separator:

```blade
<flux:otp wire:model="code">
    <flux:otp.group>
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
    </flux:otp.group>

    <flux:otp.separator />

    <flux:otp.group>
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
    </flux:otp.group>
</flux:otp>
```

Autocomplete control:

```blade
<flux:otp wire:model="code" length="6" autocomplete="off" />
```

### Props and attributes

`flux:otp` props and attributes:

- `wire:model`: binds the value to a Livewire property.
- `value`: current value as a string.
- `length`: number of input fields to display.
- `mode`: input mode. Options: `numeric` (default), `alphanumeric`, `alpha`.
- `private`: sets the input fields to private type to mask the input values.
- `submit`: keyboard behavior for form submission. Option: `auto`, which submits when all fields are filled. Default behavior is unnamed in the docs.
- `autocomplete`: sets the autocomplete attribute for the first input field. Default: `one-time-code`. Use `off` to disable browser autofill.
- Slot: `default`, custom input fields and separators. When used, the `length` prop is ignored.
- Official examples also show field/error presentation attributes on `flux:otp`: `label`, `label:sr-only`, `error:icon`, `error:class`, and `class`.

`flux:otp.input`:

- No documented props.

`flux:otp.separator`:

- No documented props.

`flux:otp.group`:

- Slot: `default`, the input fields to include in the group.

### Slots and child components

- `flux:otp` default slot accepts custom `flux:otp.input`, `flux:otp.separator`, and `flux:otp.group` children.
- Use `flux:otp.input` for each individual code character field when building a custom layout.
- Use `flux:otp.separator` to render a separator between input fields or groups.
- Use `flux:otp.group` to group input fields together.
- When the `flux:otp` default slot is used, `length` is ignored; the number of visible input fields comes from the child components.

### Livewire and Laravel usage

- Use `wire:model` as documented for OTP code state.
- Keep the bound Livewire property as a short string such as `code`, `pin`, or `licenseKey`.
- Validate OTP/PIN/license code values server-side in the Livewire action handling `wire:submit`.
- Use `submit="auto"` only when automatically submitting as soon as the code is complete is safe and expected.
- Use ordinary explicit submit buttons when users need to review, edit, or understand the verification action before sending.
- Use `private` for PIN-like values that should be masked on screen, but still treat the value as sensitive server-side.
- Use `autocomplete="off"` when browser one-time-code autofill is unwanted; otherwise rely on the documented default `one-time-code`.
- For resend flows, use a Livewire action such as `wire:click="resend"` and enforce throttling, expiry, and translated feedback server-side.

### Styling, variants, and states

- Default mode is `numeric`.
- Other documented modes are `alphanumeric` and `alpha`.
- `private` masks input values.
- `submit="auto"` submits after all fields are filled.
- Custom layouts can be created with `flux:otp.input`, `flux:otp.separator`, and `flux:otp.group`.
- Use `class` for layout utilities such as centering with `class="mx-auto"`.
- Official example centers error text with `:error:icon="false"` and `error:class="text-center"`.

### Project rules

- Prefer `flux:otp` over custom split-input markup for one-time passwords, PINs, short verification codes, and short license-key entry.
- Every visible heading, instruction, label, button label, error, and resend message must use translation keys.
- Keep OTP forms short, centered, and mobile-first; use large enough tap targets on 320px screens.
- Use `mode="numeric"` by default for authentication codes unless the backend actually issues alpha or alphanumeric codes.
- Use `mode="alphanumeric"` or `mode="alpha"` only when the issued code format requires it.
- Do not store long arrays of OTP digits in Livewire public properties; use the single documented bound string.
- Implement code expiry, attempt limits, resend throttling, and verification in actions/services, not in Blade.
- Clear sensitive OTP/PIN state after successful verification or when it is no longer needed.

### Mistakes to avoid

- Do not build custom OTP input fields when Flux OTP Input documents the needed behavior.
- Do not invent props for `flux:otp.input` or `flux:otp.separator`; the docs list none.
- Do not set both `length` expectations and custom child inputs without remembering that the default slot makes `length` ignored.
- Do not use `submit="auto"` for destructive, confusing, or multi-step verification flows.
- Do not rely on `private` as security; it only masks displayed values.
- Do not hard-code OTP labels, headings, instructions, button text, or error messages.
- Do not validate OTP format or correctness in Blade.

## Component: Pillbox

Source: https://fluxui.dev/components/pillbox
Reviewed: 2026-06-20

### Purpose

Flux Pillbox is a Flux Pro multi-select component that displays selected items as removable pills and expands the input area as needed. The docs cover basic multi-select, compact size, searchable options, custom search placeholders, option icons, combobox mode, creating new options, backend search, loading/empty messages, validation, and modal-based creation workflows.

### Basic usage

Basic multi-select:

```blade
<flux:pillbox wire:model="selectedTags" multiple :placeholder="__('tags.choose')">
    @foreach ($tagOptions as $tag)
        <flux:pillbox.option :value="$tag['id']">
            {{ $tag['label'] }}
        </flux:pillbox.option>
    @endforeach
</flux:pillbox>
```

Small size:

```blade
<flux:pillbox wire:model="selectedTags" size="sm" multiple :placeholder="__('tags.choose')">
    @foreach ($tagOptions as $tag)
        <flux:pillbox.option :value="$tag['id']">
            {{ $tag['label'] }}
        </flux:pillbox.option>
    @endforeach
</flux:pillbox>
```

Searchable options:

```blade
<flux:pillbox
    wire:model="selectedSkills"
    multiple
    searchable
    :placeholder="__('skills.choose')"
    search:placeholder="{{ __('skills.filter') }}"
>
    @foreach ($skillOptions as $skill)
        <flux:pillbox.option :value="$skill['id']">
            {{ $skill['label'] }}
        </flux:pillbox.option>
    @endforeach
</flux:pillbox>
```

Options with icons:

```blade
<flux:pillbox wire:model="selectedPlatforms" multiple :placeholder="__('platforms.choose')">
    <flux:pillbox.option value="github">
        <div class="flex items-center gap-2">
            <flux:icon.code-bracket variant="mini" class="text-zinc-400" />
            {{ __('platforms.github') }}
        </div>
    </flux:pillbox.option>

    <flux:pillbox.option value="gitlab">
        <div class="flex items-center gap-2">
            <flux:icon.server variant="mini" class="text-zinc-400" />
            {{ __('platforms.gitlab') }}
        </div>
    </flux:pillbox.option>
</flux:pillbox>
```

Combobox variant:

```blade
<flux:pillbox wire:model="selectedSkills" variant="combobox" multiple :placeholder="__('skills.choose')">
    @foreach ($skillOptions as $skill)
        <flux:pillbox.option :value="$skill['id']">
            {{ $skill['label'] }}
        </flux:pillbox.option>
    @endforeach
</flux:pillbox>
```

Create option:

```blade
<flux:pillbox wire:model="selectedTags" variant="combobox" multiple>
    <x-slot name="input">
        <flux:pillbox.input wire:model="search" :placeholder="__('tags.choose')" />
    </x-slot>

    @foreach ($tagOptions as $tag)
        <flux:pillbox.option :value="$tag['id']">
            {{ $tag['label'] }}
        </flux:pillbox.option>
    @endforeach

    <flux:pillbox.option.create wire:click="createTag" min-length="2">
        {{ __('tags.create_new') }} "<span wire:text="search"></span>"
    </flux:pillbox.option.create>
</flux:pillbox>
```

Backend search:

```blade
<flux:pillbox wire:model.live="selectedTags" variant="combobox" multiple :filter="false">
    <x-slot name="input">
        <flux:pillbox.input wire:model.live.debounce.500ms="search" :placeholder="__('tags.choose')" />
    </x-slot>

    @foreach ($tagOptions as $tag)
        <flux:pillbox.option :value="$tag['id']">
            {{ $tag['label'] }}
        </flux:pillbox.option>
    @endforeach

    <flux:pillbox.option.create wire:click="createTag" min-length="2">
        {{ __('tags.create') }} "<span wire:text="search"></span>"
    </flux:pillbox.option.create>
</flux:pillbox>
```

Loading and empty messages:

```blade
<flux:pillbox wire:model="selectedTags" variant="combobox" multiple :filter="false">
    <x-slot name="empty">
        <flux:pillbox.option.empty :when-loading="__('tags.loading')">
            {{ __('tags.none_found') }}
        </flux:pillbox.option.empty>
    </x-slot>
</flux:pillbox>
```

Create option with modal:

```blade
<flux:pillbox wire:model="projectId" variant="combobox" :placeholder="__('projects.choose')">
    <flux:pillbox.option.create modal="create-tag">
        {{ __('tags.create_new') }}
    </flux:pillbox.option.create>

    @foreach ($tagOptions as $tag)
        <flux:pillbox.option :value="$tag['id']">
            {{ $tag['label'] }}
        </flux:pillbox.option>
    @endforeach
</flux:pillbox>

<flux:modal name="create-tag" class="md:w-96">
    <form wire:submit="createTag" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('tags.create_heading') }}</flux:heading>
            <flux:text class="mt-2">{{ __('tags.create_description') }}</flux:text>
        </div>

        <flux:input
            wire:model.blur="newTagName"
            :label="__('tags.name')"
            :placeholder="__('tags.name_placeholder')"
        />

        <div class="flex">
            <flux:spacer />
            <flux:button type="submit" variant="primary">{{ __('actions.create') }}</flux:button>
        </div>
    </form>
</flux:modal>
```

### Props and attributes

`flux:pillbox` props and attributes:

- `wire:model`: binds the pillbox to a Livewire property. For multiple selections, the model should be an array of selected values.
- `placeholder`: text displayed when no pills are selected.
- `label`: label displayed above the pillbox; when provided, wraps the pillbox in a `flux:field` with `flux:label`.
- `description`: help text displayed below the pillbox; when used with `label`, appears between the label and pillbox within the `flux:field` wrapper.
- `size`: size option. Option: `sm`.
- `searchable`: adds a search input inside the dropdown to filter options.
- `search:placeholder`: custom placeholder text for the search input when `searchable` is true.
- `filter`: when false, disables client-side filtering for dynamic server-side options.
- `disabled`: prevents user interaction.
- `invalid`: applies error styling to the pillbox border.
- `multiple`: shown in official examples for multi-select pillboxes.
- `variant="combobox"`: shown in official examples to display an input directly inside the pillbox.
- Attribute: `data-flux-pillbox`, applied to the root element for styling and JavaScript behavior.

`flux:pillbox.option` props:

- `value`: value associated with the option; this is stored in the model array when selected.
- `label`: text displayed for the option.
- `selected-label`: text displayed when the option is selected.
- `disabled`: prevents the option from being selected.
- `filterable`: when false, the option will not be hidden by the search filter; useful for no-results messages.
- Slot: `default`, option content. Can include text, icons, images, or custom HTML.

`flux:pillbox.option.create` props:

- `min-length`: minimum number of characters required in the search input before showing the create option.
- `modal`: modal name to open when the option is selected.
- `wire:click`: Livewire action to call when the option is selected.

`flux:pillbox.option.empty` props:

- `when-loading`: message displayed when options are loading.
- Slot: `default`, message displayed when no options are found.

`flux:pillbox.search` props:

- `placeholder`: placeholder for the search input. Default: `Search...`.
- `icon`: icon displayed in the search input. Default: magnifying glass.
- `clearable`: when true, shows a clear button when the search input has text. Default: `true`.

`flux:pillbox.trigger` props:

- `placeholder`: text displayed when no pills are selected.
- `invalid`: applies error styling to the trigger element.
- `size`: trigger size. Option: `sm`.
- `clearable`: shows a clear-all button in the trigger.

### Slots and child components

- `flux:pillbox` default slot: pillbox options; should contain `flux:pillbox.option` components.
- `flux:pillbox` `trigger` slot: custom trigger element for the dropdown, replacing the default pill container.
- `flux:pillbox` `search` slot: custom search input for the dropdown according to the reference table.
- `flux:pillbox` `empty` slot: no-results/loading content, typically `flux:pillbox.option.empty`.
- Official create-option examples use `<x-slot name="input">` with `flux:pillbox.input` for custom search/create behavior.
- Use `flux:pillbox.option.create` to allow creating a new option from the search input.
- Use `flux:pillbox.option.empty` for no-results and loading messages.

### Livewire and Laravel usage

- Use an array Livewire property for multiple selected values, such as `public array $selectedTags = [];`.
- Keep option collections prepared in the Livewire class, presenter, or computed property; do not query in Blade.
- Use `wire:model.live` only when the selected values must update live; otherwise prefer normal `wire:model`.
- For backend search, use `:filter="false"` and bind the search input with a debounced Livewire model such as `wire:model.live.debounce.500ms="search"`.
- When using `:filter="false"`, make the backend option query include already-selected items when the search input is cleared.
- When creating new options from the pillbox, clear the search property after creation.
- When additional backend validation is used, reset the relevant error bag when the search input changes.
- Validate create-option actions server-side, enforce authorization, and use unique constraints where appropriate.
- Use `modal` on `flux:pillbox.option.create` for more complex creation workflows that need a form.

### Styling, variants, and states

- This is a Flux Pro component.
- `size="sm"` renders a smaller pillbox for compact layouts.
- `searchable` adds a search input for client-side filtering.
- `variant="combobox"` displays an input directly inside the pillbox.
- `disabled` prevents interaction.
- `invalid` applies error styling.
- `flux:pillbox.trigger` supports `clearable` for clearing all selected pills.
- Option content can include icons or custom HTML through the default slot.

### Project rules

- Prefer `flux:pillbox` over custom multi-select chips/pills for tag, amenity, skill, rule, platform, and other small multi-select controls.
- Use translations for placeholders, labels, descriptions, empty messages, loading messages, create labels, modal text, and option labels when labels are visible UI text.
- Keep selected values as IDs or short stable strings, not full models or large arrays.
- For large option sets, use backend search with `:filter="false"`, selected-column queries, indexes, and limited result counts.
- For rent2gether geo, city, amenity, rule, and lookup data, load options from local SQLite data or cached lookup tables; do not call external APIs from the pillbox search flow.
- Do not load huge option lists into a Pillbox on first render; use searchable/backend search and progressive disclosure.
- Keep creation flows transactional and authorized in Livewire actions/services.
- On mobile, keep pillbox use focused; avoid crowding long filter screens with many expanded pill groups.

### Mistakes to avoid

- Do not invent undocumented Pillbox props, child components, slots, or variants.
- Do not query inside Blade loops to render options.
- Do not pass full Eloquent models as selected values; store IDs or stable scalar values.
- Do not rely on frontend uniqueness checks alone when creating options.
- Do not forget to include selected records in backend-search result sets when `:filter="false"` and the search input is cleared.
- Do not leave stale validation errors after the user changes the search input.
- Do not hard-code visible option labels, placeholders, empty messages, loading messages, or create labels.
- Do not use Pillbox for massive datasets without server-side search, limits, and indexes.

## Component: Popover

Source: https://fluxui.dev/components/popover
Reviewed: 2026-06-20

### Purpose

Flux Popover is a Flux Pro component for showing extra generic content in a floating popup on click or hover. The docs say to use it only when the Dropdown menu component does not fit the need. Every popover must be used inside a `flux:dropdown`, which manages positioning and interaction.

### Basic usage

Options popover:

```blade
<flux:dropdown>
    <flux:button
        icon="adjustments-horizontal"
        icon:variant="micro"
        icon:class="text-zinc-400"
        icon-trailing="chevron-down"
        icon-trailing:variant="micro"
        icon-trailing:class="text-zinc-400"
    >
        {{ __('filters.options') }}
    </flux:button>

    <flux:popover class="flex flex-col gap-4">
        <flux:radio.group
            wire:model="sort"
            :label="__('filters.sort_by')"
            label:class="text-zinc-500 dark:text-zinc-400"
        >
            <flux:radio value="active" :label="__('filters.recently_active')" />
            <flux:radio value="posted" :label="__('filters.date_posted')" />
        </flux:radio.group>

        <flux:separator variant="subtle" />

        <flux:radio.group
            wire:model="view"
            :label="__('filters.view_as')"
            label:class="text-zinc-500 dark:text-zinc-400"
        >
            <flux:radio value="list" :label="__('filters.list')" />
            <flux:radio value="gallery" :label="__('filters.gallery')" />
        </flux:radio.group>

        <flux:separator variant="subtle" />

        <flux:button
            variant="subtle"
            size="sm"
            class="justify-start -m-2 px-2!"
            wire:click="resetFilters"
        >
            {{ __('filters.reset_to_default') }}
        </flux:button>
    </flux:popover>
</flux:dropdown>
```

Hover trigger:

```blade
<flux:dropdown hover position="bottom" align="start" offset="-16" gap="10">
    <button type="button" class="flex items-center gap-3">
        <flux:avatar size="sm" :name="$userName" :src="$userAvatarUrl" />
        <flux:heading>{{ $userName }}</flux:heading>
    </button>

    <flux:popover class="flex flex-col gap-3 rounded-xl shadow-xl">
        <flux:avatar size="xl" :name="$userName" :src="$userAvatarUrl" />

        <div>
            <flux:heading size="lg">{{ $userName }}</flux:heading>
            <div class="flex items-center gap-2">
                <flux:text size="lg">{{ $userHandle }}</flux:text>
                <flux:badge>{{ __('profile.follows_you') }}</flux:badge>
            </div>
        </div>

        <div class="flex gap-2">
            <flux:button variant="outline" size="sm" icon="check" class="flex-1">
                {{ __('profile.following') }}
            </flux:button>
            <flux:button variant="primary" size="sm" icon="chat-bubble-left-right" class="flex-1">
                {{ __('messages.message') }}
            </flux:button>
        </div>
    </flux:popover>
</flux:dropdown>
```

Position and alignment:

```blade
<flux:dropdown position="top" align="start">
    <flux:button>{{ __('actions.open') }}</flux:button>
    <flux:popover>...</flux:popover>
</flux:dropdown>

<flux:dropdown position="right" align="center">
    <flux:button>{{ __('actions.open') }}</flux:button>
    <flux:popover>...</flux:popover>
</flux:dropdown>

<flux:dropdown position="bottom" align="end">
    <flux:button>{{ __('actions.open') }}</flux:button>
    <flux:popover>...</flux:popover>
</flux:dropdown>
```

Gap and offset:

```blade
<flux:dropdown gap="16">
    <flux:button>{{ __('actions.open') }}</flux:button>
    <flux:popover>...</flux:popover>
</flux:dropdown>

<flux:dropdown offset="32">
    <flux:button>{{ __('actions.open') }}</flux:button>
    <flux:popover>...</flux:popover>
</flux:dropdown>
```

Category picker:

```blade
<flux:dropdown>
    <flux:button icon="tag" icon:variant="micro" icon:class="text-zinc-400">
        {{ __('categories.categories') }}

        <x-slot name="iconTrailing">
            <flux:badge size="sm" class="-mr-1">
                <span x-text="$wire.categories.length" class="tabular-nums">&nbsp;</span>
            </flux:badge>
        </x-slot>
    </flux:button>

    <flux:popover class="max-w-[18rem] flex flex-col gap-4">
        <flux:checkbox.group variant="pills" wire:model="categories">
            @foreach ($categoryOptions as $category)
                <flux:checkbox :value="$category['id']" :label="$category['label']" />
            @endforeach
        </flux:checkbox.group>

        <flux:separator variant="subtle" />

        <flux:button
            variant="subtle"
            size="sm"
            class="justify-start -m-2 !px-2"
            wire:click="$set('categories', [])"
        >
            {{ __('actions.clear_all') }}
        </flux:button>
    </flux:popover>
</flux:dropdown>
```

Feedback form:

```blade
<flux:dropdown>
    <flux:button icon="chat-bubble-oval-left" icon:variant="micro" icon:class="text-zinc-300">
        {{ __('feedback.feedback') }}
    </flux:button>

    <flux:popover class="min-w-[18rem] flex flex-col gap-4">
        <flux:radio.group variant="buttons" class="*:flex-1" wire:model="feedbackType">
            <flux:radio icon="bug-ant" value="bug">{{ __('feedback.bug_report') }}</flux:radio>
            <flux:radio icon="light-bulb" value="suggestion">{{ __('feedback.suggestion') }}</flux:radio>
            <flux:radio icon="question-mark-circle" value="question">{{ __('feedback.question') }}</flux:radio>
        </flux:radio.group>

        <flux:textarea
            wire:model.blur="feedbackMessage"
            rows="6"
            class="dark:bg-transparent!"
            :placeholder="__('feedback.message_placeholder')"
        />

        <div class="flex gap-2 justify-end">
            <flux:button variant="filled" size="sm" class="w-28">
                {{ __('actions.cancel') }}
            </flux:button>
            <flux:button size="sm" class="w-28" wire:click="submitFeedback">
                {{ __('actions.submit') }}
            </flux:button>
        </div>
    </flux:popover>
</flux:dropdown>
```

### Props and attributes

`flux:dropdown` when used with popover:

- `position`: position of the popover relative to the trigger. Options: `top`, `right`, `bottom` (default), `left`.
- `align`: alignment relative to the trigger. Options: `start` (default), `center`, `end`.
- `hover`: opens the popover on hover instead of click. The docs note that currently only the `button` element can be used as the trigger element for hover.
- `wire:model`: binds the open/closed state to a Livewire property for programmatic control.
- `gap`: documented in examples; controls the distance between trigger and popover in pixels.
- `offset`: documented in examples; shifts the popover along its alignment axis in pixels.
- Slot: `default`, exactly one trigger element followed by one `flux:popover`.
- Attribute: `data-flux-dropdown`, applied to the root element for styling and identification.
- Attribute: `data-open`, applied when the popover is open.

`flux:popover`:

- `class`: CSS classes applied to the popover container; useful for width constraints such as `max-w-sm` or `w-80`.
- Slot: `default`, content displayed inside the popover. Can include HTML and Flux components.
- Attribute: `data-flux-popover`, applied to the root element for styling and identification.

### Slots and child components

- `flux:dropdown` must contain exactly one trigger element followed by one `flux:popover`.
- The trigger can be a `flux:button`, `button`, link, or similar trigger element; hover examples currently use a `button` element.
- `flux:popover` can contain generic content, HTML, and Flux components such as `flux:radio.group`, `flux:checkbox.group`, `flux:separator`, `flux:button`, `flux:avatar`, `flux:heading`, `flux:text`, `flux:badge`, `flux:textarea`, `flux:icon`, and `flux:link`.
- Use `class` on `flux:popover` for width and layout control.

### Livewire and Laravel usage

- Use Popover for generic floating content only when `flux:dropdown` menus, `flux:tooltip`, or a modal/bottom sheet do not fit the interaction.
- Keep popover state compact when using `wire:model`: booleans and small filter state only.
- Keep form state and filter state in Livewire class properties; validate actions server-side.
- Use `wire:model.change` or normal `wire:model` for radio/checkbox controls inside popovers according to the relevant component rules.
- Use `wire:model.blur` for textarea/input text inside popovers.
- Prepare option lists in Livewire classes or presenters; do not query inside popover Blade loops.
- For category/filter popovers, keep selected values as short IDs/strings and reset with Livewire actions or documented `$set` patterns.

### Styling, variants, and states

- This is a Flux Pro component.
- `flux:popover` has no documented variants; layout is controlled with `class`.
- `position` and `align` are set on `flux:dropdown`.
- `gap` and `offset` are set on `flux:dropdown` when spacing/shift control is needed.
- `hover` opens the popover on hover instead of click.
- The `data-open` attribute appears on `flux:dropdown` while open and can be used by Flux styling patterns.
- Use compact width classes such as `max-w-[18rem]`, `min-w-[18rem]`, or `w-80` cautiously on mobile.

### Project rules

- Prefer `flux:popover` over custom floating panels when the content is small, contextual, and not a full workflow.
- Prefer `flux:dropdown`/`flux:navmenu` for simple action or navigation menus; Popover is for generic content that does not fit menu semantics.
- Avoid hover-only popovers for essential mobile interactions because this project is mobile-first and touch-first.
- Do not put large forms, huge filter trees, maps, galleries, or long lists inside popovers; use drawers, bottom sheets, modals, pagination, or lazy components.
- Every visible trigger label, option label, form label, button label, placeholder, and status text must use translation keys.
- For filters, categories, amenities, and lookup data, keep lists local SQLite/cached and small; use backend search or progressive disclosure for larger data.
- Keep popover content readable on 320px screens with constrained width and short copy.

### Mistakes to avoid

- Do not use `flux:popover` outside `flux:dropdown`.
- Do not put more than one trigger element before the popover inside a `flux:dropdown`.
- Do not use Popover for ordinary dropdown menus when the Dropdown/Navmenu component fits.
- Do not make critical actions or required information hover-only.
- Do not invent undocumented Popover props or variants.
- Do not hard-code visible text in popover examples or project Blade.
- Do not query inside popover Blade loops.
- Do not use Popover as a replacement for server-side authorization, validation, or confirmation flows.

## Component: Profile

Source: https://fluxui.dev/components/profile
Reviewed: 2026-06-20

### Purpose

Flux Profile displays a user's profile with an avatar and optional name in a compact, interactive component. It is commonly used as a trigger for profile dropdown menus, profile switchers, account menus, and compact user identity controls.

### Basic usage

Avatar only:

```blade
<flux:profile :avatar="$userAvatarUrl" />
```

With name:

```blade
<flux:profile :name="$userName" :avatar="$userAvatarUrl" />
```

Without chevron:

```blade
<flux:profile :chevron="false" :avatar="$userAvatarUrl" />
```

Circle avatar:

```blade
<flux:profile circle :chevron="false" :avatar="$userAvatarUrl" />
<flux:profile circle :name="$userName" :avatar="$userAvatarUrl" />
```

Initials fallback:

```blade
<flux:profile :name="$userName" />
<flux:profile :name="$userName" avatar:color="cyan" />
<flux:profile :initials="$userInitials" />
<flux:profile avatar:name="{{ $userName }}" />
```

Custom trailing icon:

```blade
<flux:profile
    icon:trailing="chevron-up-down"
    :avatar="$userAvatarUrl"
    :name="$userName"
/>
```

Custom avatar slot:

```blade
<flux:profile :name="$userName">
    <x-slot name="avatar">
        <flux:avatar :src="$userAvatarUrl" :name="$userName" />
    </x-slot>
</flux:profile>
```

Profile menu:

```blade
<flux:dropdown align="end">
    <flux:profile :avatar="$userAvatarUrl" />

    <flux:navmenu class="max-w-[12rem]">
        <div class="px-2 py-1.5">
            <flux:text size="sm">{{ __('account.signed_in_as') }}</flux:text>
            <flux:heading class="mt-1! truncate">{{ $userEmail }}</flux:heading>
        </div>

        <flux:navmenu.separator />

        <flux:navmenu.item :href="route('profile')" icon="user" class="text-zinc-800 dark:text-white">
            {{ __('navigation.account') }}
        </flux:navmenu.item>

        <flux:navmenu.separator />

        <flux:navmenu.item icon="arrow-right-start-on-rectangle" class="text-zinc-800 dark:text-white" wire:click="logout">
            {{ __('auth.logout') }}
        </flux:navmenu.item>
    </flux:navmenu>
</flux:dropdown>
```

Profile switcher:

```blade
<flux:dropdown position="top" align="start">
    <flux:profile :avatar="$userAvatarUrl" :name="$userName" />

    <flux:menu>
        <flux:menu.radio.group wire:model="activeProfileId">
            @foreach ($profileOptions as $profile)
                <flux:menu.radio :value="$profile['id']">
                    {{ $profile['label'] }}
                </flux:menu.radio>
            @endforeach
        </flux:menu.radio.group>

        <flux:menu.separator />

        <flux:menu.item icon="arrow-right-start-on-rectangle" wire:click="logout">
            {{ __('auth.logout') }}
        </flux:menu.item>
    </flux:menu>
</flux:dropdown>
```

### Props and attributes

`flux:profile` props:

- `name`: user's name displayed next to the avatar.
- `avatar`: URL to the image to display as avatar, or custom content can be passed via the `avatar` named slot.
- `avatar:name`: name used for avatar initial generation.
- `avatar:color`: color used for the avatar; see the Avatar component color documentation for available options.
- `circle`: whether to display a circular avatar. Default: `false`.
- `initials`: custom initials displayed when no avatar image is provided; automatically generated from `name` if not provided.
- `chevron`: whether to display a chevron dropdown indicator. Default: `true`.
- `icon:trailing`: custom icon displayed instead of the chevron; accepts any icon name.
- `icon:variant`: icon variant for the trailing icon. Options: `micro` (default), `outline`.

### Slots and child components

- Named slot `avatar`: custom content for the avatar section, typically a `flux:avatar` component.
- `flux:profile` is commonly used inside `flux:dropdown` as the trigger element.
- Profile menu examples compose `flux:profile`, `flux:dropdown`, `flux:navmenu`, `flux:navmenu.item`, and `flux:navmenu.separator`.
- Profile switcher examples compose `flux:profile`, `flux:dropdown`, `flux:menu`, `flux:menu.radio.group`, `flux:menu.radio`, `flux:menu.separator`, and `flux:menu.item`.

### Livewire and Laravel usage

- Prepare display values such as `$userName`, `$userEmail`, `$userAvatarUrl`, `$userInitials`, and profile-switcher options before rendering the Blade view.
- Use the Avatar reference rules when customizing the avatar slot.
- Use `flux:profile` as a compact profile trigger inside `flux:dropdown` for account menus and switchers.
- Use named routes for account/profile links.
- For logout, prefer the project's existing secure logout action or route pattern; do not introduce unsafe GET logout behavior just because the docs show simple href examples.
- Keep profile switcher state as compact IDs or enum-like values in Livewire.
- Do not query user/team/profile relationships inside the dropdown Blade; eager load or prepare DTO arrays before rendering.

### Styling, variants, and states

- `circle` renders the avatar circular.
- `:chevron="false"` hides the default chevron.
- `icon:trailing` replaces the default chevron with a custom icon.
- `icon:variant` supports `micro` default and `outline`.
- Initials are generated from `name` when no avatar image is provided, unless `initials` is specified.
- `avatar:color` controls the fallback avatar color when using initials.
- Use truncation classes in surrounding menu text for long names or emails, as shown in the official menu example.

### Project rules

- Prefer `flux:profile` over custom avatar/name dropdown triggers for account menus, host/guest switchers, and profile switchers.
- Use user-generated names and emails as data, but translate every surrounding label such as "signed in as", "account", and "logout".
- Use stored or trusted avatar URLs; do not add live external avatar lookups to normal page render paths without an explicit product decision.
- Use initials fallback when avatar images are missing.
- Keep profile menus small on mobile; move larger account workflows to pages, modals, or drawers.
- Do not add admin/staff/support/finance/moderation profile menu destinations unless explicitly requested.
- Ensure account/profile links still rely on backend authorization; profile visibility is not access control.

### Mistakes to avoid

- Do not hand-roll avatar/name/chevron triggers when `flux:profile` fits.
- Do not invent undocumented Profile props beyond `name`, `avatar`, `avatar:name`, `avatar:color`, `circle`, `initials`, `chevron`, `icon:trailing`, and `icon:variant`.
- Do not hard-code account menu labels.
- Do not compute initials, profile options, or team lists with queries inside Blade.
- Do not expose sensitive user data in compact menus.
- Do not use a GET logout link unless the existing Laravel auth flow explicitly supports it safely.

## Component: Progress

Source: https://fluxui.dev/components/progress
Reviewed: 2026-06-20

### Purpose

Flux Progress displays a progress bar for completion or loading status. Use it for determinate progress such as onboarding completion, profile completion, upload progress, booking-step progress, storage usage, or any workflow where the application knows the current value.

### Basic usage

Simple progress bar:

```blade
<flux:progress value="75" />
```

Custom maximum for a non-percentage scale:

```blade
<flux:progress value="3" max="7" />
```

Custom color:

```blade
<flux:progress value="75" color="purple" />
```

Custom height:

```blade
<flux:progress value="75" class="h-3" />
```

With label and description:

```blade
<flux:field>
    <flux:label>{{ __('progress.upload.label') }}</flux:label>
    <flux:progress value="42" color="blue" />
    <flux:description>{{ __('progress.upload.description', ['current' => 3, 'total' => 7]) }}</flux:description>
</flux:field>
```

With a displayed value in the label:

```blade
<flux:field>
    <flux:label>
        {{ __('progress.storage.label') }}

        <x-slot name="trailing">
            <span class="tabular-nums">{{ $storagePercent }}%</span>
        </x-slot>
    </flux:label>

    <flux:progress :value="$storagePercent" />
</flux:field>
```

With a displayed value inline:

```blade
<flux:field>
    <flux:label>{{ __('progress.storage.label') }}</flux:label>

    <div class="flex items-center gap-4 -mt-2">
        <flux:progress :value="$storagePercent" />
        <span class="text-sm tabular-nums">{{ $storagePercent }}%</span>
    </div>
</flux:field>
```

Controlled by Livewire:

```blade
<flux:slider wire:model="progress" />
<flux:progress wire:model="progress" />
```

### Props and attributes

`flux:progress` props:

- `value`: current progress value from `0` to `max`. Default: `0`.
- `max`: maximum value. Default: `100`.
- `color`: bar fill color, such as `blue`, `red`, or `green`. Default: the configured accent color.
- `class`: standard class attribute; the docs show it for changing height, such as `class="h-3"`.

Documented CSS variables:

- `--flux-progress`: computed progress as a raw number from `0` to `100`.
- `--flux-progress-percentage`: computed progress as a percentage string, such as `75%`, used internally for the bar width.

### Slots and child components

- `flux:progress` has no documented slots or child components.
- Wrap `flux:progress` in `flux:field` when a label, description, or grouped field semantics are needed.
- Use `flux:label` for the label and `flux:description` for supporting text.
- Use the `flux:label` trailing slot when the current value should appear beside the label.
- The docs show `flux:slider` with `wire:model` as a companion control for dynamically changing the progress value.

### Livewire and Laravel usage

- Use `wire:model` on `flux:progress` when the value is controlled by a Livewire property.
- Keep progress values numeric and bounded in the Livewire class or DTO before rendering.
- Compute workflow progress, profile completion, upload completion, and booking-step completion in PHP services, actions, presenters, or Livewire computed state; do not calculate business progress in Blade.
- Use translation keys for labels, descriptions, inline value explanations, and loading messages.
- For file uploads, first check the File Upload reference: `flux:file-upload.dropzone` has a documented `with-progress` pattern for upload progress.
- Use `flux:progress` for determinate progress. Use Skeleton for placeholder loading when the app does not know a concrete progress value.
- Do not store large progress histories or item arrays in Livewire public properties; keep only the current numeric value, maximum, or compact step state.

### Styling, variants, and states

- Default maximum is `100`; set `max` for non-percentage progress such as `3` of `7`.
- Default value is `0`.
- Default color is the configured accent color.
- Use `color` to override the fill color.
- Use `class` to customize height, such as `class="h-3"`.
- Use `tabular-nums` on adjacent text when displaying numeric percentages so values do not visually jump.
- The docs do not document Progress variants, sizes, icons, or named states beyond `value`, `max`, `color`, `class`, CSS variables, and Livewire binding.

### Project rules

- Prefer `flux:progress` over custom Tailwind progress bars when showing determinate completion or loading status.
- Always provide a meaningful visible label, or use an `sr-only` label when the progress bar has no visible label.
- On mobile, keep progress labels short and place secondary explanations in `flux:description` or nearby translated text.
- Use server-derived values for booking completion, host onboarding, profile completion, document verification, upload status, and multi-step form progress.
- Keep colors purposeful and consistent with the app theme; do not use decorative one-off colors for critical status without a clear meaning.
- Clamp progress values in PHP to avoid invalid values below `0` or above `max`.
- For slow 3G workflows, pair long-running actions with clear progress or loading feedback, but do not fake determinate progress when only indeterminate loading is known.

### Mistakes to avoid

- Do not invent undocumented props such as `variant`, `size`, `label`, `description`, `striped`, `animated`, or `indeterminate`.
- Do not use `flux:progress` without accessible labeling when it communicates meaningful status.
- Do not hard-code visible progress labels or descriptions in Blade.
- Do not calculate booking, pricing, verification, or completion percentages inline in Blade.
- Do not use Progress where Skeleton, Toast, File Upload `with-progress`, or a step indicator is the documented better fit.
- Do not rely on the CSS variables as the source of business truth; treat them as styling hooks only.

## Component: Radio

Source: https://fluxui.dev/components/radio
Reviewed: 2026-06-20

### Purpose

Flux Radio lets users select one option from a mutually exclusive set. Use it for single-choice questions, settings, grouped controls, filters with one active value, and card-style choice lists.

### Basic usage

Basic radio group:

```blade
<flux:radio.group wire:model.change="payment" label="{{ __('checkout.payment_method') }}">
    <flux:radio value="cc" label="{{ __('checkout.payment_methods.credit_card') }}" checked />
    <flux:radio value="paypal" label="{{ __('checkout.payment_methods.paypal') }}" />
    <flux:radio value="ach" label="{{ __('checkout.payment_methods.bank_transfer') }}" />
</flux:radio.group>
```

With descriptions:

```blade
<flux:radio.group label="{{ __('users.role') }}">
    <flux:radio
        name="role"
        value="administrator"
        label="{{ __('users.roles.administrator') }}"
        description="{{ __('users.roles.administrator_description') }}"
        checked
    />

    <flux:radio
        name="role"
        value="editor"
        label="{{ __('users.roles.editor') }}"
        description="{{ __('users.roles.editor_description') }}"
    />

    <flux:radio
        name="role"
        value="viewer"
        label="{{ __('users.roles.viewer') }}"
        description="{{ __('users.roles.viewer_description') }}"
    />
</flux:radio.group>
```

Within a fieldset:

```blade
<flux:fieldset>
    <flux:legend>{{ __('users.role') }}</flux:legend>

    <flux:radio.group>
        <flux:radio
            value="administrator"
            label="{{ __('users.roles.administrator') }}"
            description="{{ __('users.roles.administrator_description') }}"
            checked
        />

        <flux:radio
            value="editor"
            label="{{ __('users.roles.editor') }}"
            description="{{ __('users.roles.editor_description') }}"
        />
    </flux:radio.group>
</flux:fieldset>
```

Segmented:

```blade
<flux:radio.group wire:model.change="role" label="{{ __('users.role') }}" variant="segmented">
    <flux:radio label="{{ __('users.roles.admin') }}" />
    <flux:radio label="{{ __('users.roles.editor') }}" />
    <flux:radio label="{{ __('users.roles.viewer') }}" />
</flux:radio.group>
```

Segmented small:

```blade
<flux:radio.group wire:model.change="role" label="{{ __('users.role') }}" variant="segmented" size="sm">
    <flux:radio label="{{ __('users.roles.admin') }}" />
    <flux:radio label="{{ __('users.roles.editor') }}" />
    <flux:radio label="{{ __('users.roles.viewer') }}" />
</flux:radio.group>
```

Segmented with icons:

```blade
<flux:radio.group wire:model.change="role" variant="segmented">
    <flux:radio label="{{ __('users.roles.admin') }}" icon="wrench" />
    <flux:radio label="{{ __('users.roles.editor') }}" icon="pencil-square" />
    <flux:radio label="{{ __('users.roles.viewer') }}" icon="eye" />
</flux:radio.group>
```

Radio cards:

```blade
<flux:radio.group wire:model.change="shipping" label="{{ __('checkout.shipping') }}" variant="cards" class="max-sm:flex-col">
    <flux:radio value="standard" label="{{ __('checkout.shipping.standard') }}" description="{{ __('checkout.shipping.standard_description') }}" checked />
    <flux:radio value="fast" label="{{ __('checkout.shipping.fast') }}" description="{{ __('checkout.shipping.fast_description') }}" />
    <flux:radio value="next-day" label="{{ __('checkout.shipping.next_day') }}" description="{{ __('checkout.shipping.next_day_description') }}" />
</flux:radio.group>
```

Vertical cards:

```blade
<flux:radio.group label="{{ __('checkout.shipping') }}" variant="cards" class="flex-col">
    <flux:radio value="standard" label="{{ __('checkout.shipping.standard') }}" description="{{ __('checkout.shipping.standard_description') }}" />
    <flux:radio value="fast" label="{{ __('checkout.shipping.fast') }}" description="{{ __('checkout.shipping.fast_description') }}" />
    <flux:radio value="next-day" label="{{ __('checkout.shipping.next_day') }}" description="{{ __('checkout.shipping.next_day_description') }}" />
</flux:radio.group>
```

Cards with icons:

```blade
<flux:radio.group label="{{ __('checkout.shipping') }}" variant="cards" class="max-sm:flex-col">
    <flux:radio value="standard" icon="truck" label="{{ __('checkout.shipping.standard') }}" description="{{ __('checkout.shipping.standard_description') }}" />
    <flux:radio value="fast" icon="cube" label="{{ __('checkout.shipping.fast') }}" description="{{ __('checkout.shipping.fast_description') }}" />
    <flux:radio value="next-day" icon="clock" label="{{ __('checkout.shipping.next_day') }}" description="{{ __('checkout.shipping.next_day_description') }}" />
</flux:radio.group>
```

Cards without indicators:

```blade
<flux:radio.group label="{{ __('checkout.shipping') }}" variant="cards" :indicator="false" class="max-sm:flex-col">
    <flux:radio value="standard" icon="truck" label="{{ __('checkout.shipping.standard') }}" description="{{ __('checkout.shipping.standard_description') }}" />
    <flux:radio value="fast" icon="cube" label="{{ __('checkout.shipping.fast') }}" description="{{ __('checkout.shipping.fast_description') }}" />
    <flux:radio value="next-day" icon="clock" label="{{ __('checkout.shipping.next_day') }}" description="{{ __('checkout.shipping.next_day_description') }}" />
</flux:radio.group>
```

Custom card content:

```blade
<flux:radio.group label="{{ __('checkout.shipping') }}" variant="cards" class="max-sm:flex-col">
    <flux:radio value="standard" checked>
        <flux:radio.indicator />

        <div class="flex-1">
            <flux:heading class="leading-4">{{ __('checkout.shipping.standard') }}</flux:heading>
            <flux:text size="sm" class="mt-2">{{ __('checkout.shipping.standard_description') }}</flux:text>
        </div>
    </flux:radio>

    <flux:radio value="fast">
        <flux:radio.indicator />

        <div class="flex-1">
            <flux:heading class="leading-4">{{ __('checkout.shipping.fast') }}</flux:heading>
            <flux:text size="sm" class="mt-2">{{ __('checkout.shipping.fast_description') }}</flux:text>
        </div>
    </flux:radio>
</flux:radio.group>
```

Pills:

```blade
<flux:radio.group wire:model.change="priority" label="{{ __('filters.priority') }}" variant="pills">
    <flux:radio value="low" label="{{ __('filters.priorities.low') }}" />
    <flux:radio value="medium" label="{{ __('filters.priorities.medium') }}" />
    <flux:radio value="high" label="{{ __('filters.priorities.high') }}" />
    <flux:radio value="critical" label="{{ __('filters.priorities.critical') }}" />
</flux:radio.group>
```

Buttons:

```blade
<flux:radio.group variant="buttons" class="w-full *:flex-1" label="{{ __('feedback.type') }}">
    <flux:radio icon="bug-ant" checked>{{ __('feedback.types.bug_report') }}</flux:radio>
    <flux:radio icon="light-bulb">{{ __('feedback.types.suggestion') }}</flux:radio>
    <flux:radio icon="question-mark-circle">{{ __('feedback.types.question') }}</flux:radio>
</flux:radio.group>
```

### Props and attributes

`flux:radio.group` props:

- `wire:model`: binds the radio group selection to a Livewire property.
- `label`: label text displayed above the radio group. When provided, wraps the radio group in a `flux:field` with an adjacent `flux:label`.
- `description`: help text displayed below the radio group. When provided alongside `label`, appears between the label and radio group within the `flux:field` wrapper.
- `variant`: visual style of the group. Options: `default`, `segmented`, `cards`, `pills`, `buttons`.
- `invalid`: applies error styling to the radio group.
- `size="sm"`: documented for smaller segmented radios.
- `:indicator="false"`: documented on card groups to remove the radio indicator.

`flux:radio.group` attributes:

- `data-flux-radio-group`: applied to the root element for styling and identification.

`flux:radio` props:

- `label`: label text displayed above the radio button. When provided, wraps the radio button in a `flux:field` with an adjacent `flux:label`.
- `description`: help text displayed below the radio button. When provided alongside `label`, appears between the label and radio button within the `flux:field` wrapper.
- `value`: value associated with the radio button when used in a group.
- `checked`: selects the radio button by default when true.
- `disabled`: prevents user interaction with the radio button.
- `icon`: icon name to display for the segmented variant and documented card/button examples.

`flux:radio` attributes:

- `data-flux-radio`: applied to the root element for styling and identification.
- `data-checked`: applied when the radio button is selected.

### Slots and child components

- `flux:radio.group` default slot: the radio buttons grouped together.
- `flux:radio` default slot: custom content for the card variant.
- `flux:radio.indicator`: used for custom radio button layouts in the card variant.
- Related layout components shown in the docs: `flux:fieldset`, `flux:legend`, `flux:heading`, and `flux:text`.

### Livewire and Laravel usage

- Use `flux:radio.group` with `wire:model` for Livewire-bound single-choice state; in this project prefer `wire:model.change` for radio inputs to follow the saved Livewire rules.
- Keep radio values as short strings, IDs, or enum-like values that are validated server-side.
- Use Laravel validation such as `Rule::in(...)` or enum validation for every radio-backed action.
- Prepare option arrays in Livewire classes or presenters; do not query or build business choices inside Blade.
- Use `checked` only for static default examples or server-rendered defaults; for Livewire forms, prefer initializing the bound property in the class.
- Use `disabled` for options the user cannot currently select, and pair it with a translated explanation when the reason is not obvious.
- Use `flux:fieldset` and `flux:legend` when a group needs extra semantic context.

### Styling, variants, and states

- Group `variant` options are `default`, `segmented`, `cards`, `pills`, and `buttons`.
- `segmented` is a compact alternative to standard radio buttons and supports icons; the docs also show `size="sm"`.
- `cards` creates bordered radio choices, supports vertical layout with `class="flex-col"`, responsive vertical layout with `class="max-sm:flex-col"`, icons, custom card content, and optional indicator removal with `:indicator="false"`.
- `pills` creates compact rounded options suited to filters, categories, and lightweight selectable choices.
- `buttons` creates button-style grouped controls; the docs show `class="w-full *:flex-1"` for equal-width buttons.
- `checked` marks a default-selected radio.
- `disabled` prevents interaction.
- `invalid` applies error styling to the group.
- `data-checked` is available on selected radios for styling hooks.

### Project rules

- Prefer `flux:radio.group` and `flux:radio` over custom radio inputs for all mutually exclusive choices.
- Use `flux:radio.group` for small, known option sets; use `flux:select` or autocomplete for long or searchable option lists.
- Use `variant="pills"` for compact single-choice filters in mobile drawers or bottom sheets.
- Use `variant="segmented"` for mode switches with two to four options where all choices fit on 320px screens.
- Use `variant="cards"` for choices that need descriptions, prices, timing, or explanations, and use `class="max-sm:flex-col"` for mobile stacking.
- Use `variant="buttons"` only when the choice should look like a toolbar or prominent action selector.
- Every visible label, description, legend, card heading, and option text must use translation keys.
- Keep the option count small on first render; do not render huge hidden radio lists.
- For booking, verification, search filters, guest/host mode, shipping/payment-like choices, and settings, keep selected values in compact Livewire properties and validate on submit/action.

### Mistakes to avoid

- Do not use radios for multi-select choices; use Checkbox, Pillbox, or Select patterns instead.
- Do not use radios for a single binary on/off preference when Switch or Checkbox is more appropriate.
- Do not invent undocumented group variants beyond `default`, `segmented`, `cards`, `pills`, and `buttons`.
- Do not invent undocumented radio props beyond the documented props and documented examples listed here.
- Do not hard-code visible labels, descriptions, or card text in Blade.
- Do not calculate option availability or prices inline in radio card Blade.
- Do not rely on `checked` to fight a Livewire-bound value; initialize the Livewire property instead.
- Do not load country/city/amenity-scale datasets as radio groups.

## Component: Select

Source: https://fluxui.dev/components/select
Reviewed: 2026-06-20

### Purpose

Flux Select lets users choose a single option from a dropdown list, with Pro variants for custom listbox and combobox behavior. Use it for bounded choices that are too long for radio buttons or checkboxes, and for searchable/backend-backed choices when the documented listbox or combobox patterns fit.

The official docs recommend considering Checkbox or Radio instead of Select for lists of up to 5 items.

### Basic usage

Native select:

```blade
<flux:select wire:model.change="industry" placeholder="{{ __('industries.choose') }}">
    @foreach ($industryOptions as $industry)
        <flux:select.option :value="$industry['value']">
            {{ $industry['label'] }}
        </flux:select.option>
    @endforeach
</flux:select>
```

Native select with field wrapper props:

```blade
<flux:select
    wire:model.change="roleMode"
    label="{{ __('profiles.fields.role_mode') }}"
    description="{{ __('profiles.help.role_mode') }}"
    badge="{{ __('common.labels.required') }}"
    placeholder="{{ __('profiles.placeholders.role_mode') }}"
>
    <flux:select.option value="guest">
        {{ __('users.role_modes.guest') }}
    </flux:select.option>

    <flux:select.option value="host">
        {{ __('users.role_modes.host') }}
    </flux:select.option>

    <flux:select.option value="guest_host">
        {{ __('users.role_modes.guest_host') }}
    </flux:select.option>
</flux:select>
```

Small native select:

```blade
<flux:select size="sm" wire:model.change="industry" placeholder="{{ __('industries.choose') }}">
    @foreach ($industryOptions as $industry)
        <flux:select.option :value="$industry['value']">
            {{ $industry['label'] }}
        </flux:select.option>
    @endforeach
</flux:select>
```

Custom listbox select:

```blade
<flux:select variant="listbox" wire:model.change="industry" placeholder="{{ __('industries.choose') }}">
    @foreach ($industryOptions as $industry)
        <flux:select.option :value="$industry['value']">
            {{ $industry['label'] }}
        </flux:select.option>
    @endforeach
</flux:select>
```

Custom button slot for listbox:

```blade
<flux:select variant="listbox" wire:model.change="industry">
    <x-slot name="button">
        <flux:select.button
            class="rounded-full!"
            placeholder="{{ __('industries.choose') }}"
            :invalid="$errors->has('industry')"
        />
    </x-slot>

    @foreach ($industryOptions as $industry)
        <flux:select.option :value="$industry['value']">
            {{ $industry['label'] }}
        </flux:select.option>
    @endforeach
</flux:select>
```

Clearable listbox:

```blade
<flux:select variant="listbox" wire:model.change="industry" clearable>
    @foreach ($industryOptions as $industry)
        <flux:select.option :value="$industry['value']">
            {{ $industry['label'] }}
        </flux:select.option>
    @endforeach
</flux:select>
```

Options with icons:

```blade
<flux:select variant="listbox" wire:model.change="role" placeholder="{{ __('roles.choose') }}">
    <flux:select.option value="owner">
        <div class="flex items-center gap-2">
            <flux:icon.shield-check variant="mini" class="text-zinc-400" />
            {{ __('roles.owner') }}
        </div>
    </flux:select.option>

    <flux:select.option value="administrator">
        <div class="flex items-center gap-2">
            <flux:icon.key variant="mini" class="text-zinc-400" />
            {{ __('roles.administrator') }}
        </div>
    </flux:select.option>
</flux:select>
```

Searchable listbox:

```blade
<flux:select variant="listbox" searchable wire:model.change="industry" placeholder="{{ __('industries.choose') }}">
    @foreach ($industryOptions as $industry)
        <flux:select.option :value="$industry['value']">
            {{ $industry['label'] }}
        </flux:select.option>
    @endforeach
</flux:select>
```

Custom search slot:

```blade
<flux:select variant="listbox" searchable wire:model.change="industry">
    <x-slot name="search">
        <flux:select.search class="px-4" placeholder="{{ __('industries.search') }}" />
    </x-slot>

    @foreach ($industryOptions as $industry)
        <flux:select.option :value="$industry['value']">
            {{ $industry['label'] }}
        </flux:select.option>
    @endforeach
</flux:select>
```

Multiple listbox:

```blade
<flux:select
    variant="listbox"
    multiple
    wire:model.change="selectedIndustries"
    placeholder="{{ __('industries.choose') }}"
    selected-suffix="{{ __('industries.selected_suffix') }}"
>
    @foreach ($industryOptions as $industry)
        <flux:select.option :value="$industry['value']">
            {{ $industry['label'] }}
        </flux:select.option>
    @endforeach
</flux:select>
```

Multiple listbox with checkbox indicator:

```blade
<flux:select variant="listbox" multiple indicator="checkbox" wire:model.change="selectedIndustries">
    @foreach ($industryOptions as $industry)
        <flux:select.option :value="$industry['value']">
            {{ $industry['label'] }}
        </flux:select.option>
    @endforeach
</flux:select>
```

Clear search on close:

```blade
<flux:select variant="listbox" searchable multiple clear="close" wire:model.change="selectedIndustries">
    @foreach ($industryOptions as $industry)
        <flux:select.option :value="$industry['value']">
            {{ $industry['label'] }}
        </flux:select.option>
    @endforeach
</flux:select>
```

Combobox:

```blade
<flux:select variant="combobox" wire:model.change="industry" placeholder="{{ __('industries.choose') }}">
    @foreach ($industryOptions as $industry)
        <flux:select.option :value="$industry['value']">
            {{ $industry['label'] }}
        </flux:select.option>
    @endforeach
</flux:select>
```

Custom input slot for combobox:

```blade
<flux:select variant="combobox" wire:model.change="industry">
    <x-slot name="input">
        <flux:select.input
            wire:model.live.debounce.500ms="search"
            placeholder="{{ __('industries.search') }}"
            :invalid="$errors->has('search')"
        />
    </x-slot>

    @foreach ($this->industries as $industry)
        <flux:select.option :value="$industry['id']" :wire:key="$industry['id']">
            {{ $industry['label'] }}
        </flux:select.option>
    @endforeach
</flux:select>
```

Backend search:

```blade
<flux:select wire:model.change="userId" variant="combobox" :filter="false">
    <x-slot name="input">
        <flux:select.input wire:model.live.debounce.500ms="search" placeholder="{{ __('users.search') }}" />
    </x-slot>

    @foreach ($this->users as $user)
        <flux:select.option :value="$user->id" :wire:key="$user->id">
            {{ $user->name }}
        </flux:select.option>
    @endforeach
</flux:select>
```

Create option:

```blade
<flux:select wire:model.change="projectId" variant="combobox">
    <x-slot name="input">
        <flux:select.input wire:model.live.debounce.500ms="search" placeholder="{{ __('projects.search') }}" />
    </x-slot>

    @foreach ($this->projects as $project)
        <flux:select.option :value="$project->id" :wire:key="$project->id">
            {{ $project->name }}
        </flux:select.option>
    @endforeach

    <flux:select.option.create wire:click="createProject" min-length="2">
        {{ __('projects.create_from_search') }} "<span wire:text="search"></span>"
    </flux:select.option.create>
</flux:select>
```

Custom empty/loading state:

```blade
<flux:select wire:model.change="projectId" variant="combobox" :filter="false">
    <x-slot name="input">
        <flux:select.input wire:model.live.debounce.500ms="search" placeholder="{{ __('projects.search') }}" />
    </x-slot>

    @foreach ($this->projects as $project)
        <flux:select.option :value="$project->id" :wire:key="$project->id">
            {{ $project->name }}
        </flux:select.option>
    @endforeach

    <x-slot name="empty">
        <flux:select.option.empty when-loading="{{ __('projects.loading') }}">
            {{ __('projects.none_found') }}
        </flux:select.option.empty>
    </x-slot>
</flux:select>
```

Create option with modal:

```blade
<flux:select wire:model.change="projectId" variant="listbox">
    @foreach ($this->projects as $project)
        <flux:select.option :value="$project->id">
            {{ $project->name }}
        </flux:select.option>
    @endforeach

    <flux:select.option.create modal="create-project">
        {{ __('projects.create_new') }}
    </flux:select.option.create>
</flux:select>
```

### Props and attributes

`flux:select` props:

- `wire:model`: binds the select to a Livewire property.
- `placeholder`: text displayed when no option is selected.
- `label`: label text displayed above the select. When provided, wraps the select in a `flux:field` with an adjacent `flux:label`.
- `description`: help text displayed below the select. When provided alongside `label`, appears between the label and select within the `flux:field` wrapper.
- `description:trailing`: displays the description below the select instead of above it.
- `badge`: badge text displayed at the end of `flux:label` when `label` is provided.
- `size`: select size. Options: `sm`, `xs`.
- `variant`: visual style. Options: `default` native select, `listbox`, `combobox`.
- `multiple`: allows selecting multiple options. Listbox variant only.
- `filter`: when `false`, disables client-side filtering.
- `searchable`: adds a search input to filter options. Listbox and combobox variants only.
- `empty`: message shown when no search results are found for searchable selects. Default: `No results found`.
- `clearable`: displays a clear button when an option is selected. Listbox and combobox variants only.
- `selected-suffix`: text appended to the number of selected options in multiple mode. Listbox variant only.
- `clear`: when to clear the search input. Options: `select` default, `close`. Listbox and combobox variants only.
- `disabled`: prevents user interaction with the select.
- `invalid`: applies error styling to the select.
- `indicator="checkbox"`: documented for multiple listbox selects when a checkbox indicator is preferred over the default checkmark.

`flux:select` attributes:

- `data-flux-select`: applied to the root element for styling and identification.

`flux:select.option` props:

- `value`: value associated with the option.
- `label`: text content displayed for the option.
- `selected-label`: text content displayed when the option is selected.
- `disabled`: prevents selecting the option.

`flux:select.option.create` props:

- `min-length`: minimum number of characters required in the search input before displaying the create option.
- `modal`: name of the modal to open when the option is selected.
- `wire:click`: Livewire action to call when the option is selected.

`flux:select.option.empty` props:

- `when-loading`: message displayed when options are loading.

`flux:select.button` props:

- `placeholder`: text displayed when no option is selected.
- `invalid`: applies error styling to the button.
- `size`: button size. Options: `sm`, `xs`.
- `disabled`: prevents selecting the option.
- `clearable`: displays a clear button when an option is selected.

`flux:select.input` props:

- `placeholder`: text displayed when no option is selected.
- `invalid`: applies error styling to the input.
- `size`: input size. Options: `sm`, `xs`.

`flux:select.search` props:

- `placeholder`: placeholder text displayed when the input is empty.
- `icon`: name of the icon displayed at the start of the input.
- `clearable`: displays a clear button when the input has content. Default: `true`.

### Slots and child components

- `flux:select` default slot: the select options.
- `flux:select` trigger slot: custom trigger content, typically `flux:select.button` or `flux:select.input` for listbox and combobox variants.
- `flux:select` empty slot: custom content shown when no options are found for searchable selects, typically `flux:select.option.empty`.
- Documented named slots include `button`, `search`, `input`, and `empty`.
- `flux:select.option` default slot: option content, including icons and images in the listbox variant.
- `flux:select.option.empty` default slot: message displayed when no options are found.
- `flux:select.option.create` creates new options from select search.
- `flux:select.button` customizes the listbox trigger.
- `flux:select.input` customizes the combobox trigger/input.
- `flux:select.search` customizes the search field inside a searchable listbox.

### Livewire and Laravel usage

- In this project, prefer `wire:model.change` for ordinary select values.
- For backend search inside `flux:select.input`, prefer `wire:model.live.debounce.500ms` or `wire:model.live.debounce.750ms` so search stays responsive without firing on every keystroke.
- Prepare option arrays or computed query results in Livewire classes, presenters, or services; do not query or build option lists in Blade.
- Keep Livewire public state compact: selected IDs/strings, search terms, and small option DTOs only.
- Validate selected values server-side with enum rules, `Rule::in(...)`, `exists`, ownership checks, and policies where relevant.
- Use `:filter="false"` for backend search when options are generated on the server.
- Limit backend search result sets; the official example uses `limit(20)`.
- When using backend search with create option, ensure the query includes the newly selected/created item when the search input is cleared.
- When using the listbox variant with backend search and create option, clear the search input after creation to avoid an extra request when the frontend clears the input.
- When additional backend validation is performed for create option search input, reset the search field error bag when the input updates.
- Use `flux:select.option.create` only when creating the option is a real supported workflow with validation, authorization, and translated feedback.

### Styling, variants, and states

- `default` is the native browser select.
- `listbox` is the custom select variant, useful for custom option styling, icons, images, searchable lists, multiple selection, clearable values, and custom trigger/search slots.
- `combobox` is a Pro variant for autocomplete-style selection and backend search workflows.
- `size` supports `sm` and `xs`.
- `multiple` is listbox-only.
- `searchable`, `clearable`, `clear`, custom `button`, custom `search`, custom `input`, and custom `empty` behavior apply only to the documented custom variants.
- `disabled` prevents interaction.
- `invalid` applies error styling.
- `selected-label` can customize the selected display text for an option.
- `selected-suffix` customizes the localized multiple-selection suffix.
- `indicator="checkbox"` changes the multiple listbox indicator to a checkbox.

### Accessibility requirements or recommendations

- The official Select page does not document explicit ARIA props.
- Prefer Radio or Checkbox for very short option lists so mobile users can see choices without opening a dropdown.
- Keep placeholders and selected labels concise, translated, and meaningful without relying only on visual context.
- Do not use color, icons, or images inside listbox options as the only way to distinguish important choices; include readable text.
- Preserve Flux validation styling through `invalid`, `flux:error`, or field wrappers when the selected value fails backend validation.
- Keep option menus small enough for 320px mobile screens. For large lists, use searchable/backend-search patterns rather than a huge dropdown.

### Project rules

- Prefer `flux:select` over custom `<select>` markup when a dropdown choice is needed.
- For up to 5 simple choices, consider Flux Radio or Checkbox first as the official docs recommend.
- Use Select only for bounded choice lists; use Autocomplete or backend search for large datasets.
- Never load full countries, cities, amenities, or other large catalogs into a select on first render.
- For country/city search in this project, prefer the saved Autocomplete pattern backed by imported SQLite geo data instead of a huge Select.
- Use `variant="listbox"` when custom option content, multiple selection, clearable behavior, or searchable listbox behavior is needed.
- Use `variant="combobox"` for searchable single-choice flows where users type to narrow options.
- Keep option labels translated unless the label is user-generated content or imported proper-name data.
- Use compact mobile layouts and short placeholders; avoid opening enormous option menus on 320px screens.
- Do not create new options from a Select unless the domain supports it and the Livewire action handles validation, authorization, duplicate prevention, and translated errors.

### Mistakes to avoid

- Do not invent undocumented Select variants beyond `default`, `listbox`, and `combobox`.
- Do not use `multiple` outside the `listbox` variant.
- Do not use `searchable`, `clearable`, custom `button`, custom `search`, custom `input`, or custom `empty` behavior on unsupported variants.
- Do not hard-code visible placeholders, option labels, empty messages, loading messages, or create-option text in Blade.
- Do not query inside `@foreach` loops for select options.
- Do not render huge hidden option lists for mobile filters.
- Do not use Select for multi-select pill entry when Pillbox better matches the documented interaction.
- Do not rely on frontend filtering for authorization, ownership, availability, or booking rules.
- Do not use `flux:select.option.create` as a shortcut around proper backend validation and duplicate checks.

## Component: Separator

Source: https://fluxui.dev/components/separator
Reviewed: 2026-06-20

### Purpose

Flux Separator visually divides sections of content or groups of items. Use it when a simple divider improves scanability without adding a card, nested panel, or heavier layout element.

### Basic usage

Horizontal separator:

```blade
<flux:separator />
```

Separator with text:

```blade
<flux:separator text="{{ __('auth.or') }}" />
```

Vertical separator:

```blade
<flux:separator vertical />
```

Vertical separator with limited height:

```blade
<flux:separator vertical class="my-2" />
```

Subtle vertical separator:

```blade
<flux:separator vertical variant="subtle" />
```

Orientation prop:

```blade
<flux:separator orientation="horizontal" />
<flux:separator orientation="vertical" />
```

### Props and attributes

`flux:separator` props:

- `vertical`: displays a vertical separator. Default is horizontal.
- `variant`: visual style variant. Options: `subtle`. Default: standard separator.
- `text`: optional text to display in the center of the separator.
- `orientation`: alternative to the `vertical` prop. Options: `horizontal`, `vertical`. Default: `horizontal`.

Documented class usage:

- `my-*`: commonly used to shorten vertical separators by adding vertical margin.

Documented attribute:

- `data-flux-separator`: applied to the root element for styling and identification.

### Slots and child components

- `flux:separator` has no documented slots or child components.
- Use the `text` prop for centered separator text instead of slot content.
- Do not confuse `flux:separator` with compound separators such as `flux:menu.separator` or `flux:otp.separator`; those belong to their parent component APIs.

### Livewire and Laravel usage

- Separator is presentational and does not bind to Livewire state.
- Use translated strings for the `text` prop.
- Use separators in Blade views to divide related sections after the surrounding data has already been prepared by Livewire components, presenters, or services.
- Do not use separators to hide authorization, validation, or workflow boundaries; enforce those in backend logic.

### Styling, variants, and states

- Horizontal is the default orientation.
- Use `vertical` or `orientation="vertical"` for horizontally stacked content.
- Use `orientation="horizontal"` when explicit orientation is clearer than relying on the default.
- Use `variant="subtle"` when the divider should blend into the background.
- Use `class="my-2"` or other `my-*` vertical margin utilities to limit vertical separator height as shown in the official docs.
- No sizes, colors, icons, Livewire events, or interactive states are documented for `flux:separator`.

### Project rules

- Prefer `flux:separator` over custom border dividers when a simple content divider is needed.
- Keep separators sparse on mobile; use spacing, headings, or `flux:field` grouping when those are clearer.
- Use text separators only for short translated labels such as "or"; avoid sentence-length separator text.
- Use vertical separators only when the horizontal layout remains readable at 320px, otherwise stack content and use the default horizontal separator or spacing.
- Use component-specific separators such as `flux:menu.separator` inside menus and `flux:otp.separator` inside OTP inputs.

### Mistakes to avoid

- Do not invent undocumented variants beyond `subtle`.
- Do not pass children or slot content to `flux:separator`; use the documented `text` prop.
- Do not hard-code separator text in Blade.
- Do not use separators as a replacement for semantic headings, fieldsets, validation errors, or authorization checks.
- Do not use `flux:separator` inside compound components when that component documents its own separator child.
- Do not overuse vertical separators on mobile layouts where stacked spacing is more readable.

## Component: Skeleton

Source: https://fluxui.dev/components/skeleton
Reviewed: 2026-06-20

### Purpose

Flux Skeleton creates placeholder content while data is loading. Use it for loading states that preserve the layout shape of text, avatars, tables, cards, charts, and other content before real data is available.

### Basic usage

Avatar and text placeholder:

```blade
<flux:skeleton.group animate="shimmer" class="flex items-center gap-4">
    <flux:skeleton class="size-10 rounded-full" />

    <div class="flex-1">
        <flux:skeleton.line />
        <flux:skeleton.line class="w-1/2" />
    </div>
</flux:skeleton.group>
```

Line of text placeholders:

```blade
<flux:skeleton.group animate="shimmer">
    <flux:skeleton.line class="mb-2 w-1/4" />
    <flux:skeleton.line />
    <flux:skeleton.line />
    <flux:skeleton.line class="w-3/4" />
</flux:skeleton.group>
```

Animation options:

```blade
<flux:skeleton />
<flux:skeleton animate="shimmer" />
<flux:skeleton animate="pulse" />
```

Table loading state:

```blade
<flux:skeleton.group animate="shimmer">
    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('orders.customer') }}</flux:table.column>
            <flux:table.column>{{ __('orders.date') }}</flux:table.column>
            <flux:table.column>{{ __('orders.status') }}</flux:table.column>
            <flux:table.column>{{ __('orders.amount') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach (range(1, 5) as $row)
                <flux:table.row>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:skeleton class="size-5 rounded-full" />
                            <div class="flex-1">
                                <flux:skeleton.line class="w-3/4" />
                            </div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:skeleton.line />
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:skeleton.line />
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:skeleton.line />
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</flux:skeleton.group>
```

Chart loading state:

```blade
<flux:card class="dark:bg-zinc-800">
    <div class="flex flex-col gap-6">
        <div class="flex gap-12">
            <div>
                <flux:text>{{ __('charts.today') }}</flux:text>
                <flux:heading size="xl" class="mt-2 tabular-nums">---</flux:heading>
                <flux:text class="mt-2 tabular-nums">--:--</flux:text>
            </div>

            <div>
                <flux:text>{{ __('charts.yesterday') }}</flux:text>
                <flux:heading size="lg" class="mt-2 tabular-nums">---</flux:heading>
            </div>
        </div>

        <flux:skeleton animate="shimmer" class="aspect-[4/1] size-full rounded-lg" />
    </div>
</flux:card>
```

Livewire loading placeholder:

```blade
<div wire:loading.delay wire:target="search" aria-hidden="true">
    <flux:skeleton.group animate="shimmer" class="space-y-3">
        <flux:skeleton class="h-28 rounded-lg" />
        <flux:skeleton.line />
        <flux:skeleton.line class="w-2/3" />
    </flux:skeleton.group>
</div>
```

### Props and attributes

`flux:skeleton` props:

- `animate`: animation style for the skeleton. Options: `shimmer`, `pulse`. Defaults to no animation.

`flux:skeleton` slot:

- `default`: the skeleton elements, such as lines, boxes, and circles.

`flux:skeleton` CSS variable:

- `--flux-shimmer-color`: background color used for shimmer animation. Defaults to white in light mode and `var(--color-zinc-900)` in dark mode. Set this to match the page background so shimmer does not show through gaps between skeleton elements.

`flux:skeleton` attribute:

- `data-flux-skeleton`: applied to the root element for styling and identification.

`flux:skeleton.line` props:

- `size`: size of the line. Options: `base`, `lg`. Default: `base`.
- `animate`: animation style for the skeleton line. Options: `shimmer`, `pulse`. Defaults to no animation. Can be inherited from parent `flux:skeleton.group`.

`flux:skeleton.line` attribute:

- `data-flux-skeleton-line`: applied to the root element for styling and identification.

`flux:skeleton.group` props:

- `animate`: animation style for all skeleton elements inside the group. Options: `shimmer`, `pulse`. Defaults to no animation. Child `flux:skeleton` and `flux:skeleton.line` components inherit it.

`flux:skeleton.group` attribute:

- `data-flux-skeleton-group`: applied to the root element for styling and identification.

### Slots and child components

- `flux:skeleton.group`: groups skeleton elements and can provide inherited animation.
- `flux:skeleton`: generic skeleton placeholder for boxes, circles, cards, charts, and custom shapes.
- `flux:skeleton.line`: text-like placeholder that occupies the full spatial line height while rendering a slimmer bar.
- Use standard `class` attributes to define shape and dimensions, such as `size-10 rounded-full`, `w-1/2`, `h-28 rounded-lg`, or `aspect-[4/1] size-full rounded-lg`.
- Skeleton examples compose with `flux:table`, `flux:card`, `flux:text`, and `flux:heading`.

### Livewire and Laravel usage

- Use Skeleton for indeterminate loading placeholders where the shape of future content is known.
- Use `wire:loading`, `wire:loading.delay`, and `wire:target` to show skeletons during Livewire requests.
- Use `aria-hidden="true"` for purely decorative skeleton placeholders when accessible status text is provided elsewhere.
- Keep skeleton markup compact and close to the eventual layout shape; do not render heavy hidden content behind it.
- Use `flux:progress` instead when a concrete numeric progress value is known.
- Do not calculate placeholder widths or shapes with runtime randomness in Blade; use stable classes so SSR and hydration remain predictable.
- Prepare real loading data and empty states in Livewire classes or presenters; Skeleton is only the temporary visual state.

### Styling, variants, and states

- Animation options are `shimmer` and `pulse`; default is no animation.
- `flux:skeleton.group animate="..."` passes the animation to child skeletons and skeleton lines.
- `flux:skeleton.line` size options are `base` and `lg`.
- Use classes for shape, width, height, spacing, rounded corners, and aspect ratio.
- Use `--flux-shimmer-color` when shimmer needs to match a custom page or card background.
- No documented color, variant, icon, label, value, or Livewire binding props exist for Skeleton beyond the APIs listed here.

### Project rules

- Prefer `flux:skeleton` over custom Tailwind placeholder divs when showing loading UI.
- Use skeletons for slow 3G and mobile-first workflows where users need immediate layout feedback.
- Keep skeletons lightweight on 320px screens; avoid large repeated placeholder DOM for long lists.
- Match the skeleton shape to the eventual content enough to prevent layout shift.
- Use Skeleton for loading states, not for empty states; empty states must use translated explanatory text and next actions.
- Avoid indefinite shimmer overuse; if loading can fail or take long, also provide translated status/error feedback.
- For tables, cards, search results, booking quotes, media, and charts, use compact placeholder counts rather than mirroring an entire large dataset.

### Mistakes to avoid

- Do not invent undocumented animation values beyond `shimmer` and `pulse`.
- Do not invent undocumented Skeleton props such as `variant`, `color`, `rows`, `circle`, `width`, or `height`; use classes for dimensions and shape.
- Do not use Skeleton as a substitute for validation messages, empty states, or authorization handling.
- Do not hard-code visible loading/status copy in Blade when pairing skeletons with text.
- Do not render huge skeleton lists that cost more than the content they replace.
- Do not use random widths in Blade for skeleton lines in this project; keep skeleton markup deterministic.

## Component: Slider

Source: https://fluxui.dev/components/slider
Reviewed: 2026-06-20

### Purpose

Flux Slider is a Flux Pro component for selecting a numeric value with a horizontal slider control. It also supports range selection with two thumbs, step marks, labeled ticks, keyboard big steps, and custom track/thumb classes.

### Basic usage

Single value slider:

```blade
<flux:slider wire:model="amount" />
```

Configured minimum, maximum, and step:

```blade
<flux:slider min="0" max="100" step="10" wire:model="amount" />
```

Displayed value:

```blade
<flux:field>
    <flux:label>
        {{ __('filters.corner_radius') }}

        <x-slot name="trailing">
            <span wire:text="amount" class="tabular-nums"></span>
        </x-slot>
    </flux:label>

    <flux:slider wire:model="amount" />
</flux:field>
```

Slider with number input:

```blade
<flux:field>
    <flux:label>{{ __('filters.corner_radius') }}</flux:label>

    <div class="flex items-center gap-4 -mt-2">
        <flux:slider wire:model="amount" />
        <flux:input wire:model="amount" type="number" size="sm" class="max-w-18" />
    </div>
</flux:field>
```

Big steps for shift-arrow keyboard interaction:

```blade
<flux:slider step="1" big-step="10" wire:model="amount" />
```

Step marks:

```blade
<flux:slider min="1" max="5" wire:model="rating">
    @foreach (range(1, 5) as $value)
        <flux:slider.tick :value="$value" />
    @endforeach
</flux:slider>
```

Numbered steps:

```blade
<flux:slider min="1" max="5" wire:model="rating">
    @foreach (range(1, 5) as $value)
        <flux:slider.tick :value="$value">{{ $value }}</flux:slider.tick>
    @endforeach
</flux:slider>
```

Custom step labels:

```blade
<flux:slider min="1" max="5" wire:model="comfortLevel">
    <flux:slider.tick value="1">{{ __('filters.levels.low') }}</flux:slider.tick>
    <flux:slider.tick value="3">{{ __('filters.levels.mid') }}</flux:slider.tick>
    <flux:slider.tick value="5">{{ __('filters.levels.high') }}</flux:slider.tick>
</flux:slider>
```

Range slider:

```blade
<flux:slider range />
```

Initial range value:

```blade
<flux:slider range value="20,80" />
```

Livewire-bound range:

```blade
<flux:slider range wire:model="priceRange" />
```

Displaying range values:

```blade
<flux:field>
    <flux:label>
        {{ __('filters.price_range') }}

        <x-slot name="trailing">
            {{ __('currency.symbol') }}<span wire:text="priceRange[0]" class="tabular-nums"></span>
            -
            {{ __('currency.symbol') }}<span wire:text="priceRange[1]" class="tabular-nums"></span>
        </x-slot>
    </flux:label>

    <flux:slider
        range
        wire:model="priceRange"
        min="0"
        max="990"
        step="10"
        min-steps-between="10"
        big-step="100"
    />
</flux:field>
```

Custom styles:

```blade
<flux:slider track:class="h-5" thumb:class="size-5" wire:model="amount" />
```

### Props and attributes

`flux:slider` props:

- `range`: enables range selection.
- `min`: minimum value of the slider.
- `max`: maximum value of the slider.
- `step`: step size of the slider.
- `big-step`: step size used when holding shift while pressing arrow keys.
- `min-steps-between`: minimum distance between thumbs in number of steps.
- `track:class`: CSS classes applied to the track.
- `thumb:class`: CSS classes applied to the thumb.

`flux:slider.tick` props:

- `value`: value at which the tick should be displayed.

### Slots and child components

- `flux:slider` default slot: optional `flux:slider.tick` children.
- `flux:slider.tick` default slot: tick label. If left empty, displays a horizontal line.
- Compose Slider with `flux:field`, `flux:label`, and `flux:input` when labels, displayed values, or exact numeric entry are needed.

### Livewire and Laravel usage

- Use `wire:model` as documented to bind slider values to Livewire properties.
- Range sliders bound with `wire:model` use an array value, such as `public array $priceRange = [20, 80];`.
- Display current values with `wire:text` on adjacent elements, as shown in the official docs.
- Validate and clamp numeric values server-side before using them in search, pricing, availability, or booking logic.
- For range sliders, validate both bounds and ensure the start value is less than or equal to the end value.
- Use a paired `flux:input type="number"` when exact numeric entry is important for accessibility or precision.
- Keep slider state compact in Livewire public properties: a single number or a small two-value array.
- Do not perform pricing, booking, or availability calculations in Blade based on slider values; pass compact state to services/actions.

### Styling, variants, and states

- This is a Flux Pro component.
- Use `min`, `max`, and `step` to constrain values.
- Use `big-step` for larger keyboard adjustments when shift is held.
- Use `range` for two-thumb range selection.
- Use `min-steps-between` to keep range thumbs apart by a minimum number of steps.
- Use `flux:slider.tick` to show tick marks, numbers, or custom labels.
- Use `track:class` and `thumb:class` for documented styling customization.
- No documented variants, colors, sizes, icons, disabled prop, or invalid prop are listed for `flux:slider` in the official reference.

### Project rules

- Prefer `flux:slider` over custom slider markup for numeric value controls.
- Use sliders only where approximate adjustment is acceptable; use `flux:input type="number"` or pair an input when exact values matter.
- Use sliders for compact mobile filters such as price range, distance radius, guest count ranges, or comfort/noise thresholds only when the values are bounded and meaningful.
- Keep labels and displayed value text translated.
- For mobile 320px layouts, keep sliders full-width and avoid dense tick labels that overlap.
- Use stable tick labels from translation keys for named levels.
- Use range sliders for filters only after server-side validation; do not trust client-side slider bounds for search or booking rules.
- Prefer `flux:progress` for read-only progress values; Slider is for user input.

### Mistakes to avoid

- Do not invent undocumented Slider props such as `variant`, `size`, `color`, `label`, `disabled`, `invalid`, or `tooltip`.
- Do not use Slider for unbounded values or huge ranges where precise typing/search is better.
- Do not hard-code visible labels, tick labels, or value explanations in Blade.
- Do not calculate money, availability, discounts, or booking totals inline from slider values.
- Do not render many dense ticks on mobile when they will overlap or create visual noise.
- Do not use Slider as a replacement for Select/Radio when the values are discrete named options rather than numeric ranges.

## Component: Switch

Source: https://fluxui.dev/components/switch
Reviewed: 2026-06-20

### Purpose

Flux Switch toggles a setting on or off. The official docs describe it as suitable for binary options like enabling or disabling features, and specifically recommend switches as auto-saving controls outside forms; use checkboxes otherwise.

### Basic usage

Inline field with validation:

```blade
<flux:field variant="inline">
    <flux:label>{{ __('settings.notifications.enable') }}</flux:label>
    <flux:switch wire:model.live="notifications" />
    <flux:error name="notifications" />
</flux:field>
```

Grouped switches:

```blade
<flux:fieldset>
    <flux:legend>{{ __('settings.email_notifications') }}</flux:legend>

    <div class="space-y-4">
        <flux:switch
            wire:model.live="communication"
            label="{{ __('settings.notifications.communication') }}"
            description="{{ __('settings.notifications.communication_description') }}"
        />

        <flux:separator variant="subtle" />

        <flux:switch
            wire:model.live="marketing"
            label="{{ __('settings.notifications.marketing') }}"
            description="{{ __('settings.notifications.marketing_description') }}"
        />
    </div>
</flux:fieldset>
```

Left-aligned compact switches:

```blade
<flux:fieldset>
    <flux:legend>{{ __('settings.email_notifications') }}</flux:legend>

    <div class="space-y-3">
        <flux:switch label="{{ __('settings.notifications.communication') }}" align="left" />
        <flux:switch label="{{ __('settings.notifications.marketing') }}" align="left" />
        <flux:switch label="{{ __('settings.notifications.security') }}" align="left" />
    </div>
</flux:fieldset>
```

Dark mode toggle pattern from the Flux dark-mode docs:

```blade
<flux:switch x-data x-model="$flux.dark" label="{{ __('settings.dark_mode') }}" />
```

### Props and attributes

`flux:switch` props:

- `wire:model`: binds the switch to a Livewire property.
- `label`: label text displayed above the switch. When provided, wraps the switch in a `flux:field` component with an adjacent `flux:label` component.
- `description`: help text displayed below the switch. When provided alongside `label`, appears between the label and switch within the `flux:field` wrapper.
- `align`: alignment of the switch relative to its label. Options: `right`/`start` (default), `left`/`end`.
- `disabled`: prevents user interaction with the switch.

`flux:switch` attributes:

- `data-flux-switch`: applied to the root element for styling and identification.
- `data-checked`: applied when the switch is in the on state.

### Slots and child components

- No dedicated `flux:switch` named slots or child components are documented.
- Compose Switch with `flux:field`, `flux:label`, and `flux:error` for explicit labels and validation.
- Use `label` and `description` props when the automatic `flux:field` wrapper is sufficient.
- Use `flux:fieldset` and `flux:legend` to group related switches.
- Use `flux:separator variant="subtle"` between stacked switches when visual separation is needed.

### Livewire and Laravel usage

- Use `wire:model` to bind a switch to a boolean Livewire property.
- Use `wire:model.live` for the documented auto-saving switch pattern when changing the setting should immediately persist or trigger Livewire behavior.
- Validate boolean switch values server-side before persisting settings, preferences, notifications, privacy flags, or host listing options.
- Keep switch state small in public Livewire properties; each switch should normally map to a boolean or boolean-like form field.
- Use `flux:error name="..."` when the switch can fail validation.
- For dark mode, the Flux docs show Alpine binding with `x-data x-model="$flux.dark"` on `flux:switch`.
- Do not hide authorization, verification, booking, payment, or availability decisions behind client-side switch state only; enforce those rules in actions, services, policies, or validation.

### Styling, variants, and states

- The documented alignment options are `right`/`start` and `left`/`end`.
- Use `disabled` to prevent user interaction.
- The on state is reflected by the documented `data-checked` attribute.
- `label` and `description` can create a field wrapper automatically.
- No documented `variant`, `size`, `color`, `icon`, `badge`, `invalid`, `loading`, or `required` props are listed for `flux:switch`.

### Project rules

- Prefer `flux:switch` over custom toggle markup for binary settings that should auto-save outside a form.
- Prefer `flux:checkbox` for boolean fields inside normal submitted forms, matching the Flux documentation's switch-vs-checkbox guidance.
- Use translated labels, legends, descriptions, and any surrounding explanatory text.
- Use switches for settings such as notification preferences, privacy flags, dark mode, host availability toggles, or listing feature enablement only when the consequence is clear and reversible.
- On 320px mobile layouts, keep grouped switches vertically stacked with clear labels and avoid dense two-column switch grids.
- For important booking, payment, visibility, or host-listing state changes, pair the switch with server-side validation and, when the action is consequential, a confirmation or explanatory state.
- Use `align="left"` for compact settings lists only when it remains readable on mobile.

### Mistakes to avoid

- Do not invent undocumented Switch props such as `variant`, `size`, `color`, `icon`, `badge`, `invalid`, `loading`, `required`, or `tooltip`.
- Do not use Switch for multi-option choices; use Select, Radio, Tabs, or another documented component as appropriate.
- Do not use Switch for submitted form booleans when a checkbox better matches the documented guidance.
- Do not hard-code visible labels or descriptions in Blade.
- Do not trust switch state alone for permissions, listing publication, booking acceptance, payment, or verification logic.
- Do not render large switch matrices on mobile; group related switches with fieldsets and progressive disclosure.

## Component: Table

Source: https://fluxui.dev/components/table
Reviewed: 2026-06-20

### Purpose

Flux Table displays structured data in a condensed format. Use it for tabular data that benefits from rows, columns, sorting, pagination, row actions, badges, avatars, sticky headers, or sticky columns.

### Basic usage

Full-featured table with pagination and sortable columns:

```blade
<flux:table :paginate="$this->orders">
    <flux:table.columns>
        <flux:table.column>{{ __('orders.customer') }}</flux:table.column>
        <flux:table.column sortable :sorted="$sortBy === 'date'" :direction="$sortDirection" wire:click="sort('date')">
            {{ __('orders.date') }}
        </flux:table.column>
        <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection" wire:click="sort('status')">
            {{ __('orders.status') }}
        </flux:table.column>
        <flux:table.column sortable :sorted="$sortBy === 'amount'" :direction="$sortDirection" wire:click="sort('amount')">
            {{ __('orders.amount') }}
        </flux:table.column>
    </flux:table.columns>

    <flux:table.rows>
        @foreach ($this->orders as $order)
            <flux:table.row :key="$order->id">
                <flux:table.cell class="flex items-center gap-3">
                    <flux:avatar size="xs" src="{{ $order->customer_avatar }}" />
                    {{ $order->customer }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">{{ $order->date }}</flux:table.cell>
                <flux:table.cell class="py-0">
                    <flux:badge size="sm" :color="$order->status_color">{{ $order->status }}</flux:badge>
                </flux:table.cell>
                <flux:table.cell variant="strong">{{ $order->amount }}</flux:table.cell>
                <flux:table.cell class="py-0">
                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"></flux:button>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table.rows>
</flux:table>
```

Livewire sorting and pagination pattern:

```php
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersTable extends Component
{
    use WithPagination;

    private const SORTABLE_COLUMNS = ['date', 'status', 'amount'];

    public string $sortBy = 'date';

    public string $sortDirection = 'desc';

    public function sort(string $column): void
    {
        if (! in_array($column, self::SORTABLE_COLUMNS, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortBy = $column;
        $this->sortDirection = 'asc';
    }

    #[Computed]
    public function orders(): LengthAwarePaginator
    {
        return Order::query()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(5);
    }
}
```

Simple table:

```blade
<flux:table>
    <flux:table.columns>
        <flux:table.column>{{ __('orders.customer') }}</flux:table.column>
        <flux:table.column>{{ __('orders.date') }}</flux:table.column>
        <flux:table.column>{{ __('orders.status') }}</flux:table.column>
        <flux:table.column>{{ __('orders.amount') }}</flux:table.column>
    </flux:table.columns>

    <flux:table.rows>
        <flux:table.row>
            <flux:table.cell>{{ $customerName }}</flux:table.cell>
            <flux:table.cell>{{ $date }}</flux:table.cell>
            <flux:table.cell class="py-0">
                <flux:badge color="green" size="sm">{{ __('orders.statuses.paid') }}</flux:badge>
            </flux:table.cell>
            <flux:table.cell variant="strong">{{ $amount }}</flux:table.cell>
        </flux:table.row>
    </flux:table.rows>
</flux:table>
```

Pagination:

```blade
<flux:table :paginate="$orders">
    ...
</flux:table>
```

Pagination scroll target:

```blade
<flux:table :paginate="$orders" pagination:scroll-to />

<flux:table :paginate="$orders" pagination:scroll-to="#orders" />
```

Sortable column markers:

```blade
<flux:table>
    <flux:table.columns>
        <flux:table.column>{{ __('orders.customer') }}</flux:table.column>
        <flux:table.column sortable sorted direction="desc">{{ __('orders.date') }}</flux:table.column>
        <flux:table.column sortable>{{ __('orders.amount') }}</flux:table.column>
    </flux:table.columns>
</flux:table>
```

Sticky header:

```blade
<flux:table container:class="max-h-80">
    <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
        ...
    </flux:table.columns>
</flux:table>
```

Sticky columns:

```blade
<flux:table container:class="max-h-80">
    <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
        <flux:table.column sticky class="bg-white dark:bg-zinc-900">{{ __('orders.id') }}</flux:table.column>
        ...
    </flux:table.columns>

    <flux:table.rows>
        @foreach ($this->orders as $order)
            <flux:table.row :key="$order->id">
                <flux:table.cell sticky class="bg-white dark:bg-zinc-900">{{ $order->id }}</flux:table.cell>
                ...
            </flux:table.row>
        @endforeach
    </flux:table.rows>
</flux:table>
```

### Props and attributes

`flux:table` props:

- `paginate`: a Laravel paginator instance to enable pagination.
- `pagination:scroll-to`: scrolls to an element when a pagination button is clicked. Pass a CSS selector to target a specific element. Default target is `body`.
- `container:class`: additional CSS classes applied to the container, useful for height constraints like `max-h-80`.

`flux:table` attributes:

- `data-flux-table`: applied to the root element for styling and identification.

`flux:table.columns` props:

- `sticky`: makes the header row sticky when scrolling.

`flux:table.columns` slots:

- `default`: the table columns.

`flux:table.column` props:

- `align`: alignment of the column content. Options: `start`, `center`, `end`.
- `sortable`: enables sorting functionality for the column.
- `sorted`: indicates this column is currently being sorted.
- `direction`: sort direction when the column is sorted. Options: `asc`, `desc`.
- `sticky`: makes the column sticky when scrolling.

`flux:table.rows` slots:

- `default`: the table rows.

`flux:table.row` props:

- `key`: alias for `wire:key`, the unique identifier for the row.
- `sticky`: makes the row sticky when scrolling.

`flux:table.row` slots:

- `default`: the table cells for this row.

`flux:table.cell` props:

- `align`: alignment of the cell content. Options: `start`, `center`, `end`.
- `variant`: visual style of the cell. Options: `default`, `strong`.
- `sticky`: makes the cell sticky when scrolling.

### Slots and child components

- `flux:table` wraps the whole table and accepts `flux:table.columns` and `flux:table.rows`.
- `flux:table.columns` contains `flux:table.column` children.
- `flux:table.rows` contains `flux:table.row` children.
- `flux:table.row` contains `flux:table.cell` children.
- The docs compose table cells with `flux:avatar`, `flux:badge`, and `flux:button`.
- Related components documented with Table are Avatar, Badge, and Dropdown.

### Livewire and Laravel usage

- Pass a Laravel paginator instance to `:paginate` when the table should paginate.
- In Livewire components, use `WithPagination` with a computed property that returns the paginated query.
- Use `wire:click` on sortable columns to call a Livewire sorting action.
- Use `sortable`, `sorted`, and `direction` together to show sorting state.
- Use `key` or `:key` on dynamic rows so Livewire has a stable row identifier.
- Keep sorting state compact, such as a `sortBy` string and `sortDirection` string.
- Apply sorting in the Eloquent query before pagination.
- Validate or whitelist sortable column names in real project code before passing them to `orderBy`.
- Use selected columns, eager loading, and paginator-backed queries; do not call `Model::all()` for table data.
- If search is needed, implement it in Livewire/Eloquent outside the table component; the official Table reference does not document a `search`, `searchable`, or `filter` prop for `flux:table`.

### Styling, variants, and states

- Use `container:class` to set height constraints such as `max-h-80`.
- Use sticky headers with `flux:table.columns sticky`.
- Use sticky columns with `sticky` on matching `flux:table.column` and `flux:table.cell` components.
- When using sticky headers, columns, or cells, set a background class such as `bg-white dark:bg-zinc-900` to prevent content overlap.
- Use `align="start"`, `align="center"`, or `align="end"` on columns and cells.
- Use `flux:table.cell variant="strong"` for emphasized cell content.
- Use `pagination:scroll-to` to control scroll behavior after pagination.
- No documented `striped`, `bordered`, `compact`, `size`, `color`, `search`, `searchable`, `filter`, `selectable`, or `empty` props are listed for `flux:table`.

### Project rules

- Prefer `flux:table` over custom table markup for structured tabular data.
- On this mobile-first marketplace, use tables only when row/column comparison is genuinely useful; for public listing search results, prefer mobile cards unless a table is clearly better.
- Keep table columns few and high-value on 320px screens; use progressive disclosure or row actions for secondary details.
- Use translated column labels, badge labels, action labels, empty states, and pagination-adjacent text.
- Use `@forelse` or a prepared empty state around dynamic table data so empty lists are friendly and translated.
- Put row actions behind documented Flux buttons/dropdowns rather than raw icon-only markup without labels or tooltips.
- Use paginator-backed Eloquent queries for table rows and avoid loading large collections into Livewire public properties.
- Sticky headers and sticky columns must include explicit light/dark backgrounds.
- Do not put business logic, relationship queries, aggregates, money calculations, or availability checks in table cells; prepare display DTOs or eager-loaded data before rendering.
- For host/guest management views, make sure row actions are authorized server-side and not only hidden in the table.

### Mistakes to avoid

- Do not invent undocumented table props such as `striped`, `bordered`, `compact`, `size`, `color`, `search`, `searchable`, `filter`, `selectable`, `empty`, or `loading`.
- Do not rely on `sortable` alone to sort data; it marks UI state, while Livewire/Eloquent must handle the sort action and query.
- Do not pass untrusted request or query-string values directly into `orderBy`; whitelist sortable columns.
- Do not use `Model::all()` or load unbounded rows for tables.
- Do not render wide, dense desktop-style tables as the primary mobile experience.
- Do not use sticky headers, sticky columns, or sticky cells without backgrounds, because the docs warn that overlap can occur.
- Do not hard-code visible table headers, statuses, row action labels, or empty-state text in Blade.
- Do not calculate totals, statuses, or permissions inline inside `flux:table.cell`.

## Component: Tabs

Source: https://fluxui.dev/components/tabs
Reviewed: 2026-06-20

### Purpose

Flux Tabs is a Flux Pro component for organizing related content into separate panels inside a single container. Use it to switch between sections without leaving the page. For full-page navigation, the official docs direct users to the Navbar component instead.

### Basic usage

Tabs with panels and a Livewire-bound active tab:

```blade
<flux:tab.group>
    <flux:tabs wire:model="tab">
        <flux:tab name="profile">{{ __('settings.tabs.profile') }}</flux:tab>
        <flux:tab name="account">{{ __('settings.tabs.account') }}</flux:tab>
        <flux:tab name="billing">{{ __('settings.tabs.billing') }}</flux:tab>
    </flux:tabs>

    <flux:tab.panel name="profile">
        ...
    </flux:tab.panel>

    <flux:tab.panel name="account">
        ...
    </flux:tab.panel>

    <flux:tab.panel name="billing">
        ...
    </flux:tab.panel>
</flux:tab.group>
```

Tabs with icons:

```blade
<flux:tab.group>
    <flux:tabs>
        <flux:tab name="profile" icon="user">{{ __('settings.tabs.profile') }}</flux:tab>
        <flux:tab name="account" icon="cog-6-tooth">{{ __('settings.tabs.account') }}</flux:tab>
        <flux:tab name="billing" icon="banknotes">{{ __('settings.tabs.billing') }}</flux:tab>
    </flux:tabs>

    <flux:tab.panel name="profile">...</flux:tab.panel>
    <flux:tab.panel name="account">...</flux:tab.panel>
    <flux:tab.panel name="billing">...</flux:tab.panel>
</flux:tab.group>
```

Padded edges:

```blade
<flux:tabs class="px-4">
    <flux:tab name="profile">{{ __('settings.tabs.profile') }}</flux:tab>
    <flux:tab name="account">{{ __('settings.tabs.account') }}</flux:tab>
    <flux:tab name="billing">{{ __('settings.tabs.billing') }}</flux:tab>
</flux:tabs>
```

Scrollable tabs for mobile overflow:

```blade
<flux:tab.group>
    <flux:tabs scrollable scrollable:fade>
        <flux:tab name="profile">{{ __('settings.tabs.profile') }}</flux:tab>
        <flux:tab name="account">{{ __('settings.tabs.account') }}</flux:tab>
        <flux:tab name="billing">{{ __('settings.tabs.billing') }}</flux:tab>
        <flux:tab name="security">{{ __('settings.tabs.security') }}</flux:tab>
        <flux:tab name="notifications">{{ __('settings.tabs.notifications') }}</flux:tab>
    </flux:tabs>

    <flux:tab.panel name="profile">...</flux:tab.panel>
    <flux:tab.panel name="account">...</flux:tab.panel>
    <flux:tab.panel name="billing">...</flux:tab.panel>
    <flux:tab.panel name="security">...</flux:tab.panel>
    <flux:tab.panel name="notifications">...</flux:tab.panel>
</flux:tab.group>
```

Segmented tabs:

```blade
<flux:tabs variant="segmented">
    <flux:tab>{{ __('views.list') }}</flux:tab>
    <flux:tab>{{ __('views.board') }}</flux:tab>
    <flux:tab>{{ __('views.timeline') }}</flux:tab>
</flux:tabs>
```

Small segmented tabs:

```blade
<flux:tabs variant="segmented" size="sm">
    <flux:tab>{{ __('common.demo') }}</flux:tab>
    <flux:tab>{{ __('common.code') }}</flux:tab>
</flux:tabs>
```

Segmented tabs with icons:

```blade
<flux:tabs variant="segmented">
    <flux:tab icon="list-bullet">{{ __('views.list') }}</flux:tab>
    <flux:tab icon="squares-2x2">{{ __('views.board') }}</flux:tab>
    <flux:tab icon="calendar-days">{{ __('views.timeline') }}</flux:tab>
</flux:tabs>
```

Pill tabs:

```blade
<flux:tabs variant="pills">
    <flux:tab>{{ __('views.list') }}</flux:tab>
    <flux:tab>{{ __('views.board') }}</flux:tab>
    <flux:tab>{{ __('views.timeline') }}</flux:tab>
</flux:tabs>
```

Dynamic tabs:

```blade
<flux:tab.group>
    <flux:tabs>
        @forelse ($tabs as $id => $labelKey)
            <flux:tab :name="$id">{{ __($labelKey) }}</flux:tab>
        @empty
            <flux:tab name="empty" disabled>{{ __('common.none') }}</flux:tab>
        @endforelse

        <flux:tab icon="plus" wire:click="addTab" action>{{ __('tabs.add') }}</flux:tab>
    </flux:tabs>

    @forelse ($tabs as $id => $labelKey)
        <flux:tab.panel :name="$id">
            ...
        </flux:tab.panel>
    @empty
        <flux:tab.panel name="empty">
            {{ __('tabs.empty') }}
        </flux:tab.panel>
    @endforelse
</flux:tab.group>
```

Dynamic tab state:

```php
public array $tabs = [
    'tab-1' => 'tabs.labels.first',
    'tab-2' => 'tabs.labels.second',
];

public function addTab(): void
{
    $id = 'tab-'.str()->random();

    $this->tabs[$id] = 'tabs.labels.custom';
}
```

### Props and attributes

`flux:tab.group` slots:

- `default`: the tabs and panels components.

`flux:tabs` props:

- `wire:model`: binds the active tab to a Livewire property.
- `variant`: visual style of the tabs. Options: `default`, `segmented`, `pills`.
- `size`: size of the tabs. Options: `base` (default), `sm`.
- `scrollable`: enables horizontal scrolling.
- `scrollable:scrollbar`: controls scrollbar visibility when tabs are scrollable. Option: `hide`.
- `scrollable:fade`: adds a fade effect to the trailing edge when tabs are scrollable.

`flux:tabs` slots:

- `default`: the individual tab components.

`flux:tabs` attributes:

- `data-flux-tabs`: applied to the root element for styling and identification.

`flux:tab` props:

- `name`: unique identifier for the tab, used to match with its panel.
- `icon`: name of the icon to display at the start of the tab.
- `icon:trailing`: name of the icon to display at the end of the tab.
- `icon:variant`: variant of the icon. Options: `outline`, `solid`, `mini`, `micro`.
- `selected`: when true, the tab is selected by default.
- `action`: converts the tab to an action button, used for "Add tab" functionality.
- `accent`: when true, applies accent color styling to the tab.
- `size`: size of the tab. Only applies when `variant="segmented"`. Options: `base` (default), `sm`.
- `disabled`: disables the tab.

`flux:tab` slots:

- `default`: the tab label content.

`flux:tab` attributes:

- `data-flux-tab`: applied to the tab element for styling and identification.
- `data-selected`: applied when the tab is selected or active.

`flux:tab.panel` props:

- `name`: unique identifier matching the associated tab.
- `selected`: when true, the panel is selected by default.

`flux:tab.panel` slots:

- `default`: the panel content displayed when the associated tab is selected.

`flux:tab.panel` attributes:

- `data-flux-tab-panel`: applied to the panel element for styling and identification.

### Slots and child components

- `flux:tab.group` contains `flux:tabs` and associated `flux:tab.panel` components.
- `flux:tabs` contains individual `flux:tab` components.
- `flux:tab.panel` content is shown when the associated tab is selected.
- Tab `name` values and panel `name` values must match.
- The docs show `flux:tab action` for add-tab behavior.
- Related components documented with Tabs are Navbar and Radio.

### Livewire and Laravel usage

- Use `wire:model` on `flux:tabs` to bind the active tab to a compact Livewire property.
- Keep tab names stable string identifiers such as `profile`, `account`, or generated IDs.
- For dynamic tabs, generate matching `flux:tab :name="$id"` and `flux:tab.panel :name="$id"` pairs.
- Use Livewire actions such as `wire:click="addTab"` on `flux:tab action` for dynamic tab creation.
- Keep dynamic tab arrays small in public properties; do not store large panel data in the tab list itself.
- Use translation keys for every visible tab label, add-tab label, and fallback label.
- If tab state should be shareable or survive reloads, sync the bound Livewire property to URL query state in the Livewire class rather than inventing a Tabs prop.
- For full-page navigation or app shell navigation, use Navbar rather than Tabs.

### Styling, variants, and states

- This is a Flux Pro component.
- `flux:tabs` variants are `default`, `segmented`, and `pills`.
- `flux:tabs` sizes are `base` and `sm`.
- `flux:tab` size applies only when `variant="segmented"`.
- Use `scrollable` when tabs exceed the viewport, especially on mobile.
- Use `scrollable:fade` as a visual cue that more tabs are available beyond the visible area.
- Use `scrollable:scrollbar="hide"` only when hiding the scrollbar is acceptable; the docs note this also hides it on desktop where users may rely on it.
- Use Tailwind classes such as `px-4` on `flux:tabs` and/or `flux:tab.panel` for padded edges.
- Use `selected` on a tab or panel for default selected state when not using Livewire-bound active state.
- Use `disabled` for tabs that cannot be selected.
- Use `accent` for documented accent color styling on tabs.
- No documented color, gap, orientation, vertical, compact, loading, badge, count, href, or route props are listed for Tabs.

### Accessibility requirements or recommendations

- The official Tabs page does not document explicit ARIA props or keyboard requirements.
- Keep tab labels short, visible, translated, and descriptive.
- Do not use icon-only tabs unless surrounding context makes the destination clear; the docs show icons paired with labels.
- Prefer `scrollable` and `scrollable:fade` over clipping tab labels on mobile.

### Project rules

- Prefer `flux:tabs` over custom tab markup when switching between related panels inside one screen.
- Do not use Tabs for full-page navigation, primary app navigation, or locale/role shell navigation; use documented Navbar/navigation components for that.
- Use `scrollable` for any tab list that may overflow 320px mobile screens.
- Keep the number of tabs small and the labels short on mobile.
- Use `variant="segmented"` for compact in-container view toggles such as list/board/timeline, and `variant="pills"` when separated pill-like tabs better fit the UI.
- Ensure every `flux:tab name="..."` has a matching `flux:tab.panel name="..."` when panels are used.
- Keep inactive panels lightweight; do not render huge hidden maps, galleries, tables, or result lists just because they are in tabs.
- For booking, pricing, availability, verification, or publication workflows, tab visibility must not replace server-side validation or authorization.

### Mistakes to avoid

- Do not invent undocumented Tabs props such as `color`, `gap`, `orientation`, `vertical`, `compact`, `loading`, `badge`, `count`, `href`, `route`, or `lazy`.
- Do not mismatch `flux:tab` names and `flux:tab.panel` names.
- Do not use Tabs for full-page navigation; the docs point to Navbar for that.
- Do not hide scrollbars on desktop unless the loss of horizontal-scroll affordance is acceptable.
- Do not use icon-only tabs without accessible visible labels in this project.
- Do not store large arrays or heavy panel data in public Livewire tab state.
- Do not hard-code visible tab labels, action labels, or empty-state text in Blade.

## Component: Text

Source: https://fluxui.dev/components/text
Reviewed: 2026-06-20

### Purpose

Flux Text provides consistent typographical components for body copy and links. Use `flux:text` for general content text and `flux:link` for clickable text that navigates to pages or resources, or renders as a button for action-style text.

### Basic usage

Standard text with a heading:

```blade
<flux:heading>{{ __('content.text_component') }}</flux:heading>
<flux:text class="mt-2">{{ __('content.body_copy') }}</flux:text>
```

Text sizing examples from the docs use Tailwind classes:

```blade
<flux:text class="text-base">{{ __('content.base_text') }}</flux:text>
<flux:text>{{ __('content.default_text') }}</flux:text>
<flux:text class="text-xs">{{ __('content.smaller_text') }}</flux:text>
```

Text color and importance:

```blade
<flux:text variant="strong">{{ __('content.strong_text') }}</flux:text>
<flux:text>{{ __('content.default_text') }}</flux:text>
<flux:text variant="subtle">{{ __('content.subtle_text') }}</flux:text>
<flux:text color="blue">{{ __('content.colored_text') }}</flux:text>
```

Inline link inside text:

```blade
<flux:text>
    {{ __('help.visit_our') }}
    <flux:link href="{{ route('help.index') }}">{{ __('help.documentation') }}</flux:link>
    {{ __('help.for_more_information') }}
</flux:text>
```

Link variants:

```blade
<flux:link href="{{ route('home') }}">{{ __('links.default') }}</flux:link>
<flux:link href="{{ route('home') }}" variant="ghost">{{ __('links.ghost') }}</flux:link>
<flux:link href="{{ route('home') }}" variant="subtle">{{ __('links.subtle') }}</flux:link>
```

Link rendered as a button:

```blade
<flux:link as="button" wire:click="createAccount">
    {{ __('auth.create_account') }}
</flux:link>
```

### Props and attributes

`flux:text` props:

- `size`: size of the text. Options: `sm`, `default`, `lg`, `xl`. Default: `default`.
- `variant`: text variant. Options: `strong`, `subtle`. Default: `default`.
- `color`: color of the text. Options: `default`, `red`, `orange`, `yellow`, `lime`, `green`, `emerald`, `teal`, `cyan`, `sky`, `blue`, `indigo`, `violet`, `purple`, `fuchsia`, `pink`, `rose`. Default: `default`.
- `inline`: when true, renders the text element as a `span` instead of a `p`.

`flux:link` props:

- `href`: URL that the link points to. Required.
- `variant`: link style variant. Options: `default`, `ghost`, `subtle`. Default: `default`.
- `external`: when true, opens the link in a new tab.
- `as`: HTML tag to render the link as. Options: `a` (default), `button`.

### Slots and child components

- No named slots are documented for `flux:text` or `flux:link`.
- The docs show text content in the default component body.
- `flux:link` may be nested inside `flux:text`.
- Related components documented with Text are Heading and Card.

### Livewire and Laravel usage

- Use `flux:text` for paragraph-style UI copy instead of ad hoc `p` or `div` markup when a consistent Flux typographic primitive is suitable.
- Use `flux:link href="{{ route('...') }}"` for internal Laravel routes.
- Use `flux:link as="button"` for action-style text buttons; the docs show it with `wire:click`.
- Use translated strings for all visible text and link labels.
- Keep dynamic values prepared in Livewire classes, presenters, DTOs, or services; do not calculate booking, pricing, or availability explanations inline inside `flux:text`.
- Use `inline` when the text must render as a `span` within another sentence or inline layout.

### Styling, variants, and states

- The docs show standard Tailwind classes such as `text-base` and `text-xs` for text sizing examples.
- The reference also documents `flux:text size` options: `sm`, `default`, `lg`, `xl`.
- `flux:text` variants are `strong` and `subtle`; default is normal text.
- `flux:text` colors are the documented palette values listed above.
- `flux:link` variants are `default`, `ghost`, and `subtle`.
- `flux:link external` opens the link in a new tab.
- `flux:link as="button"` renders a button instead of an anchor.
- No documented icon, badge, underline, weight, align, truncate, target, rel, or disabled props are listed for `flux:text` or `flux:link`.

### Accessibility requirements or recommendations

- The official Text page does not document explicit ARIA props.
- Use descriptive translated link labels; avoid vague labels when the link destination is not obvious from surrounding text.
- Use `flux:link as="button"` for actions instead of links with dummy `href` values.
- For external links, consider context and copy so users understand they are leaving or opening another page.

### Project rules

- Prefer `flux:text` for consistent secondary copy, helper copy, empty-state body copy, and card descriptions.
- Prefer `flux:heading` for headings and hierarchy; do not fake headings with large `flux:text`.
- Prefer `flux:link` over raw anchors for text links when a documented Flux link fits.
- Keep text short and readable on 320px screens.
- Use `variant="subtle"` for supporting copy and `variant="strong"` only where the text needs emphasis.
- Use semantic color sparingly for status or context; do not create one-off color systems with `flux:text color`.
- Pair colored text with clear wording; color alone must not carry status meaning.
- Use `route()` or project URL helpers for internal links rather than hard-coded URLs.

### Mistakes to avoid

- Do not invent undocumented Text or Link props such as `icon`, `badge`, `underline`, `weight`, `align`, `truncate`, `target`, `rel`, `disabled`, or `loading`.
- Do not use `flux:text` for input fields; use documented Input or Textarea components.
- Do not hard-code visible copy or link labels in Blade.
- Do not use `flux:link href="#"` in production UI unless it is only documentation placeholder code.
- Do not use links for actions that should be buttons; use `flux:link as="button"` or a documented button component.
- Do not use color alone to communicate booking, payment, verification, availability, or error status.

## Component: Textarea

Source: https://fluxui.dev/components/textarea
Reviewed: 2026-06-20

### Purpose

Flux Textarea captures multi-line text input from users. The official docs describe it as ideal for comments, descriptions, and feedback.

### Basic usage

Basic textarea:

```blade
<flux:textarea />
```

Textarea with translated label and placeholder:

```blade
<flux:textarea
    label="{{ __('forms.order_notes') }}"
    placeholder="{{ __('forms.order_notes_placeholder') }}"
/>
```

Fixed row height:

```blade
<flux:textarea rows="2" label="{{ __('forms.note') }}" />
```

Auto-sizing textarea:

```blade
<flux:textarea rows="auto" />
```

Resize behavior:

```blade
<flux:textarea resize="vertical" />
<flux:textarea resize="none" />
<flux:textarea resize="horizontal" />
<flux:textarea resize="both" />
```

Livewire-bound textarea with validation styling:

```blade
<flux:textarea
    wire:model.blur="message"
    label="{{ __('messages.message') }}"
    description="{{ __('messages.message_help') }}"
    badge="{{ __('forms.optional') }}"
    :invalid="$errors->has('message')"
/>
```

Trailing description:

```blade
<flux:textarea
    wire:model.blur="description"
    label="{{ __('listings.description') }}"
    description:trailing="{{ __('listings.description_hint') }}"
/>
```

### Props and attributes

`flux:textarea` props:

- `wire:model`: binds the textarea to a Livewire property.
- `placeholder`: placeholder text displayed when the textarea is empty.
- `label`: label text displayed above the textarea. When provided, wraps the textarea in a `flux:field` component with an adjacent `flux:label` component.
- `description`: help text displayed below the textarea. When provided alongside `label`, appears between the label and textarea within the `flux:field` wrapper.
- `description:trailing`: displays the description below the textarea instead of above it.
- `badge`: badge text displayed at the end of the `flux:label` component when the `label` prop is provided.
- `rows`: number of visible text lines. Use `auto` for automatic height adjustment. Default: `4`.
- `resize`: controls how the textarea can be resized. Options: `vertical` (default), `horizontal`, `both`, `none`.
- `invalid`: when true, applies error styling to the textarea.

`flux:textarea` attributes:

- `data-flux-textarea`: applied to the textarea element for styling and identification.

### Slots and child components

- No named slots or child components are documented for `flux:textarea`.
- Use the documented `label`, `description`, `description:trailing`, and `badge` props when the automatic `flux:field` wrapper is enough.
- Related components documented with Textarea are Field and Input.

### Livewire and Laravel usage

- Use `wire:model` as documented to bind textarea values to Livewire properties.
- In this project, prefer `wire:model.blur` for normal textarea fields so long text does not update on every keystroke.
- Do not use live typing updates for long textareas.
- Validate textarea values server-side before saving comments, descriptions, feedback, booking messages, host responses, complaint text, or profile copy.
- Use translated labels, placeholders, descriptions, badges, validation messages, and helper text.
- Use `invalid` when validation state needs to be reflected visually.
- Keep textarea public properties as strings; do not store large rich-text structures or arrays in textarea state.
- Use `flux:editor` only when documented rich text is truly required; use `flux:textarea` for plain multi-line text.

### Styling, variants, and states

- Default visible rows: `4`.
- Use numeric `rows` values such as `2` for fixed visible height.
- Use `rows="auto"` for automatic height adjustment.
- The docs note that auto-sizing uses CSS field-sizing and is not available in all browsers; check browser support before relying on it for critical UX.
- Resize options are `vertical`, `horizontal`, `both`, and `none`; default is `vertical`.
- Use `resize="none"` when user resizing would break a constrained mobile layout.
- Use `description:trailing` when helper text should appear below the textarea.
- No documented `variant`, `size`, `color`, `icon`, `clearable`, `maxlength`, `counter`, `autosize`, or `disabled` props are listed for `flux:textarea`.

### Accessibility requirements or recommendations

- Use a visible `label` for each textarea unless the surrounding Flux Field pattern already provides one.
- Use `description` or `description:trailing` for concise guidance when the expected text is not obvious.
- Keep placeholders as hints only; do not use placeholders as the only label.
- Avoid `resize="none"` when users may need extra space to review longer text, unless the layout requires it.

### Project rules

- Prefer `flux:textarea` over custom `<textarea>` markup for plain multi-line user input.
- Use textareas for comments, descriptions, feedback, booking messages, host responses, complaint details, and profile/about text.
- Keep mobile forms short; use textarea fields only where free-form text is genuinely needed.
- For 320px layouts, avoid fixed large row counts that push primary actions far below the fold.
- Use `rows="auto"` carefully because browser support is not universal.
- Prefer `resize="vertical"` or `resize="none"` on mobile; avoid `horizontal` and `both` unless a real workflow needs them.
- Do not calculate prices, availability, rules, or booking decisions from textarea content in Blade; pass submitted text to validated actions/services.

### Mistakes to avoid

- Do not invent undocumented Textarea props such as `variant`, `size`, `color`, `icon`, `clearable`, `maxlength`, `counter`, `autosize`, `disabled`, or `loading`.
- Do not use live debounce or live typing updates for long textareas.
- Do not hard-code visible labels, placeholders, descriptions, badges, or validation text in Blade.
- Do not use Textarea for single-line input; use documented Input components.
- Do not use Textarea for rich text formatting; use Editor only when the documented rich-text component is needed.
- Do not rely on `rows="auto"` without considering browser support and mobile behavior.

## Component: Time Picker

Source: https://fluxui.dev/components/time-picker
Reviewed: 2026-06-20

### Purpose

Flux Time Picker is a Flux Pro component for selecting specific times for scheduling, appointments, time-based filtering, and precise scheduling workflows.

### Basic usage

Basic time picker:

```blade
<flux:time-picker />
```

Initial selected time with an `H:i` value:

```blade
<flux:time-picker value="11:30" />
```

Livewire-bound time picker:

```blade
<flux:time-picker wire:model="time" />
```

Input trigger for more precise time selection control:

```blade
<flux:time-picker type="input" />
```

Input trigger without dropdown:

```blade
<flux:time-picker type="input" :dropdown="false" />
```

Multiple time selection:

```blade
<flux:time-picker multiple />
```

Explicit time format:

```blade
<flux:time-picker time-format="12-hour" />
<flux:time-picker time-format="24-hour" />
```

Custom interval:

```blade
<flux:time-picker interval="60" />
```

Minimum and maximum selectable times:

```blade
<flux:time-picker min="09:00" max="17:00" />
<flux:time-picker min="now" />
<flux:time-picker max="now" />
```

Unavailable times and ranges:

```blade
<flux:time-picker unavailable="03:00,04:00,05:30-07:29" />
```

Open the picker to a specific time:

```blade
<flux:time-picker open-to="10:00" />
```

Override locale:

```blade
<flux:time-picker locale="ja-JP" />
```

Project booking-time example:

```blade
<flux:time-picker
    wire:model.change="checkInTime"
    label="{{ __('booking.check_in_time') }}"
    description="{{ __('booking.check_in_time_help') }}"
    placeholder="{{ __('booking.select_time') }}"
    interval="30"
    min="09:00"
    max="22:00"
    clearable
/>
```

### Props and attributes

`flux:time-picker` props:

- `wire:model`: binds the time picker to a Livewire property.
- `value`: selected time value. Single mode uses `H:i`; multiple mode uses comma-separated `H:i` values.
- `type`: picker trigger type. Options: `input`, `button`. Default: `button`.
- `dropdown`: documented in the `type="input" :dropdown="false"` pattern to remove the dropdown from the input trigger.
- `multiple`: allows multiple time selections. Default: `false`.
- `time-format`: display format. Options: `auto`, `12-hour`, `24-hour`. Default: `auto`.
- `interval`: minutes between displayed time options. Default: `30`.
- `min`: earliest selectable time. Accepts a time string or `now`.
- `max`: latest selectable time. Accepts a time string or `now`.
- `unavailable`: unavailable time value, comma-separated time values, or documented range syntax such as `05:30-07:29`.
- `open-to`: time the picker opens to. Default is the selected time or current time.
- `label`: label text displayed above the picker. When provided, Flux wraps the picker in `flux:field` with an adjacent `flux:label`.
- `description`: help text displayed above the picker when used with `label`.
- `description:trailing`: displays the description below the picker instead of above it.
- `badge`: badge text displayed at the end of the generated `flux:label` when `label` is provided.
- `placeholder`: placeholder text displayed when no time is selected.
- `size`: picker size. Options: `sm`, `xs`.
- `clearable`: displays a clear button when a time is selected.
- `disabled`: prevents user interaction with the picker.
- `invalid`: applies error styling to the picker.
- `locale`: locale for the picker. Documented examples include `fr`, `en-US`, and `ja-JP`.

`flux:time-picker` attributes:

- `data-flux-time-picker`: applied to the root element for styling and identification.

### Slots and child components

- No named slots or child components are documented for `flux:time-picker`.
- Use the documented `label`, `description`, `description:trailing`, and `badge` props when the automatic `flux:field` wrapper is enough.
- Related components documented with Time Picker are Date Picker, Calendar, and Input.

### Livewire and Laravel usage

- Use `wire:model` as documented to bind the selected time to a Livewire property.
- In this project, prefer `wire:model.change` for check-in time, check-out time, planned arrival time, host rule times, and other time-choice fields.
- Store and validate normal single time values as `H:i` strings in Livewire state, form objects, DTOs, or actions.
- For `multiple`, follow the documented comma-separated `H:i,H:i` value format unless a Livewire class explicitly normalizes the submitted value before validation.
- Use translated labels, descriptions, badges, placeholders, validation errors, and helper text.
- Use `type="input"` when typed time entry or precise input control is important.
- Use separate time pickers for check-in and check-out time fields; do not treat `multiple` as a range picker.
- Pass selected times into booking, availability, pricing, reminder, or host-rule services/actions. Do not calculate booking rules in Blade.
- Treat `min`, `max`, and `unavailable` as UI constraints only; revalidate all time availability and booking rules server-side before quoting, requesting, booking, confirming, or extending a stay.

### Styling, variants, and states

- Time Picker is documented as a Flux Pro component.
- Trigger types are `button` and `input`; `button` is the default.
- The `type="input" :dropdown="false"` pattern removes the dropdown from the input trigger.
- Default time format is browser-locale driven via `auto`; use `12-hour` or `24-hour` when the interface requires an explicit format.
- Default interval is `30` minutes; use `interval` to control the granularity of displayed time options.
- Use `min`, `max`, and `unavailable` to visually restrict choices.
- Use `min="now"` or `max="now"` for current-time boundaries where appropriate.
- Use `open-to` to control the initial scroll/open position when no selected time gives the desired starting point.
- Use `locale` to override browser locale with a valid locale string.
- Use `size="sm"` or `size="xs"` only where a smaller control is documented and appropriate.
- Use `clearable`, `disabled`, and `invalid` for clearable, disabled, and validation states.
- No documented `variant`, `color`, `icon`, `loading`, `seconds`, `timezone`, `step`, `minute-step`, `range`, or `required` props are listed for `flux:time-picker`.

### Accessibility requirements or recommendations

- The official Time Picker page does not document explicit ARIA props.
- Provide a visible translated `label` unless an enclosing Flux Field pattern already supplies accessible labeling.
- Do not rely on placeholder text as the only label.
- Pair disabled or unavailable time behavior with translated validation feedback when a submitted time is rejected server-side.
- Keep intervals practical on mobile so the option list is easy to scan and tap on 320px screens.

### Project rules

- Prefer `flux:time-picker` over custom time inputs for booking times, planned arrival times, host check-in/check-out rules, turnover windows, reminders, appointment-like filters, and scheduling UI.
- Use `wire:model.change` for time choices unless there is a documented need for another Livewire binding mode.
- Keep Livewire public properties compact: store selected time strings or compact DTO fields, not large time-slot arrays.
- Use `interval` values that match host/business rules and avoid overly dense mobile lists.
- Use `min`, `max`, and `unavailable` to guide the user, then enforce the real booking, turnover, cleaning, and availability rules in services/actions with tests.
- Use app locale intentionally when overriding `locale`; otherwise allow the browser locale behavior documented by Flux.
- Use `24-hour` format when the product flow needs predictable time display across English/Russian booking rules, unless a specific locale experience calls for `auto` or `12-hour`.
- Keep all visible copy translated through Laravel language files.

### Mistakes to avoid

- Do not invent undocumented Time Picker props such as `variant`, `color`, `icon`, `loading`, `seconds`, `timezone`, `step`, `minute-step`, `range`, or `required`.
- Do not use `multiple` as a start/end range picker; use separate documented pickers for separate time fields.
- Do not rely on `min`, `max`, or `unavailable` as the only booking or availability protection.
- Do not hard-code labels, placeholders, descriptions, badges, or validation messages in Blade.
- Do not calculate stay rules, turnover eligibility, pricing, reminders, or availability inline in Blade.
- Do not create custom Tailwind-only time picker markup when the documented Flux Time Picker solves the workflow.

## Component: Timeline

Source: https://fluxui.dev/components/timeline
Reviewed: 2026-06-20

### Purpose

Flux Timeline is a Flux Pro compound component for displaying a series of events or steps in a vertical or horizontal timeline.

### Basic usage

Basic vertical timeline:

```blade
<flux:timeline>
    <flux:timeline.item>
        <flux:timeline.indicator>
            <flux:icon.eye variant="micro" />
        </flux:timeline.indicator>

        <flux:timeline.content>
            <flux:heading>
                {{ __('activity.review_requested') }}
                <flux:text inline>{{ __('activity.days_ago', ['count' => 4]) }}</flux:text>
            </flux:heading>
        </flux:timeline.content>
    </flux:timeline.item>

    <flux:timeline.item>
        <flux:timeline.indicator color="green">
            <flux:icon.check variant="micro" />
        </flux:timeline.indicator>

        <flux:timeline.content>
            <flux:heading>{{ __('activity.approved') }}</flux:heading>
        </flux:timeline.content>
    </flux:timeline.item>
</flux:timeline>
```

Large numbered steps:

```blade
<flux:timeline size="lg">
    <flux:timeline.item>
        <flux:timeline.indicator>1</flux:timeline.indicator>

        <flux:timeline.content>
            <flux:heading>{{ __('wizard.submit') }}</flux:heading>
            <flux:text>{{ __('wizard.submit_help') }}</flux:text>
        </flux:timeline.content>
    </flux:timeline.item>
</flux:timeline>
```

Horizontal timeline:

```blade
<flux:timeline horizontal>
    <flux:timeline.item>
        <flux:timeline.indicator>
            <flux:icon.credit-card variant="micro" />
        </flux:timeline.indicator>

        <flux:timeline.content>
            <flux:heading>{{ __('booking.order_confirmed') }}</flux:heading>
        </flux:timeline.content>
    </flux:timeline.item>
</flux:timeline>
```

Progress status:

```blade
<flux:timeline horizontal>
    <flux:timeline.item status="complete">
        <flux:timeline.indicator>
            <flux:icon.credit-card variant="micro" />
        </flux:timeline.indicator>

        <flux:timeline.content>
            <flux:heading>{{ __('booking.confirmed') }}</flux:heading>
        </flux:timeline.content>
    </flux:timeline.item>

    <flux:timeline.item status="current">
        <flux:timeline.indicator>
            <flux:icon.truck variant="micro" />
        </flux:timeline.indicator>

        <flux:timeline.content>
            <flux:heading>{{ __('booking.in_progress') }}</flux:heading>
        </flux:timeline.content>
    </flux:timeline.item>

    <flux:timeline.item status="incomplete">
        <flux:timeline.indicator>
            <flux:icon.home variant="micro" />
        </flux:timeline.indicator>

        <flux:timeline.content>
            <flux:heading>{{ __('booking.completed') }}</flux:heading>
        </flux:timeline.content>
    </flux:timeline.item>
</flux:timeline>
```

Indicator colors:

```blade
<flux:timeline.indicator color="red">
    <flux:icon.x-mark variant="micro" />
</flux:timeline.indicator>

<flux:timeline.indicator color="amber">
    <flux:icon.exclamation-triangle variant="micro" />
</flux:timeline.indicator>

<flux:timeline.indicator color="green">
    <flux:icon.check variant="micro" />
</flux:timeline.indicator>
```

Bare indicator:

```blade
<flux:timeline.indicator variant="bare">
    <flux:icon.document-text class="size-6 text-zinc-400" />
</flux:timeline.indicator>
```

Block item:

```blade
<flux:timeline>
    <flux:timeline.item>
        <flux:timeline.block>
            <flux:callout variant="secondary">
                <flux:callout.heading>
                    {{ __('messages.host_replied') }}
                    <flux:text>{{ __('messages.reply_time') }}</flux:text>
                </flux:callout.heading>

                <x-slot name="actions">
                    <flux:button>{{ __('messages.view_message') }}</flux:button>
                    <flux:button variant="ghost">{{ __('messages.reply') }}</flux:button>
                </x-slot>
            </flux:callout>
        </flux:timeline.block>
    </flux:timeline.item>
</flux:timeline>
```

Block subgrid:

```blade
<flux:timeline.block class="border rounded-xl overflow-hidden">
    <flux:timeline.subgrid class="p-3">
        <flux:avatar size="xs" circle src="{{ $avatarUrl }}" />

        <div class="space-y-1">
            <flux:heading>{{ __('messages.guest_commented') }}</flux:heading>
            <flux:text>{{ $commentPreview }}</flux:text>
        </div>
    </flux:timeline.subgrid>
</flux:timeline.block>
```

Alignment:

```blade
<flux:timeline align="start">
    <!-- ... -->
</flux:timeline>

<flux:timeline.item align="baseline">
    <flux:timeline.indicator>1</flux:timeline.indicator>
    <flux:timeline.content>
        <flux:heading>{{ __('booking.created') }}</flux:heading>
    </flux:timeline.content>
</flux:timeline.item>
```

Baseline adjustment:

```blade
<flux:timeline.item class="[&_[data-flux-timeline-baseline]]:text-2xl" align="baseline">
    <flux:timeline.indicator>2</flux:timeline.indicator>

    <flux:timeline.content>
        <flux:heading size="xl">{{ __('wizard.review') }}</flux:heading>
    </flux:timeline.content>
</flux:timeline.item>
```

Spacing:

```blade
<flux:timeline class="[--flux-timeline-item-gap:3rem] [--flux-timeline-content-gap:1rem]">
    <!-- ... -->
</flux:timeline>
```

### Props and attributes

`flux:timeline` props:

- `horizontal`: renders the timeline horizontally instead of vertically.
- `align`: cross-axis alignment of indicators to content. Options: `start`, `baseline`, `center`, `end`. Default: `center`.
- `size`: size of the timeline indicators. Option: `lg`.

`flux:timeline` styling variables:

- `--flux-timeline-item-gap`: controls the space between each timeline item.
- `--flux-timeline-content-gap`: controls the space between the indicator and the content within an item.

`flux:timeline.item` props:

- `status`: controls indicator and connector line styling. Options: `complete`, `current`, `incomplete`.
- `align`: per-item alignment override. Options: `start`, `baseline`, `center`, `end`.
- `size`: per-item size override. Option: `lg`.

`flux:timeline.indicator` props:

- `variant`: visual variant. Option: `bare`, which strips default sizing and background.
- `status`: overrides the parent item's status for indicator styling. Options: `complete`, `current`, `incomplete`.
- `color`: colored background for the indicator. Options: `red`, `orange`, `amber`, `yellow`, `lime`, `green`, `emerald`, `teal`, `cyan`, `sky`, `blue`, `indigo`, `violet`, `purple`, `fuchsia`, `pink`, `rose`.

Documented attributes and selectors:

- `data-flux-timeline-baseline`: target this attribute for custom baseline adjustment when using `align="baseline"` with large text.

### Slots and child components

- `flux:timeline` default slot: timeline items.
- `flux:timeline.item` default slot: indicator and content components for this item.
- `flux:timeline.indicator` default slot: content inside the indicator, such as icons, numbers, or text.
- `flux:timeline.content` default slot: content displayed next to the indicator.
- `flux:timeline.block` default slot: full-width block content with no indicator column; use for embedded cards, callouts, or similar full-width content.
- `flux:timeline.subgrid` default slot: content displayed inside a subgrid layout that realigns content with the timeline indicator and content columns.
- Related components documented with Timeline are Progress and Tabs.

### Livewire and Laravel usage

- The Timeline docs do not document Timeline-specific events, actions, or Livewire bindings.
- Use Timeline as a rendering component for prepared event, status, step, audit, booking, payment, check-in, check-out, message, complaint, review, or host-listing workflow data.
- Prepare timeline rows in Livewire class components, presenters, services, or DTO arrays before rendering; do not query or calculate timeline state in Blade.
- Use `@forelse` for dynamic timeline rows so empty states are explicit.
- Use translated headings, body text, badge labels, button labels, dates, and relative-time text.
- Use documented Flux child components inside Timeline, such as `flux:heading`, `flux:text`, `flux:badge`, `flux:button`, `flux:callout`, `flux:avatar`, and `flux:composer`, only according to their own saved component rules.
- Use `wire:click` on nested documented action components such as `flux:button` when an item needs an action; Timeline itself does not define documented action props.

### Styling, variants, and states

- Timeline is documented as a Flux Pro component.
- Default orientation is vertical; use `horizontal` only when the row fits comfortably on target screen widths.
- Use `size="lg"` for larger indicators, especially numbered steps.
- Use `status="complete"`, `status="current"`, and `status="incomplete"` on `flux:timeline.item` for progress state styling.
- Use `status` on `flux:timeline.indicator` only when the indicator needs to override the parent item status.
- Use `color` on `flux:timeline.indicator` for semantic colored backgrounds.
- Use `variant="bare"` on indicators when using larger standalone icons instead of the default indicator treatment.
- Use `align` on the timeline or on individual items for `start`, `baseline`, `center`, or `end` alignment.
- Use the documented `data-flux-timeline-baseline` selector for baseline adjustment with large text.
- Use the documented CSS variables for item and content spacing.
- No documented `variant` prop is listed for `flux:timeline`, `flux:timeline.item`, `flux:timeline.content`, `flux:timeline.block`, or `flux:timeline.subgrid`.
- No documented `color` prop is listed for `flux:timeline`, `flux:timeline.item`, `flux:timeline.content`, `flux:timeline.block`, or `flux:timeline.subgrid`.

### Accessibility requirements or recommendations

- The official Timeline page does not document explicit ARIA props.
- Do not communicate booking, payment, verification, or complaint status by color alone; pair color and icon choices with translated text.
- Keep horizontal timelines short and readable on mobile; use vertical timelines for longer histories and 320px-first screens.
- Use meaningful headings and concise text for each item so the sequence is understandable without relying only on icons.
- Ensure nested buttons and links inside block items use documented Flux Button or Link semantics.

### Project rules

- Prefer `flux:timeline` for booking lifecycle histories, payment milestones, host listing wizard steps, stay extension progress, relocation steps, complaint status, review timelines, and guest/host activity histories.
- Prefer vertical timelines for mobile-first history and audit displays.
- Use horizontal timelines only for short progress summaries with a small number of steps.
- Map domain statuses to the documented Timeline statuses only when they fit: `complete`, `current`, and `incomplete`.
- Use `flux:timeline.block` for rich event blocks such as callouts, message previews, or composer replies.
- Use `flux:timeline.subgrid` inside blocks when nested content should align with Timeline columns.
- Keep icons documented and valid; use Flux Icon rules before adding or changing icon names.
- Keep timeline data compact and precomputed; do not load full audit logs or large histories into Livewire public properties.
- Paginate or progressively reveal long histories instead of rendering a huge DOM.

### Mistakes to avoid

- Do not invent undocumented Timeline props such as `variant`, `color`, `icon`, `badge`, `dense`, `compact`, `interactive`, `loading`, `href`, `wire:model`, or `clickable` on `flux:timeline`.
- Do not use undocumented item statuses beyond `complete`, `current`, and `incomplete`.
- Do not apply indicator `color` values outside the documented Flux color list.
- Do not use `horizontal` for long mobile histories that will overflow or become cramped.
- Do not hard-code visible Timeline text in Blade.
- Do not query booking, payment, message, complaint, or audit records inside Timeline Blade loops.
- Do not rely on color alone to express important marketplace state.

## Component: Toast

Source: https://fluxui.dev/components/toast
Reviewed: 2026-06-20

### Purpose

Flux Toast provides temporary, dismissible feedback messages for actions or events.

### Basic usage

Place a toast host somewhere on the page, often in the layout:

```blade
<body>
    <!-- ... -->
    <flux:toast />
</body>
```

Persist the toast host when using `wire:navigate` so messages do not disappear during navigation:

```blade
<body>
    <!-- ... -->

    @persist('toast')
        <flux:toast />
    @endpersist
</body>
```

Trigger a toast from a Livewire component:

```php
<?php

namespace App\Livewire;

use Flux\Flux;
use Livewire\Component;

class EditPost extends Component
{
    public function save(): void
    {
        // ...

        Flux::toast(__('posts.saved'));
    }
}
```

Toast with heading:

```php
Flux::toast(
    heading: __('notifications.changes_saved'),
    text: __('notifications.settings_can_be_updated'),
);
```

Toast with link:

```php
Flux::toast(
    text: __('invoices.created'),
    link: [
        'text' => __('invoices.view_invoice'),
        'href' => route('invoices.show', $invoice),
        'navigate' => true,
    ],
);
```

Variants:

```php
Flux::toast(variant: 'success', text: __('notifications.saved'));
Flux::toast(variant: 'warning', text: __('notifications.review_needed'));
Flux::toast(variant: 'danger', text: __('notifications.failed'));
```

Duration:

```php
Flux::toast(duration: 1000, text: __('notifications.saved'));
Flux::toast(duration: 0, text: __('notifications.manual_dismiss_required'));
```

Positioning:

```blade
<flux:toast position="top end" />
<flux:toast position="top end" class="pt-24" />
```

Stacked toasts:

```blade
<flux:toast.group>
    <flux:toast />
</flux:toast.group>
```

Always-expanded stack:

```blade
<flux:toast.group expanded>
    <flux:toast />
</flux:toast.group>
```

Positioned stack:

```blade
<flux:toast.group position="top end">
    <flux:toast />
</flux:toast.group>
```

Alpine trigger:

```blade
<button x-on:click="$flux.toast('{{ __('notifications.saved') }}')">
    {{ __('actions.save_changes') }}
</button>
```

JavaScript trigger:

```blade
<script>
    Flux.toast({
        heading: @js(__('notifications.changes_saved')),
        text: @js(__('notifications.saved')),
        variant: 'success',
    })
</script>
```

JavaScript link example:

```blade
<button
    x-on:click="$flux.toast(@js(__('invoices.created')), {
        link: {
            text: @js(__('invoices.view_invoice')),
            href: @js(route('invoices.show', $invoice)),
            navigate: true,
        },
    })"
>
    {{ __('invoices.create_invoice') }}
</button>
```

### Props and attributes

`flux:toast` props:

- `position`: position of the toast on the screen. Options: `bottom end` (default), `bottom center`, `bottom start`, `top end`, `top center`, `top start`.

`flux:toast.group` props:

- `position`: position of the toast group on the screen. Options: `bottom end` (default), `bottom center`, `bottom start`, `top end`, `top center`, `top start`.
- `expanded`: when true, always shows the toast stack expanded so all toasts are visible at once. Default: `false`.

`Flux::toast()` parameters:

- `heading`: optional heading text for the toast.
- `text`: main content text for the toast.
- `variant`: visual style. Options: `success`, `warning`, `danger`.
- `duration`: duration in milliseconds. Use `0` for permanent toasts. Default: `5000`.
- `link`: optional link configuration. Supports `text`, `href`, `target`, `rel`, `download`, and `navigate`.

`$flux.toast()` parameters:

- `message`: string containing the toast message. In simple form, the message becomes the toast text content.
- `options`: object containing `heading`, `text`, `variant`, `duration`, and optional `link` configuration with `text`, `href`, `target`, `rel`, `download`, and `navigate`.

`window.Flux.toast()` supports the documented simple message and configuration object signatures shown for JavaScript usage.

### Slots and child components

- No named slots are documented for `flux:toast`.
- `flux:toast.group` wraps a `flux:toast` component to display stacked toasts.
- Related components documented with Toast are Callout and Modal.

### Livewire and Laravel usage

- Add a single `flux:toast` host in the app layout before triggering toasts.
- When the layout uses `wire:navigate`, wrap the host in `@persist('toast')` as documented.
- Use `Flux::toast()` from Livewire class components after successful actions or important recoverable events.
- Import `Flux\Flux` in PHP classes before calling `Flux::toast()`.
- Use translated strings for `heading`, `text`, link text, button labels, and any JavaScript-triggered message.
- Use named routes via `route()` for Laravel toast links.
- Use `navigate => true` in link configuration when the toast link should follow Livewire navigation.
- Prefer server-triggered `Flux::toast()` for Livewire actions in this project; use `$flux.toast()` or `Flux.toast()` only when the interaction is truly client-side.
- Do not use Toast as the only validation surface for forms; keep inline `flux:error` or validation messages near the relevant field.
- Do not put booking, pricing, availability, or authorization logic in Blade or JavaScript just to decide which toast to show; return the outcome from services/actions and trigger the toast from the Livewire class.

### Styling, variants, and states

- Default toast position is `bottom end`.
- Valid positions are `bottom end`, `bottom center`, `bottom start`, `top end`, `top center`, and `top start`.
- Use classes such as `pt-24` on `flux:toast` when a positioned toast needs padding for navbars.
- Valid toast variants are `success`, `warning`, and `danger`.
- Default duration is `5000` milliseconds.
- Use `duration: 0` for permanent toasts.
- A toast stack is created by wrapping `flux:toast` in `flux:toast.group`.
- Toast stacks overlap by default and expand on hover.
- Use `expanded` on `flux:toast.group` to always show all stacked toasts.
- No documented `variant`, `duration`, `heading`, `text`, `link`, or `expanded` props are listed for `flux:toast`; these are trigger parameters or group props as documented above.

### Accessibility requirements or recommendations

- The official Toast page does not document explicit ARIA props.
- Keep toast text concise and clear so temporary messages are understandable before they dismiss.
- Use permanent toasts only when the user must manually acknowledge or follow up on the message.
- Do not rely only on color or variant to communicate success, warning, or danger; use translated wording that explains the state.
- Provide a link when the toast needs a clear next step.

### Project rules

- Prefer Flux Toast for action feedback such as saved settings, booking request submitted, payment placeholder confirmed, review submitted, message sent, favorite changed, or host listing step saved.
- Use translated, calm, friendly copy for every toast.
- Use `success` for completed actions, `warning` for recoverable attention states, and `danger` for failed or blocked actions.
- Use inline validation errors, callouts, or modal confirmations for persistent or field-specific issues; Toast is supporting feedback, not the only explanation.
- Keep mobile placement in mind; avoid top-positioned toasts that cover mobile headers or important booking actions unless padding is configured.
- Keep the toast host in the shared layout rather than duplicating `flux:toast` across pages.
- Do not stack noisy toasts for every minor Livewire update; reserve them for user-visible outcomes.

### Mistakes to avoid

- Do not trigger toasts before a `flux:toast` host exists on the page.
- Do not omit `@persist('toast')` in layouts that use `wire:navigate` and need toast continuity.
- Do not invent undocumented Toast variants such as `info`, `primary`, `secondary`, `neutral`, or `destructive`.
- Do not pass undocumented parameters such as `position`, `icon`, `size`, `color`, `dismissible`, or `actions` to `Flux::toast()`.
- Do not put `duration`, `variant`, `heading`, `text`, or `link` on `flux:toast` as component props unless the Flux docs add that pattern later.
- Do not hard-code toast copy or link labels in Blade, PHP, Alpine, or JavaScript.
- Do not use Toast as a replacement for authorization, validation, confirmation modals, or visible booking-state explanations.

## Component: Tooltip

Source: https://fluxui.dev/components/tooltip
Reviewed: 2026-06-20

### Purpose

Flux Tooltip provides additional information when users hover over or focus on an element.

The official docs warn that touch devices such as mobile phones often do not show hover-based tooltips, so essential information should be conveyed directly in the UI instead of relying on a tooltip.

### Basic usage

Basic tooltip:

```blade
<flux:tooltip content="{{ __('navigation.settings') }}">
    <flux:button icon="cog-6-tooth" icon:variant="outline" />
</flux:tooltip>
```

Button shorthand:

```blade
<flux:button icon="cog-6-tooth" tooltip="{{ __('navigation.settings') }}" />
```

Info tooltip with toggleable behavior for touch devices:

```blade
<flux:heading class="flex items-center gap-2">
    {{ __('forms.tax_identification_number') }}

    <flux:tooltip toggleable>
        <flux:button icon="information-circle" size="sm" variant="ghost" />

        <flux:tooltip.content class="max-w-[20rem] space-y-2">
            <p>{{ __('forms.tax_identification_number_help_primary') }}</p>
            <p>{{ __('forms.tax_identification_number_help_secondary') }}</p>
        </flux:tooltip.content>
    </flux:tooltip>
</flux:heading>
```

Position options:

```blade
<flux:tooltip content="{{ __('navigation.settings') }}" position="top">
    <flux:button icon="cog-6-tooth" icon:variant="outline" />
</flux:tooltip>

<flux:tooltip content="{{ __('navigation.settings') }}" position="right">
    <flux:button icon="cog-6-tooth" icon:variant="outline" />
</flux:tooltip>

<flux:tooltip content="{{ __('navigation.settings') }}" position="bottom">
    <flux:button icon="cog-6-tooth" icon:variant="outline" />
</flux:tooltip>

<flux:tooltip content="{{ __('navigation.settings') }}" position="left">
    <flux:button icon="cog-6-tooth" icon:variant="outline" />
</flux:tooltip>
```

Disabled button workaround:

```blade
<flux:tooltip content="{{ __('booking.cannot_confirm_until_reviewed') }}">
    <div>
        <flux:button disabled icon="arrow-turn-down-right">
            {{ __('booking.confirm_request') }}
        </flux:button>
    </div>
</flux:tooltip>
```

Keyboard shortcut hint:

```blade
<flux:tooltip content="{{ __('navigation.search') }}" kbd="Cmd+K">
    <flux:button icon="magnifying-glass" />
</flux:tooltip>
```

Tooltip content keyboard hint:

```blade
<flux:tooltip>
    <flux:button icon="question-mark-circle" />

    <flux:tooltip.content kbd="?">
        {{ __('help.open_help') }}
    </flux:tooltip.content>
</flux:tooltip>
```

### Props and attributes

`flux:tooltip` props:

- `content`: text content to display in the tooltip. Alternative to using the `flux:tooltip.content` component.
- `position`: position of the tooltip relative to the trigger element. Options: `top` (default), `right`, `bottom`, `left`.
- `align`: tooltip alignment. Options: `center` (default), `start`, `end`.
- `disabled`: prevents user interaction with the tooltip.
- `gap`: spacing in pixels between the trigger element and the tooltip. Default: `5`.
- `offset`: offset in pixels of the tooltip from the trigger element. Default: `0`.
- `toggleable`: makes the tooltip clickable instead of hover-only. Useful for touch devices.
- `interactive`: uses `aria-expanded` and `aria-controls` to signal that the tooltip has interactive content.
- `kbd`: keyboard shortcut hint displayed at the end of the tooltip.

`flux:tooltip` attributes:

- `data-flux-tooltip`: applied to the root element for styling and identification.

`flux:tooltip.content` props:

- `kbd`: keyboard shortcut hint displayed at the end of the tooltip content.

### Slots and child components

- `flux:tooltip` default slot contains the trigger element and, when not using the `content` prop, a `flux:tooltip.content` child component.
- `flux:tooltip.content` default slot contains custom tooltip content.
- The docs show `flux:button` as the trigger element.
- Related components documented with Tooltip are Button and Icon.

### Livewire and Laravel usage

- Use `flux:tooltip` for supplemental hints on icon-only buttons, compact controls, and secondary explanations.
- Use the documented `tooltip` shorthand prop on `flux:button` when a simple text tooltip is enough.
- Use `flux:tooltip.content` when tooltip content needs multiple lines, markup, or a keyboard shortcut hint.
- Use translated strings for all tooltip content and visible trigger labels.
- For essential help text, show the information directly in the UI and use the tooltip only as an enhancement.
- Use `toggleable` when tooltip content is important for touch users.
- Use `interactive` when tooltip content is interactive so Flux applies the documented ARIA attributes.
- Do not query, calculate, or authorize inside tooltip Blade markup; pass prepared strings and state from Livewire classes, presenters, services, or DTO arrays.

### Styling, variants, and states

- Default position is `top`.
- Position options are `top`, `right`, `bottom`, and `left`.
- Alignment options are `center`, `start`, and `end`; default is `center`.
- Default `gap` is `5` pixels.
- Default `offset` is `0` pixels.
- Use `disabled` to prevent tooltip interaction.
- Use `toggleable` to support click/press opening instead of hover-only behavior.
- Use `kbd` on either `flux:tooltip` or `flux:tooltip.content` for keyboard shortcut hints.
- No documented `variant`, `color`, `size`, `icon`, `delay`, `duration`, `open`, `wire:model`, or `placement` props are listed for `flux:tooltip`.

### Accessibility requirements or recommendations

- Do not rely on hover-only tooltips for essential mobile information.
- Use `toggleable` when content is important enough for touch users to access.
- Use `interactive` when tooltip content contains interactive controls or needs the documented `aria-expanded` and `aria-controls` behavior.
- Keep tooltip content short and connected to the trigger.
- Icon-only buttons should have an accessible label through the documented Button/Icon patterns; tooltip copy should enhance, not replace, core semantics.

### Project rules

- Prefer `flux:tooltip` over custom tooltip markup when a tooltip is appropriate.
- Use tooltips for optional explanations, icon button labels, keyboard shortcut hints, and compact control help.
- Keep essential booking, price, availability, rule, verification, and error information visible outside the tooltip, especially on 320px mobile screens.
- Prefer `toggleable` for any tooltip whose content affects booking, payment, identity, host rules, or next-step understanding.
- Use the disabled-button wrapper workaround when a disabled Flux Button needs a tooltip.
- Keep tooltip content concise; use Callout, Field description, Modal, or visible text for longer explanations.
- Keep all tooltip copy translated through Laravel language files.

### Mistakes to avoid

- Do not put essential mobile-only information exclusively in hover tooltips.
- Do not invent undocumented Tooltip props such as `variant`, `color`, `size`, `icon`, `delay`, `duration`, `open`, `wire:model`, `placement`, or `dismissible`.
- Do not use undocumented positions beyond `top`, `right`, `bottom`, and `left`.
- Do not use undocumented alignments beyond `center`, `start`, and `end`.
- Do not rely on a tooltip attached directly to a disabled button; wrap the disabled button as documented.
- Do not hard-code tooltip text in Blade.
- Do not use long paragraphs in hover-only tooltips; use visible UI or a toggleable tooltip when more detail is needed.
