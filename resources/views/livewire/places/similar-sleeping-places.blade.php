<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
    @forelse($places as $place)
        <x-listings.card :card="$place" card-variant="compact" />
    @empty
        <flux:card class="space-y-2 text-center sm:col-span-2 lg:col-span-3">
            <flux:icon name="magnifying-glass" class="mx-auto size-8 text-zinc-300 dark:text-zinc-700" />
            <flux:heading size="sm">{{ __('listing.detail.similar.empty_title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-500">{{ __('listing.detail.similar.empty_helper') }}</flux:text>
        </flux:card>
    @endforelse
</div>
