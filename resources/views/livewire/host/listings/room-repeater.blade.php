<flux:card class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="lg">{{ __('listing_wizard.rooms.created') }}</flux:heading>
        <flux:button size="sm" type="button" wire:click="addRoom" wire:loading.attr="disabled">
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
