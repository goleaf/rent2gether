<a href="{{ route('favorites.index', ['locale' => app()->getLocale()]) }}" wire:navigate class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm font-medium text-zinc-800 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100">
    <flux:icon name="heart" class="size-4 text-emerald-600 dark:text-emerald-300" />
    <span>{{ trans_choice('favorites.counts.saved', $this->count, ['count' => $this->count]) }}</span>
</a>
