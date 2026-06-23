<flux:card class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <div>
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $room?->title }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_wizard.sleeping_places.auto_create_helper') }}</flux:text>
        </div>
        <flux:button size="sm" type="button" wire:click="autoCreate" wire:loading.attr="disabled" icon="plus">
            {{ __('listing_wizard.sleeping_places.auto_create') }}
        </flux:button>
    </div>

    <div class="space-y-2">
        @forelse($room?->sleepingPlaces ?? [] as $place)
            <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                <div class="font-medium">{{ $place->display_name ?: __('listing_wizard.defaults.sleeping_place_name', ['number' => $place->place_number]) }}</div>
                <div class="text-xs text-zinc-500">{{ __('listing_wizard.sleeping_places.price') }}: {{ $place->base_price_per_night }} {{ $place->currency }}</div>
            </div>
        @empty
            <div class="rounded-lg bg-zinc-50 px-3 py-3 text-sm text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">
                {{ __('listing_wizard.sleeping_places.empty') }}
            </div>
        @endforelse
    </div>
</flux:card>
