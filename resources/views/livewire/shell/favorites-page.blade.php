<x-ui.page>
    <section class="space-y-3">
        <flux:badge color="emerald" icon="check-circle">{{ $page['eyebrow'] }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="heart" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $page['title'] }}</span>
                </span>
            </flux:heading>
            <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">
                {{ $page['helper'] }}
            </flux:text>
        </div>

        @if($actionHref)
            <flux:button href="{{ $actionHref }}" variant="primary" wire:navigate class="data-loading:opacity-70" icon="map-pin">
                {{ $page['action'] }}
            </flux:button>
        @endif
    </section>

    <livewire:favorites.favorites-page />
</x-ui.page>
