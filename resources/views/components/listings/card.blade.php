<article
    {{ $attributes->merge([
        'class' => $embedded
            ? 'overflow-hidden rounded-lg bg-white dark:bg-zinc-900'
            : ($isCompact
            ? 'overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900'
            : 'overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900'),
    ]) }}
    data-listing-card
    data-listing-card-variant="{{ $cardVariant }}"
>
    <div class="relative">
        <a href="{{ $card['url'] }}" wire:navigate class="block bg-zinc-100 dark:bg-zinc-800" aria-label="{{ __('listing_card.open_named', ['title' => $card['title']]) }}">
            @if($card['primary_photo_url'])
                <img
                    src="{{ $card['primary_photo_url'] }}"
                    alt="{{ $card['image_alt'] }}"
                    width="{{ $isCompact ? 360 : 720 }}"
                    height="{{ $isCompact ? 240 : 480 }}"
                    loading="lazy"
                    decoding="async"
                    class="{{ $isCompact ? 'h-36' : 'h-44' }} w-full object-cover"
                />
            @else
                <span class="{{ $isCompact ? 'h-36' : 'h-44' }} flex w-full items-center justify-center text-zinc-300 dark:text-zinc-700">
                    <flux:icon name="home" variant="outline" class="size-10" />
                    <span class="sr-only">{{ __('listing_card.empty_photo_alt') }}</span>
                </span>
            @endif
        </a>

        <div class="absolute left-2 top-2 flex max-w-[70%] flex-wrap gap-1.5">
            <x-listings.card-badges :badges="array_slice($card['badges'] ?? [], 0, $isCompact ? 2 : 3)" />
        </div>

        @if($showActions && ! in_array($cardVariant, ['host-preview', 'waitlist'], true))
            <div class="absolute right-2 top-2 flex flex-col gap-1.5">
                <livewire:favorites.favorite-toggle
                    :sleeping-place-id="$placeId"
                    :source="$cardVariant"
                    :check-in="$card['check_in_date'] ?? request('in', '')"
                    :check-out="$card['check_out_date'] ?? request('out', '')"
                    :guests-count="$card['guests_count'] ?? request('guests', 1)"
                    :key="'listing-favorite-'.$cardVariant.'-'.$placeId.'-'.($card['nights_count'] ?? 'none')"
                />
            </div>
        @endif
    </div>

    <div class="{{ $isCompact ? 'space-y-2 p-3' : 'space-y-3 p-4' }}">
        <div class="space-y-1">
            <div class="flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                <flux:icon name="map-pin" variant="mini" class="size-3.5 shrink-0" />
                <span class="truncate">{{ $card['location'] }}</span>
            </div>

            <a href="{{ $card['url'] }}" wire:navigate>
                <flux:heading size="sm" class="line-clamp-2 hover:text-emerald-700 dark:hover:text-emerald-300">
                    {{ $card['title'] }}
                </flux:heading>
            </a>

            @if(! $isCompact && $card['summary'])
                <flux:text size="sm" class="line-clamp-2 text-zinc-600 dark:text-zinc-400">
                    {{ $card['summary'] }}
                </flux:text>
            @endif

            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ $card['room_type'] }} · {{ $card['sleeping_place_type'] }} · {{ $card['room_gender_policy'] }}
            </flux:text>

            @if(! $isCompact)
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                    {{ $card['people_in_room_summary'] }}
                </flux:text>
            @endif
        </div>

        @if(! empty($card['badges']) && ! $isCompact)
            <div class="flex flex-wrap gap-1.5">
                <x-listings.card-badges :badges="array_slice($card['badges'], 3, 3)" />
                @if($card['rating_average'])
                    <flux:badge size="sm" icon="star">
                        {{ __('listing_card.rating_summary', ['rating' => number_format((float) $card['rating_average'], 1), 'count' => $card['reviews_count']]) }}
                    </flux:badge>
                @endif
            </div>
        @endif

        <x-listings.card-price :card="$card" />

        @if(! empty($card['hints']))
            <div class="flex flex-wrap gap-1.5" aria-label="{{ __('guest_hints.title') }}">
                @forelse(array_slice($card['hints'], 0, $isCompact ? 2 : 3) as $hint)
                    <flux:badge size="sm" color="{{ $hintColor($hint) }}" icon="home-modern">
                        {{ $hint['text'] }}
                    </flux:badge>
                @empty
                @endforelse
            </div>
        @endif

        @if(! empty($card['compatibility_score']))
            <div class="flex flex-wrap items-center gap-2">
                <flux:badge
                    size="sm"
                    color="{{ ($card['fit_status'] ?? null) === 'not_suitable' ? 'red' : (in_array(($card['fit_status'] ?? null), ['attention', 'uncomfortable'], true) ? 'yellow' : 'green') }}"
                 icon="exclamation-triangle">
                    {{ __('compatibility.title') }} · {{ __('compatibility.badge_short', ['score' => $card['compatibility_score']]) }}
                </flux:badge>

                @if(! $isCompact && ! empty($card['fit_status']))
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ __('compatibility.fit_statuses.'.$card['fit_status']) }}
                    </span>
                @endif
            </div>
        @endif

        @if(! $isCompact)
            <x-listings.card-amenities :amenities="$card['key_amenities'] ?? []" />
            <x-listings.card-rules :rules="$card['key_rules'] ?? []" />
        @endif

        @if(! empty($card['warnings']) && ! $isCompact)
            <div class="space-y-1">
                @foreach(array_slice($card['warnings'], 0, 2) as $warning)
                    <p class="text-xs text-amber-700 dark:text-amber-300">{{ $warning }}</p>
                @endforeach
            </div>
        @endif

        @if($showActions)
            <div class="grid grid-cols-2 gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800 sm:flex sm:flex-wrap">
                <flux:button href="{{ $card['url'] }}" size="sm" variant="primary" icon="eye" wire:navigate>
                    {{ __('listing_card.view_place') }}
                </flux:button>

                @if($card['is_available'] === false)
                    <livewire:waitlist.join-waitlist-button
                        :sleeping-place-id="$placeId"
                        :check-in="$card['check_in_date'] ?? request('in', '')"
                        :check-out="$card['check_out_date'] ?? request('out', '')"
                        :guests-count="$card['guests_count'] ?? request('guests', 1)"
                        :source="$cardVariant"
                        :key="'listing-waitlist-'.$cardVariant.'-'.$placeId.'-'.($card['nights_count'] ?? 'none')"
                    />
                @elseif($card['book_url'])
                    <flux:button href="{{ $card['book_url'] }}" size="sm" variant="ghost" icon="calendar-days" wire:navigate>
                        {{ __('listing_card.book') }}
                    </flux:button>
                @endif

                @if(! in_array($cardVariant, ['host-preview', 'waitlist'], true))
                    <livewire:listings.compare-toggle
                        :sleeping-place-id="$placeId"
                        :selected="(bool) ($card['is_in_comparison'] ?? false)"
                        :key="'listing-compare-'.$cardVariant.'-'.$placeId"
                    />
                @endif
            </div>
        @endif
    </div>
</article>
