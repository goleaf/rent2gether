<div class="space-y-4">
    <flux:card class="space-y-1">
        <flux:heading size="lg">{{ __('listing_publish.title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_publish.helper') }}</flux:text>
    </flux:card>

    <livewire:host.listings.before-publish-checklist :property-id="$propertyId" :key="'before-publish-'.$propertyId" />
</div>
