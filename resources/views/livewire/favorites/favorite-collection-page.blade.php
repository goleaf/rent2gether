<x-ui.page>
    <section class="space-y-3">
        <flux:badge color="emerald" icon="check-circle">{{ __('favorites.collection') }}</flux:badge>
        <div class="space-y-1">
            <flux:heading size="xl" level="1">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="heart" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $this->collection->title }}</span>
                </span>
            </flux:heading>
            @if($this->collection->description)
                <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">{{ $this->collection->description }}</flux:text>
            @else
                <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">{{ __('favorites.collection_helper') }}</flux:text>
            @endif
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:badge icon="heart">{{ trans_choice('favorites.counts.places', (int) $this->collection->favorites_count, ['count' => (int) $this->collection->favorites_count]) }}</flux:badge>
            <flux:badge color="green" icon="check-circle">{{ trans_choice('favorites.counts.available', (int) $this->collection->available_favorites_count, ['count' => (int) $this->collection->available_favorites_count]) }}</flux:badge>
            <flux:badge color="amber" icon="exclamation-triangle">{{ trans_choice('favorites.counts.price_changed', (int) $this->collection->price_changed_favorites_count, ['count' => (int) $this->collection->price_changed_favorites_count]) }}</flux:badge>
        </div>
    </section>

    <flux:card class="space-y-3">
        <div class="grid gap-3 sm:grid-cols-2">
            <livewire:favorites.favorite-filters :filter="$filter" />
            <livewire:favorites.favorite-sort :sort="$sort" />
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <flux:button type="button" variant="primary" icon="heart" wire:click="compareSelected" wire:loading.attr="disabled">
                {{ __('favorites.compare_selected', ['count' => count($selectedForCompare)]) }}
            </flux:button>
            <flux:text size="sm" class="text-zinc-500">{{ __('favorites.compare_helper') }}</flux:text>
        </div>

        @error('selectedForCompare')
            <flux:callout color="amber" icon="exclamation-triangle">
                <flux:callout.text>{{ $message }}</flux:callout.text>
            </flux:callout>
        @enderror
    </flux:card>

    <div wire:loading.delay class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
        {{ __('favorites.loading') }}
    </div>

    <section class="grid gap-3 sm:grid-cols-2">
        @forelse($this->cards as $card)
            <livewire:favorites.favorite-card :card="$card" :key="'collection-favorite-'.$card['id']" />
        @empty
            <flux:card class="sm:col-span-2">
                <div class="space-y-3 text-center">
                    <div class="mx-auto flex size-11 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                        <flux:icon name="folder-open" class="size-6" />
                    </div>
                    <div class="space-y-1">
                        <flux:heading size="lg">
                            <span class="inline-flex min-w-0 items-center gap-2">
                                <flux:icon name="heart" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('favorites.collection_empty.title') }}</span>
                            </span>
                        </flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('favorites.collection_empty.text') }}</flux:text>
                    </div>
                </div>
            </flux:card>
        @endforelse
    </section>

    @if(count($this->cards) >= $visibleCount)
        <flux:button type="button" variant="primary" class="w-full" wire:click="loadMore" wire:loading.attr="disabled" icon="arrow-down">
            {{ __('favorites.load_more') }}
        </flux:button>
    @endif
</x-ui.page>
