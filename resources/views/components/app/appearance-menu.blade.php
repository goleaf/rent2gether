<flux:dropdown x-data align="end">
    <flux:button variant="subtle" square class="group" aria-label="{{ __('navigation.appearance.label') }}">
        <flux:icon.sun x-show="$flux.appearance === 'light'" variant="mini" class="text-zinc-500 dark:text-white" />
        <flux:icon.moon x-show="$flux.appearance === 'dark'" variant="mini" class="text-zinc-500 dark:text-white" />
        <flux:icon.moon x-show="$flux.appearance === 'system' && $flux.dark" variant="mini" class="text-zinc-500 dark:text-white" />
        <flux:icon.sun x-show="$flux.appearance === 'system' && ! $flux.dark" variant="mini" class="text-zinc-500 dark:text-white" />
    </flux:button>

    <flux:menu>
        <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'">{{ __('navigation.appearance.light') }}</flux:menu.item>
        <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'">{{ __('navigation.appearance.dark') }}</flux:menu.item>
        <flux:menu.item icon="computer-desktop" x-on:click="$flux.appearance = 'system'">{{ __('navigation.appearance.system') }}</flux:menu.item>
    </flux:menu>
</flux:dropdown>
