<div class="-mx-4 flex gap-3 overflow-x-auto px-4 pb-1 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 lg:grid-cols-3">
    @forelse($this->collectionCards as $collection)
        <a
            href="{{ $collection['url'] }}"
            wire:navigate
            class="block min-w-[16rem] rounded-lg border border-zinc-200 bg-white p-4 shadow-sm transition hover:border-emerald-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-emerald-700 sm:min-w-0"
        >
            <div class="flex items-start gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                    <flux:icon :name="$collection['icon']" class="size-5" />
                </div>
                <div class="min-w-0 flex-1">
                    <flux:heading size="sm" class="truncate">{{ $collection['title'] }}</flux:heading>
                    <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-400">
                        {{ __('favorites.collection_summary', [
                            'count' => $collection['favorites_count'],
                            'available' => $collection['available_count'],
                            'changed' => $collection['price_changed_count'],
                        ]) }}
                    </flux:text>
                    @if($collection['updated'])
                        <flux:text size="xs" class="mt-2 text-zinc-500">{{ __('favorites.updated_at', ['time' => $collection['updated']]) }}</flux:text>
                    @endif
                </div>
            </div>
        </a>
    @empty
        <flux:card class="min-w-[18rem] sm:min-w-0 sm:col-span-2 lg:col-span-3">
            <div class="space-y-1 text-center">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="heart" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('favorites.collections_empty.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('favorites.collections_empty.text') }}</flux:text>
            </div>
        </flux:card>
    @endforelse
</div>
