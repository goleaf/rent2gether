<flux:dropdown align="end">
    <flux:button variant="ghost" icon="language" aria-label="{{ __('navigation.language_switcher') }}">
        {{ strtoupper(app()->getLocale()) }}
    </flux:button>

    <flux:menu>
        @foreach($locales as $locale)
            <flux:menu.item
                href="{{ $urlFor($locale) }}"
                class="{{ app()->isLocale($locale) ? 'font-medium' : '' }}"
                wire:navigate
             icon="language">
                {{ __('navigation.languages.'.$locale) }}
            </flux:menu.item>
        @endforeach
    </flux:menu>
</flux:dropdown>
