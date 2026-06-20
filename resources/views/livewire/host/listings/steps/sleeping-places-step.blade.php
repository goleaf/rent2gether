<div class="space-y-4">
    <flux:card class="space-y-1">
        <flux:heading size="lg">{{ __('listing_wizard.sleeping_places.title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_wizard.sleeping_places.helper') }}</flux:text>
    </flux:card>

    @foreach($rooms as $room)
        <livewire:host.listings.sleeping-place-repeater :room-id="$room->id" :key="'place-repeater-'.$room->id" />
    @endforeach
</div>
