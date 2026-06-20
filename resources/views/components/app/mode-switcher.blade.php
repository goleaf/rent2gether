<div class="flex items-center rounded-lg bg-zinc-100 p-1 dark:bg-zinc-900" aria-label="{{ __('navigation.mode_switcher') }}">
    <flux:button
        size="xs"
        :variant="$isHostMode ? 'ghost' : 'primary'"
        href="{{ $guestHref }}"
        wire:navigate
        class="min-h-8"
    >
        {{ __('navigation.guest_mode') }}
    </flux:button>

    <flux:button
        size="xs"
        :variant="$isHostMode ? 'primary' : 'ghost'"
        href="{{ $hostHref }}"
        wire:navigate
        class="min-h-8"
    >
        {{ __('navigation.host_mode') }}
    </flux:button>
</div>
