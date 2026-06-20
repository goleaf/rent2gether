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

### Purpose

Flux is a UI system for Blade components. Its design favors simple syntax first, then more composable component assemblies when the simple form is not enough.

### Basic usage

```blade
<flux:input wire:model="email" label="{{ __('profile.email') }}" />
```

Use the long form when the field needs custom layout:

```blade
<flux:field>
    <flux:label>{{ __('profile.email') }}</flux:label>
    <flux:input wire:model.blur="email" />
    <flux:error name="email" />
</flux:field>
```

### Props and attributes

- No component-specific prop table on this page.
- The page documents the project-level pattern that simple components may expose shorthand props while also supporting composable child components.

### Slots and child components

- Prefer composition with documented child components when customization is needed.
- Examples include `flux:field`, `flux:label`, `flux:error`, `flux:dropdown`, `flux:menu`, and `flux:context`.

### Livewire and Laravel usage

- Use Flux controls like native Livewire inputs: bind with `wire:model`, `wire:model.blur`, `wire:model.change`, or approved debounced search bindings.
- Keep user-facing text translated with Laravel translation keys.
- Use route helpers for internal links and actions.

### Styling, variants, and states

- Flux styles components; the application supplies contextual spacing and layout.
- Prefer margins, gaps, and layout utilities outside Flux components instead of expecting Flux to handle page spacing.
- Let Flux use native browser features and CSS-based interactions instead of adding custom JavaScript.

### Project rules

- Start with the simple Flux syntax for common UI.
- Move to documented composition when the UI needs custom field layout, icons, actions, or validation placement.
- Use Flux-native components before building custom Blade/Tailwind replacements.

### Mistakes to avoid

- Do not create verbose wrapper APIs when Flux already provides concise components.
- Do not add JavaScript for behavior Flux or the browser already handles.
- Do not put `@if` or other Blade control directives inside a Flux component opening tag; use dynamic attributes such as `:disabled="$disabled"`.

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

- Build breadcrumb arrays in the controller/Livewire class/presenter, not in Blade with queries.
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
- `flux:chart.cursor` supports `class` and SVG line styling attributes.
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
