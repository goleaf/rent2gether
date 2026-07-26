<x-ui.page>
    <x-ui.section>
        <div class="space-y-1">
            <flux:heading size="xl" level="1">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="magnifying-glass" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('search.title') }}</span>
                </span>
            </flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('search.helper') }}</flux:text>
        </div>

        <flux:card class="space-y-4">
            <div class="space-y-3">
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('search.fields.city') }}</span>
                        </span>
                    </flux:label>
                    <flux:description>
                        <span class="inline-flex min-w-0 items-start gap-1.5">
                            <flux:icon name="information-circle" variant="mini" class="mt-0.5 size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">
                                {{ __('search.city_autocomplete.helper') }}
                            </span>
                        </span>
                    </flux:description>
                    <flux:autocomplete
                        type="search" icon="map-pin"
                        clearable
                        wire:model.live.debounce.500ms="cityQuery"
                        placeholder="{{ __('search.placeholders.city') }}"
                        container:class="max-h-80"
                        >
                    @foreach($this->cityOptions as $cityOption)
                        <flux:autocomplete.item
                            wire:key="search-city-{{ $cityOption['id'] }}"
                            wire:click="selectCity({{ $cityOption['id'] }})"
                        >
                            {{ $cityOption['country'] ? $cityOption['name'].', '.$cityOption['country'] : $cityOption['name'] }}
                        </flux:autocomplete.item>
                    @endforeach
                </flux:autocomplete>
                    <flux:error name="cityQuery" />
                </flux:field>

                <div wire:loading.delay wire:target="cityQuery" class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                    {{ __('search.city_autocomplete.loading') }}
                </div>

                @if($cityOpen && $cityHasEnoughCharacters && $this->cityOptions === [])
                    <div wire:loading.remove wire:target="cityQuery" class="rounded-lg border border-zinc-200 bg-white px-3 py-4 text-sm text-zinc-600 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ __('search.city_autocomplete.no_results') }}</p>
                        <p class="mt-1">{{ __('search.city_autocomplete.no_results_text') }}</p>
                    </div>
                @endif
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.fields.check_in') }}</span>
    </span>
</flux:label>
                    <flux:input type="date" min="{{ now()->toDateString() }}" wire:model.change="checkIn" icon="calendar-days" />
                </flux:field>

                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.fields.check_out') }}</span>
    </span>
</flux:label>
                    <flux:input type="date" min="{{ $checkIn ?: now()->addDay()->toDateString() }}" wire:model.change="checkOut" icon="calendar-days" />
                </flux:field>

                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.fields.guests') }}</span>
    </span>
</flux:label>
                    <flux:input type="number" min="1" inputmode="numeric" wire:model.change="guestsCount" icon="users" />
                </flux:field>

                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.fields.sort') }}</span>
    </span>
</flux:label>
                    <flux:select wire:model.change="sort">
                        @foreach($this->sortOptions as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            @if($this->dateWarning)
                <flux:callout color="amber" icon="exclamation-triangle">
                    <flux:callout.text>{{ $this->dateWarning }}</flux:callout.text>
                </flux:callout>
            @elseif($this->nights > 0)
                <div class="flex flex-wrap gap-2 text-sm">
                    <flux:badge color="blue" icon="calendar-days">{{ trans_choice('search.summary.nights', $this->nights, ['count' => $this->nights]) }}</flux:badge>
                    <flux:badge icon="calendar-days">{{ trans_choice('search.summary.calendar_days', $this->calendarDays, ['count' => $this->calendarDays]) }}</flux:badge>
                </div>
            @endif

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <flux:button type="button" variant="primary" class="w-full sm:w-auto" wire:click="$set('filtersOpen', true)" icon="funnel">
                    {{ __('search.actions.open_filters', ['count' => $this->activeFilterCount()]) }}
                </flux:button>

                @if($this->activeFilterCount() > 0)
                    <flux:button type="button" variant="ghost" class="w-full sm:w-auto" wire:click="clearFilters" icon="x-mark">
                        {{ __('search.actions.clear_all') }}
                    </flux:button>
                @endif
            </div>

            <livewire:saved-searches.save-search-button
                :city-id="$saveSearchCityId"
                :city-name="$cityQuery"
                :district="$district"
                :check-in="$checkIn"
                :check-out="$checkOut"
                :guests-count="$guestsCount"
                :price-min="$priceMin"
                :price-max="$priceMax"
                :currency="$currency ?: 'EUR'"
                :room-type="$roomType"
                :sleeping-place-type="$sleepingPlaceType"
                :instant-booking="$instantBooking"
                :verified-host="$verifiedHost"
                :has-reviews="$hasReviews"
                :require-wifi="$wifi"
                :require-kitchen="$kitchen"
                :require-washing-machine="$washingMachine"
                :require-locker="$locker"
                :require-workspace="$workspace"
                :key="'save-search-'.$city.'-'.$district.'-'.$checkIn.'-'.$checkOut.'-'.$priceMin.'-'.$priceMax.'-'.$instantBooking"
            />
        </flux:card>
    </x-ui.section>

    <x-ui.section>
        <main class="min-w-0 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    @if($results['total_is_exact'])
                        {{ trans_choice('search.summary.matched_results', $results['total'], ['count' => $results['total']]) }}
                    @else
                        {{ __('search.summary.matched_results_lower_bound', ['count' => $results['total']]) }}
                    @endif
                </flux:text>

                <div wire:loading.delay wire:target="cityQuery,district,checkIn,checkOut,guestsCount,priceMin,priceMax,currency,propertyType,roomType,sleepingPlaceType,roomGenderPolicy,sort" class="text-sm text-zinc-500">
                    {{ __('search.summary.updating') }}
                </div>
            </div>

            @if($results['cards'] === [])
                <flux:card class="space-y-4 text-center">
                    <flux:icon name="magnifying-glass" class="mx-auto size-10 text-zinc-300 dark:text-zinc-700" />
                    <div class="space-y-1">
                        <flux:heading size="lg">
                            <span class="inline-flex min-w-0 items-center gap-2">
                                <flux:icon name="magnifying-glass" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('search.empty.title') }}</span>
                            </span>
                        </flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('search.empty.helper') }}</flux:text>
                    </div>
                    <div class="grid gap-2 text-left text-sm text-zinc-600 dark:text-zinc-300 sm:grid-cols-2">
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">{{ __('search.empty.change_dates') }}</div>
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">{{ __('search.empty.increase_budget') }}</div>
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">{{ __('search.empty.nearby_cities') }}</div>
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">{{ __('search.empty.fewer_filters') }}</div>
                    </div>
                </flux:card>
            @else
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($results['cards'] as $card)
                        <x-listings.card
                            :card="$card"
                            card-variant="search"
                            wire:key="sleeping-place-card-{{ $card['sleeping_place_id'] }}"
                        />
                    @endforeach
                </div>

                @if($results['has_more'])
                    <div class="py-2">
                        <flux:button type="button" variant="primary" class="w-full data-loading:opacity-70" wire:click="loadMore" icon="arrow-down">
                            <span wire:loading.remove wire:target="loadMore">{{ __('search.actions.load_more') }}</span>
                            <span wire:loading wire:target="loadMore">{{ __('search.actions.loading_more') }}</span>
                        </flux:button>
                    </div>
                @endif
            @endif
        </main>
    </x-ui.section>

    @if($filtersOpen)
        <div class="fixed inset-0 z-40">
            <flux:button type="button" variant="ghost" class="absolute inset-0 h-auto w-full rounded-none bg-zinc-950/50 p-0 hover:bg-zinc-950/50 dark:hover:bg-zinc-950/50" wire:click="$set('filtersOpen', false)" aria-label="{{ __('search.filters_sheet.close') }}" />

            <section class="absolute inset-x-0 bottom-0 max-h-[86vh] overflow-y-auto rounded-t-xl bg-white p-4 shadow-2xl dark:bg-zinc-950 sm:bottom-4 sm:left-auto sm:right-4 sm:top-4 sm:w-full sm:max-w-sm sm:rounded-xl">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <flux:heading size="lg">
                            <span class="inline-flex min-w-0 items-center gap-2">
                                <flux:icon name="magnifying-glass" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('search.filters') }}</span>
                            </span>
                        </flux:heading>
                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('search.filters_sheet.helper') }}</flux:text>
                    </div>
                    <flux:button type="button" variant="ghost" size="sm" wire:click="$set('filtersOpen', false)" icon="x-mark">
                        {{ __('search.filters_sheet.close') }}
                    </flux:button>
                </div>

                <div class="space-y-5 pb-4">
                    @include('livewire.search.partials.sleeping-place-filters')
                </div>

                <div class="sticky bottom-0 -mx-4 border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                    <flux:button type="button" variant="primary" class="w-full" wire:click="$set('filtersOpen', false)" icon="magnifying-glass">
                        {{ __('search.actions.show_results', ['count' => $results['total']]) }}
                    </flux:button>
                </div>
            </section>
        </div>
    @endif
</x-ui.page>
