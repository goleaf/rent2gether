<div class="space-y-5">
    <section class="space-y-2">
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="heart" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.title') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('saved_searches.helper') }}</flux:text>
    </section>

    <section class="grid gap-2 sm:grid-cols-5">
        @foreach([
            'total' => $this->summary['total'],
            'active' => $this->summary['active'],
            'new_matches' => $this->summary['new'],
            'price_drops' => $this->summary['price_drops'],
            'available_again' => $this->summary['available_again'],
        ] as $key => $count)
            <div class="rounded-lg border border-zinc-200 bg-white px-3 py-3 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">{{ __('saved_searches.summary.'.$key) }}</div>
                <div class="text-xl font-semibold text-zinc-950 dark:text-zinc-50">{{ $count }}</div>
            </div>
        @endforeach
    </section>

    <div wire:loading.delay class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
        {{ __('saved_searches.loading') }}
    </div>

    <section class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="heart" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('saved_searches.sections.all') }}</span>
                </span>
            </flux:heading>
            <flux:button href="{{ route('search.index', ['locale' => app()->getLocale()]) }}" size="sm" variant="primary" icon="magnifying-glass" wire:navigate>
                {{ __('saved_searches.empty.button') }}
            </flux:button>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            @forelse($this->cards as $card)
                <livewire:saved-searches.saved-search-card :card="$card" :key="'saved-search-card-'.$card['id']" />
            @empty
                <flux:card class="sm:col-span-2">
                    <div class="space-y-3 text-center">
                        <div class="mx-auto flex size-11 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                            <flux:icon name="bookmark" class="size-6" />
                        </div>
                        <div class="space-y-1">
                            <flux:heading size="lg">
                                <span class="inline-flex min-w-0 items-center gap-2">
                                    <flux:icon name="heart" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('saved_searches.empty.title') }}</span>
                                </span>
                            </flux:heading>
                            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('saved_searches.empty.text') }}</flux:text>
                        </div>
                        <flux:button href="{{ route('search.index', ['locale' => app()->getLocale()]) }}" variant="primary" wire:navigate icon="magnifying-glass">
                            {{ __('saved_searches.empty.button') }}
                        </flux:button>
                    </div>
                </flux:card>
            @endforelse
        </div>
    </section>
</div>
