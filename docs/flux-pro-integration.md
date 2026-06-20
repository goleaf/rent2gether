# Flux Pro Integration Runbook

Flux Pro is installed in `rent2gether`.

## Current Status

- Laravel `13.16.1`
- PHP `8.5`
- Livewire `4.3.1`
- Flux UI `2.14.1`
- Flux UI Pro `2.14.1`
- Tailwind CSS `4.3.1`
- `/auth.json` is ignored by Git.
- `/_data/flux-pro/` is ignored by Git.

## Installation Method

The project uses a local Composer path repository:

```json
{
    "type": "path",
    "url": "_data/flux-pro",
    "options": {
        "symlink": false
    }
}
```

Composer mirrors `_data/flux-pro` into `vendor/livewire/flux-pro`. This avoids storing Flux license credentials in the repository.

## Important Caveat

Because `_data/flux-pro` is ignored, another machine needs one of:

- the same `_data/flux-pro` package folder, or
- official Composer authentication for `composer.fluxui.dev`, followed by switching or removing the local path repository as appropriate.

Do not commit `_data/flux-pro`, `auth.json`, or license material.

## Runtime Wiring

Flux is wired in:

- `resources/css/app.scss`
- `resources/views/components/layouts/app.blade.php`
- `resources/views/layouts/app.blade.php`

The shared layout includes:

```blade
@livewireStyles
@fluxAppearance
@livewireScripts
@fluxScripts
```

The CSS includes:

```css
@use 'tailwindcss';
@import '../../vendor/livewire/flux/dist/flux.css';
@import '../../vendor/livewire/flux-pro/dist/editor.css';
@custom-variant dark (&:where(.dark, .dark *));
```

Tailwind is processed through `@tailwindcss/postcss` after Sass so the SCSS entrypoint can keep Tailwind v4 directives working.

## Agent Skill

Use the generated project skill `fluxui-development` for Flux UI work. It lives at `.agents/skills/fluxui-development/SKILL.md`.

## Component Migration Rules

- Read the official Flux documentation page before migrating a component family.
- Prefer Flux Pro components over custom Blade/Tailwind markup whenever a matching Flux component exists.
- Accordion-like disclosure UI must use `flux:accordion`, `flux:accordion.item`, `flux:accordion.heading`, and `flux:accordion.content`.
- Use `transition` for content-heavy mobile sections so expansion feels smooth.
- Use `exclusive` for FAQ-style groups where only one answer should be open at a time.
- Use `:expanded="..."` for default-open sections instead of native `open` attributes.
- Do not add raw `<details>` or `<summary>` tags in app Blade views; `tests/Feature/FluxProComponentUsageTest.php` enforces this.
- Autocomplete-like text suggestion inputs should use `flux:autocomplete` and `flux:autocomplete.item`.
- Use Flux Autocomplete only when the stored value is the input text. If the UI must show one label while storing a different ID/value, follow the Flux documentation and use a combobox/select-style component instead.

Accordion reference: `https://fluxui.dev/components/accordion`.
Autocomplete reference: `https://fluxui.dev/components/autocomplete`.

## Verification

Use these commands after Flux or component changes:

```bash
composer show livewire/livewire
composer show livewire/flux
composer show livewire/flux-pro
npm run build
php artisan test --compact
```

For browser checks, resolve the Herd URL with Laravel Boost `get-absolute-url`.
