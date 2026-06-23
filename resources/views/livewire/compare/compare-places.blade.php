<x-ui.page>
    <section class="space-y-2">
        <flux:badge color="emerald" icon="check-circle">{{ __('decision.compare.eyebrow') }}</flux:badge>
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="scale" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('decision.compare.title') }}</span>
            </span>
        </flux:heading>
        <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">{{ __('decision.compare.helper') }}</flux:text>
    </section>

    <flux:card class="space-y-3">
        <div class="grid gap-3 sm:grid-cols-3">
            <flux:field>
                <flux:label>{{ __('listing.detail.booking.check_in') }}</flux:label>
                <flux:input type="date" min="{{ now()->toDateString() }}" wire:model.change="checkIn" icon="calendar-days" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('listing.detail.booking.check_out') }}</flux:label>
                <flux:input type="date" min="{{ $checkIn ?: now()->toDateString() }}" wire:model.change="checkOut" icon="calendar-days" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('listing.detail.booking.guests') }}</flux:label>
                <flux:input type="number" min="1" inputmode="numeric" wire:model.change="guestsCount" icon="users" />
            </flux:field>
        </div>

        <flux:text size="sm" class="text-zinc-500">{{ __('decision.compare.date_helper') }}</flux:text>

        @if($cards->isNotEmpty())
            <flux:button type="button" variant="ghost" icon="heart" wire:click="saveComparedToFavorites" wire:loading.attr="disabled">
                {{ __('favorites.save_compared') }}
            </flux:button>
        @endif
    </flux:card>

    <div wire:loading.delay wire:target="checkIn,checkOut,guestsCount,removePlace" class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
        {{ __('decision.common.updating') }}
    </div>

    @if($cards->isNotEmpty())
        <section class="overflow-x-auto pb-2" aria-label="{{ __('decision.compare.table_label') }}">
            <div class="grid min-w-[42rem] gap-3" style="grid-template-columns: repeat({{ max(1, $cards->count()) }}, minmax(10rem, 1fr));">
                @foreach($cards as $card)
                    <flux:card class="space-y-3">
                        @if(! empty($card['listing_card']))
                            <x-listings.card :card="$card['listing_card']" card-variant="comparison" embedded :show-actions="false" />
                        @else
                            <div class="space-y-1">
                                <a href="{{ $card['url'] }}" wire:navigate>
                                    <flux:heading size="sm" class="line-clamp-2 hover:text-emerald-700 dark:hover:text-emerald-300">
                                        {{ $card['title'] }}
                                    </flux:heading>
                                </a>
                                <flux:text size="sm" class="text-zinc-500">{{ $card['bed_type'] }}</flux:text>
                            </div>
                        @endif

                        <dl class="space-y-2 text-sm">
                            @foreach([
                                'price_night',
                                'total_price',
                                'deposit',
                                'people_in_room',
                                'room_type',
                                'locker',
                                'wifi',
                                'kitchen',
                                'cancellation',
                                'rating',
                                'compatibility_score',
                            ] as $field)
                                <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                                    <dt class="text-zinc-500">{{ __('decision.compare.fields.'.$field) }}</dt>
                                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $card[$field] }}</dd>
                                </div>
                            @endforeach
                        </dl>

                        <div class="space-y-2">
                            <flux:heading size="sm">
                                <span class="inline-flex min-w-0 items-center gap-2">
                                    <flux:icon name="exclamation-triangle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('decision.compare.fields.warnings') }}</span>
                                </span>
                            </flux:heading>
                            <div class="space-y-1">
                                @foreach($card['warnings'] as $warning)
                                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                                        {{ $warning }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                            <livewire:favorites.favorite-toggle
                                :sleeping-place-id="$card['id']"
                                source="comparison"
                                :check-in="$checkIn"
                                :check-out="$checkOut"
                                :guests-count="$guestsCount"
                                :key="'compare-favorite-'.$card['id'].'-'.$checkIn.'-'.$checkOut.'-'.$guestsCount"
                            />
                            <flux:button href="{{ $card['url'] }}" size="sm" variant="primary" wire:navigate icon="eye">
                                {{ __('app.actions.view') }}
                            </flux:button>
                            <flux:button type="button" size="sm" variant="ghost" wire:click="removePlace({{ $card['id'] }})" icon="trash">
                                {{ __('app.actions.remove') }}
                            </flux:button>
                            @if($card['available_for_dates'] === false)
                                <livewire:waitlist.join-waitlist-button
                                    :sleeping-place-id="$card['id']"
                                    :check-in="$checkIn"
                                    :check-out="$checkOut"
                                    :guests-count="$guestsCount"
                                    source="comparison"
                                    :key="'compare-waitlist-'.$card['id'].'-'.$checkIn.'-'.$checkOut.'-'.$guestsCount"
                                />
                            @endif
                        </div>
                    </flux:card>
                @endforeach
            </div>
        </section>
    @else
        <flux:card>
            <div class="space-y-3 text-center">
                <div class="mx-auto flex size-11 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                    <flux:icon name="scale" class="size-6" />
                </div>
                <div class="space-y-1">
                    <flux:heading size="lg">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="scale" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('decision.compare.empty_title') }}</span>
                        </span>
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('decision.compare.empty_helper') }}</flux:text>
                </div>
                <flux:button href="{{ route('favorites.index', ['locale' => app()->getLocale()]) }}" variant="primary" wire:navigate icon="heart">
                    {{ __('decision.compare.empty_action') }}
                </flux:button>
            </div>
        </flux:card>
    @endif
</x-ui.page>
