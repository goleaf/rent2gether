<div class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="wrench-screwdriver" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('listing_publish.checklist_title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_publish.checklist_helper') }}</flux:text>
    </div>

    <flux:text size="sm" class="font-medium">{{ __('listing_publish.blocking_issues') }}</flux:text>

    @include('livewire.host.listings.listing-readiness-checklist', ['readiness' => $readiness])
</div>
