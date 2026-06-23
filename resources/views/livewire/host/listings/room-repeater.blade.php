<flux:card class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('listing_wizard.rooms.created') }}</span>
            </span>
        </flux:heading>
        <flux:button size="sm" type="button" wire:click="addRoom" wire:loading.attr="disabled" icon="plus">
            {{ __('listing_wizard.rooms.add_room') }}
        </flux:button>
    </div>

    <div class="space-y-2">
        @forelse($rooms as $room)
            <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                <div class="font-medium">{{ $room->title }}</div>
                <div class="text-xs text-zinc-500">{{ __('listing_wizard.rooms.sleeping_places_count') }}: {{ $room->sleeping_places_count }}</div>
            </div>
        @empty
            <div class="rounded-lg bg-zinc-50 px-3 py-3 text-sm text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">
                {{ __('listing_wizard.rooms.empty') }}
            </div>
        @endforelse
    </div>
</flux:card>
