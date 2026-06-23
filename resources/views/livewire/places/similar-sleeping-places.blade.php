<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
    @forelse($places as $place)
        <x-listings.card :card="$place" card-variant="compact" />
    @empty
        <flux:card class="space-y-2 text-center sm:col-span-2 lg:col-span-3">
            <flux:icon name="magnifying-glass" class="mx-auto size-8 text-zinc-300 dark:text-zinc-700" />
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('listing.detail.similar.empty_title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-500">{{ __('listing.detail.similar.empty_helper') }}</flux:text>
        </flux:card>
    @endforelse
</div>
