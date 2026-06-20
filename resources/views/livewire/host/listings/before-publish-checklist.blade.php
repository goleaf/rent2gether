<div class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('listing_publish.checklist_title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_publish.checklist_helper') }}</flux:text>
    </div>

    <flux:text size="sm" class="font-medium">{{ __('listing_publish.blocking_issues') }}</flux:text>

    @include('livewire.host.listings.listing-readiness-checklist', ['readiness' => $readiness])
</div>
