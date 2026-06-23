<div class="space-y-3">
    <div wire:loading.delay class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
        {{ __('saved_searches.loading_results') }}
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        @forelse($this->cards as $card)
            <div wire:key="saved-search-result-{{ $card['id'] }}" class="space-y-2">
                @if(! empty($card['listing_card']))
                    <x-listings.card :card="$card['listing_card']" card-variant="compact" />
                @else
                    <article class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="relative">
                            @if($card['image'])
                                <img
                                    src="{{ $card['image'] }}"
                                    alt="{{ $card['image_alt'] }}"
                                    width="640"
                                    height="420"
                                    loading="lazy"
                                    decoding="async"
                                    class="h-40 w-full bg-zinc-100 object-cover dark:bg-zinc-800"
                                />
                            @else
                                <div class="flex h-40 w-full items-center justify-center bg-zinc-100 dark:bg-zinc-800">
                                    <flux:icon name="home" class="size-10 text-zinc-300 dark:text-zinc-700" />
                                </div>
                            @endif

                            <div class="absolute right-2 top-2">
                                <livewire:favorites.favorite-toggle
                                    :sleeping-place-id="$card['place_id']"
                                    source="saved_search"
                                    :key="'saved-search-favorite-'.$card['place_id'].'-'.$card['id']"
                                />
                            </div>
                        </div>

                        <div class="space-y-3 p-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="map-pin" variant="mini" class="size-3.5 shrink-0" />
                                    <span class="truncate">{{ $card['location'] }}</span>
                                </div>
                                <flux:heading size="sm" class="line-clamp-2">
                                    <a href="{{ $card['url'] }}" wire:navigate class="hover:text-emerald-700 dark:hover:text-emerald-300">
                                        {{ $card['title'] }}
                                    </a>
                                </flux:heading>
                                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                                    {{ $card['room_type'] }} · {{ $card['sleeping_place_type'] }}
                                </flux:text>
                            </div>
                        </div>
                    </article>
                @endif

                <div class="rounded-lg border border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex flex-wrap gap-1.5">
                        <flux:badge size="sm" icon="heart">{{ __('saved_searches.result.match_score', ['score' => $card['match_score'] ?? 0]) }}</flux:badge>
                        <flux:badge size="sm" color="{{ $card['availability_state'] === 'available_again' ? 'blue' : ($card['availability_state'] === 'unavailable' ? 'red' : 'green') }}" icon="exclamation-triangle">
                            {{ __('saved_searches.availability.'.$card['availability_state']) }}
                        </flux:badge>
                        @if($card['price_state'] !== 'same')
                            <flux:badge size="sm" color="{{ $card['price_state'] === 'dropped' ? 'green' : 'amber' }}" icon="exclamation-triangle">
                                {{ __('saved_searches.price.'.$card['price_state'], ['amount' => $card['price_change']]) }}
                            </flux:badge>
                        @endif
                        <flux:badge size="sm" icon="heart">{{ $card['rating'] }}</flux:badge>
                    </div>
                </div>
            </div>
        @empty
            <flux:card class="sm:col-span-2">
                <div class="space-y-2 text-center">
                    <flux:heading size="lg">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="heart" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('saved_searches.no_results') }}</span>
                        </span>
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('saved_searches.no_results_helper') }}</flux:text>
                </div>
            </flux:card>
        @endforelse
    </div>

    @if(count($this->cards) >= $visibleCount)
        <flux:button type="button" variant="primary" class="w-full" wire:click="loadMore" icon="arrow-down">
            {{ __('saved_searches.load_more') }}
        </flux:button>
    @endif
</div>
