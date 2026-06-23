<div class="space-y-5">
    <section class="grid gap-2 sm:grid-cols-5">
        <div class="rounded-lg border border-zinc-200 bg-white px-3 py-3 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500">{{ __('favorites.summary.total') }}</div>
            <div class="text-xl font-semibold text-zinc-950 dark:text-zinc-50">{{ $this->summary['total'] }}</div>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white px-3 py-3 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500">{{ __('favorites.summary.available') }}</div>
            <div class="text-xl font-semibold text-zinc-950 dark:text-zinc-50">{{ $this->summary['available'] }}</div>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white px-3 py-3 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500">{{ __('favorites.summary.price_changed') }}</div>
            <div class="text-xl font-semibold text-zinc-950 dark:text-zinc-50">{{ $this->summary['price_changed'] }}</div>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white px-3 py-3 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500">{{ __('favorites.summary.available_again') }}</div>
            <div class="text-xl font-semibold text-zinc-950 dark:text-zinc-50">{{ $this->summary['available_again'] }}</div>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white px-3 py-3 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500">{{ __('favorites.summary.reminders') }}</div>
            <div class="text-xl font-semibold text-zinc-950 dark:text-zinc-50">{{ $this->summary['reminders'] }}</div>
        </div>
    </section>

    <section class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="heart" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('favorites.collections') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('favorites.collections_helper') }}</flux:text>
            </div>
            <flux:button type="button" size="sm" variant="primary" icon="heart" wire:click="$set('createCollectionOpen', true)">
                {{ __('favorites.create_collection') }}
            </flux:button>
        </div>

        <livewire:favorites.favorite-collections-list />
    </section>

    @if($createCollectionOpen)
        <livewire:favorites.create-collection-sheet />
    @endif

    <div wire:loading.delay class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
        {{ __('favorites.loading') }}
    </div>

    <section class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="heart" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('favorites.sections.recent') }}</span>
                </span>
            </flux:heading>
            @if($this->summary['total'] > 0)
                <flux:button href="{{ route('favorites.index', ['locale' => app()->getLocale()]) }}" size="sm" variant="ghost" wire:navigate icon="heart">
                    {{ __('favorites.all') }}
                </flux:button>
            @endif
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            @forelse($this->recentCards as $card)
                <livewire:favorites.favorite-card :card="$card" :key="'favorite-recent-'.$card['id']" />
            @empty
                <flux:card class="sm:col-span-2">
                    <div class="space-y-3 text-center">
                        <div class="mx-auto flex size-11 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                            <flux:icon name="heart" class="size-6" />
                        </div>
                        <div class="space-y-1">
                            <flux:heading size="lg">
                                <span class="inline-flex min-w-0 items-center gap-2">
                                    <flux:icon name="heart" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('favorites.empty.title') }}</span>
                                </span>
                            </flux:heading>
                            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('favorites.empty.text') }}</flux:text>
                        </div>
                        <flux:button href="{{ route('search.index', ['locale' => app()->getLocale()]) }}" variant="primary" wire:navigate icon="magnifying-glass">
                            {{ __('favorites.empty.button') }}
                        </flux:button>
                    </div>
                </flux:card>
            @endforelse
        </div>
    </section>

    @foreach([
        'price_changed' => $this->priceChangedCards,
        'available_again' => $this->availableAgainCards,
        'reminders' => $this->reminderCards,
    ] as $section => $cards)
        @if($cards !== [])
            <section class="space-y-3">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="heart" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('favorites.sections.'.$section) }}</span>
                    </span>
                </flux:heading>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach($cards as $card)
                        <livewire:favorites.favorite-card :card="$card" :key="'favorite-'.$section.'-'.$card['id']" />
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach
</div>
