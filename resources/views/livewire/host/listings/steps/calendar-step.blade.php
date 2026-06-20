<div class="space-y-4">
    <flux:card class="space-y-1">
        <flux:heading size="lg">{{ __('listing_calendar.title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_calendar.helper') }}</flux:text>
    </flux:card>

    <livewire:host.listings.calendar-rules-editor :property-id="$propertyId" :key="'calendar-rules-'.$propertyId" />
    <livewire:host.listings.calendar-bulk-editor :property-id="$propertyId" :key="'calendar-bulk-'.$propertyId" />
</div>
