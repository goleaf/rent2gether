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
