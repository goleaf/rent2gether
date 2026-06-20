<flux:card class="space-y-4">
    @if(! empty($card['listing_card']))
        <x-listings.card :card="$card['listing_card']" card-variant="favorite" embedded :show-actions="false" />
    @else
        <div class="flex gap-3">
            <a href="{{ $card['url'] }}" wire:navigate class="block size-24 shrink-0 overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-900">
                @if($card['image'])
                    <img
                        src="{{ $card['image'] }}"
                        alt="{{ $card['image_alt'] }}"
                        width="160"
                        height="160"
                        loading="lazy"
                        decoding="async"
                        class="size-full object-cover"
                    />
                @else
                    <span class="flex size-full items-center justify-center text-zinc-300 dark:text-zinc-700">
                        <flux:icon name="photo" class="size-8" />
                    </span>
                @endif
            </a>

            <div class="min-w-0 flex-1 space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <a href="{{ $card['url'] }}" wire:navigate>
                            <flux:heading size="sm" class="line-clamp-2 hover:text-emerald-700 dark:hover:text-emerald-300">
                                {{ $card['title'] }}
                            </flux:heading>
                        </a>
                        <flux:text size="sm" class="truncate text-zinc-500">{{ $card['location'] }}</flux:text>
                    </div>

                    <flux:button
                        type="button"
                        size="sm"
                        variant="ghost"
                        icon="heart"
                        wire:click="remove"
                        wire:confirm="{{ __('favorites.remove_confirmation') }}"
                        aria-label="{{ __('favorites.remove') }}"
                    />
                </div>

                <div class="flex flex-wrap gap-1.5">
                    <flux:badge size="sm">{{ $card['room_type'] }}</flux:badge>
                    <flux:badge size="sm">{{ $card['sleeping_place_type'] }}</flux:badge>
                    <flux:badge size="sm">{{ $card['priority_label'] }}</flux:badge>
                    <flux:badge size="sm">{{ $card['decision_status_label'] }}</flux:badge>
                </div>
            </div>
        </div>
    @endif

    @if(! empty($card['listing_card']))
        <div class="flex flex-wrap gap-1.5">
            <flux:badge size="sm">{{ $card['priority_label'] }}</flux:badge>
            <flux:badge size="sm">{{ $card['decision_status_label'] }}</flux:badge>
        </div>
    @endif

    <div class="grid grid-cols-2 gap-2 text-sm">
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-zinc-500">{{ __('favorites.current_price') }}</div>
            <div class="font-medium text-zinc-950 dark:text-zinc-50">{{ $card['price_per_night'] }}</div>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-zinc-500">{{ __('favorites.deposit') }}</div>
            <div class="font-medium text-zinc-950 dark:text-zinc-50">{{ $card['deposit'] }}</div>
        </div>
        @if($card['total_price'])
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-zinc-500">{{ __('favorites.current_total') }}</div>
                <div class="font-medium text-zinc-950 dark:text-zinc-50">{{ $card['total_price'] }}</div>
            </div>
        @endif
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-zinc-500">{{ __('favorites.rating') }}</div>
            <div class="font-medium text-zinc-950 dark:text-zinc-50">{{ $card['rating'] }}</div>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        <flux:badge color="{{ $card['availability_state'] === 'available' || $card['availability_state'] === 'available_again' ? 'green' : ($card['availability_state'] === 'needs_check' ? 'zinc' : 'amber') }}">
            {{ __('favorites.availability_statuses.'.$card['availability_state']) }}
        </flux:badge>

        <flux:badge color="{{ $card['price_state'] === 'dropped' ? 'green' : ($card['price_state'] === 'increased' ? 'amber' : 'zinc') }}">
            @if($card['price_state'] === 'dropped' && $card['price_change'])
                {{ __('favorites.price_dropped_amount', ['amount' => $card['price_change']]) }}
            @elseif($card['price_state'] === 'increased' && $card['price_change'])
                {{ __('favorites.price_increased_amount', ['amount' => $card['price_change']]) }}
            @else
                {{ __('favorites.price_statuses.'.$card['price_state']) }}
            @endif
        </flux:badge>
    </div>

    @if($card['note'])
        <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-700 dark:border-zinc-800 dark:text-zinc-300">
            {{ $card['note'] }}
        </div>
    @endif

    <div class="text-sm text-zinc-500">{{ $card['dates'] }}</div>

    <div class="grid grid-cols-2 gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800 sm:flex sm:flex-wrap">
        <flux:button href="{{ $card['url'] }}" size="sm" variant="primary" icon="arrow-right" wire:navigate>
            {{ __('favorites.open_place') }}
        </flux:button>

        @if($card['book_url'])
            <flux:button href="{{ $card['book_url'] }}" size="sm" variant="ghost" icon="calendar-days" wire:navigate>
                {{ __('favorites.book') }}
            </flux:button>
        @endif

        <flux:button type="button" size="sm" variant="{{ $selectedForCompare ? 'primary' : 'ghost' }}" icon="scale" wire:click="toggleCompare">
            {{ __('favorites.compare') }}
        </flux:button>

        @if(! empty($card['listing_card']))
            <flux:button
                type="button"
                size="sm"
                variant="ghost"
                icon="heart"
                wire:click="remove"
                wire:confirm="{{ __('favorites.remove_confirmation') }}"
            >
                {{ __('favorites.remove') }}
            </flux:button>
        @endif

        @if(in_array($card['availability_state'], ['unavailable', 'partial', 'needs_check'], true))
            <livewire:waitlist.join-waitlist-button
                :sleeping-place-id="$card['place_id']"
                :check-in="$card['check_in'] ?? ''"
                :check-out="$card['check_out'] ?? ''"
                :guests-count="$card['guests_count'] ?? 1"
                source="favorite"
                :key="'favorite-waitlist-'.$card['favorite_id'].'-'.$card['check_in'].'-'.$card['check_out']"
            />
        @endif
    </div>
</flux:card>
