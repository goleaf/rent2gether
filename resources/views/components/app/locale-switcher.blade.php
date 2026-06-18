@props(['currentLocale' => app()->getLocale()])

<flux:dropdown align="end">
    <flux:button variant="ghost" icon="language">
        {{ strtoupper($currentLocale) }}
    </flux:button>

    <flux:menu>
        <flux:menu.item href="{{ url('/en') }}" class="{{ $currentLocale === 'en' ? 'font-medium' : '' }}">EN</flux:menu.item>
        <flux:menu.item href="{{ url('/ru') }}" class="{{ $currentLocale === 'ru' ? 'font-medium' : '' }}">RU</flux:menu.item>
    </flux:menu>
</flux:dropdown>
