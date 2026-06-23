<div class="space-y-4">
    <flux:card class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('listing_calendar.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_calendar.helper') }}</flux:text>
    </flux:card>

    <livewire:host.listings.calendar-rules-editor :property-id="$propertyId" :key="'calendar-rules-'.$propertyId" />
    <livewire:host.listings.calendar-bulk-editor :property-id="$propertyId" :key="'calendar-bulk-'.$propertyId" />
</div>
