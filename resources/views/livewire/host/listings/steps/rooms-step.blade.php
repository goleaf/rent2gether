<div class="space-y-4">
    <flux:card class="space-y-1">
        <flux:heading size="lg">{{ __('listing_wizard.rooms.title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_wizard.rooms.helper') }}</flux:text>
    </flux:card>

    <livewire:host.listings.room-repeater :property-id="$propertyId" :key="'room-repeater-'.$propertyId" />
</div>
