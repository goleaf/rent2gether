<div class="space-y-4">
    <flux:card class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('listing_wizard.sleeping_places.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_wizard.sleeping_places.helper') }}</flux:text>
    </flux:card>

    @forelse($rooms as $room)
        <livewire:host.listings.sleeping-place-repeater :room-id="$room->id" :key="'place-repeater-'.$room->id" />
    @empty
        <flux:callout variant="secondary" icon="information-circle" :text="__('listing_wizard.rooms.empty')" />
    @endforelse
</div>
