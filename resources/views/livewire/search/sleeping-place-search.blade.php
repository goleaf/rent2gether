@php
    $results = $this->searchResults;
    $money = static fn (float|int|string $amount, string $currency): string => \Illuminate\Support\Number::currency((float) $amount, $currency, app()->getLocale());
    $saveSearchCityId = ctype_digit((string) $city) ? (int) $city : null;
@endphp

<div class="min-h-screen bg-zinc-50 pb-24 dark:bg-zinc-950">
    <section class="mx-auto max-w-6xl space-y-4 px-4 py-4">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('search.title') }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('search.helper') }}</flux:text>
        </div>

        <flux:card class="space-y-4">
            <div class="space-y-3">
                <flux:field>
                    <flux:label>{{ __('search.fields.city') }}</flux:label>
                    <div class="relative">
                        <flux:input
                            type="search"
                            icon="map-pin"
                            autocomplete="off"
                            wire:model.live.debounce.500ms="cityQuery"
                            placeholder="{{ __('search.placeholders.city') }}"
                        />

                        @if($cityQuery !== '')
                            <button
                                type="button"
                                wire:click="clearCity"
                                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md px-2 py-1 text-xs text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                            >
                                {{ __('search.actions.clear_city') }}
                            </button>
                        @endif
                    </div>
                    <flux:description>{{ __('search.city_autocomplete.helper') }}</flux:description>
                </flux:field>

                <div wire:loading.delay wire:target="cityQuery" class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                    {{ __('search.city_autocomplete.loading') }}
                </div>

                @if($cityOpen && \Illuminate\Support\Str::length(\App\Support\Geo\GeoNameNormalizer::normalize($cityQuery)) >= 2)
                    <div wire:loading.remove wire:target="cityQuery" class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                        @if($this->cityOptions === [])
                            <div class="px-3 py-4 text-sm text-zinc-600 dark:text-zinc-300">
                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ __('search.city_autocomplete.no_results') }}</p>
                                <p class="mt-1">{{ __('search.city_autocomplete.no_results_text') }}</p>
                            </div>
                        @else
                            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach($this->cityOptions as $cityOption)
                                    <li wire:key="search-city-{{ $cityOption['id'] }}">
                                        <button
                                            type="button"
                                            class="flex min-h-12 w-full items-center justify-between gap-3 px-3 py-3 text-left text-sm hover:bg-zinc-50 focus:bg-zinc-50 focus:outline-none dark:hover:bg-zinc-800 dark:focus:bg-zinc-800"
                                            wire:click="selectCity({{ $cityOption['id'] }})"
                                        >
                                            <span class="min-w-0">
                                                <span class="block truncate font-medium text-zinc-900 dark:text-zinc-100">{{ $cityOption['name'] }}</span>
                                                @if($cityOption['country'])
                                                    <span class="block truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $cityOption['country'] }}</span>
                                                @endif
                                            </span>
                                            <span class="shrink-0 text-xs text-zinc-400">{{ __('search.city_autocomplete.choose') }}</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <flux:field>
                    <flux:label>{{ __('search.fields.check_in') }}</flux:label>
                    <flux:input type="date" min="{{ now()->toDateString() }}" wire:model.change="checkIn" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('search.fields.check_out') }}</flux:label>
                    <flux:input type="date" min="{{ $checkIn ?: now()->addDay()->toDateString() }}" wire:model.change="checkOut" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('search.fields.guests') }}</flux:label>
                    <flux:input type="number" min="1" inputmode="numeric" wire:model.change="guestsCount" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('search.fields.sort') }}</flux:label>
                    <flux:select wire:model.change="sort">
                        @foreach($this->sortOptions() as $value => $label)
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
                    <flux:badge color="blue">{{ trans_choice('search.summary.nights', $this->nights, ['count' => $this->nights]) }}</flux:badge>
                    <flux:badge>{{ trans_choice('search.summary.calendar_days', $this->calendarDays, ['count' => $this->calendarDays]) }}</flux:badge>
                </div>
            @endif

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <flux:button type="button" variant="primary" class="w-full sm:w-auto" wire:click="$set('filtersOpen', true)">
                    {{ __('search.actions.open_filters', ['count' => $this->activeFilterCount()]) }}
                </flux:button>

                @if($this->activeFilterCount() > 0)
                    <flux:button type="button" variant="ghost" class="w-full sm:w-auto" wire:click="clearFilters">
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
    </section>

    <section class="mx-auto max-w-6xl px-4">
        <main class="min-w-0 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ trans_choice('search.summary.showing_results', $results['showing'], ['count' => $results['showing']]) }}
                </flux:text>

                <div wire:loading.delay wire:target="cityQuery,district,checkIn,checkOut,guestsCount,priceMin,priceMax,currency,propertyType,roomType,sleepingPlaceType,roomGenderPolicy,sort" class="text-sm text-zinc-500">
                    {{ __('search.summary.updating') }}
                </div>
            </div>

            @if($results['cards'] === [])
                <flux:card class="space-y-4 text-center">
                    <flux:icon name="magnifying-glass" class="mx-auto size-10 text-zinc-300 dark:text-zinc-700" />
                    <div class="space-y-1">
                        <flux:heading size="lg">{{ __('search.empty.title') }}</flux:heading>
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
                        <flux:button type="button" variant="primary" class="w-full data-loading:opacity-70" wire:click="loadMore">
                            <span wire:loading.remove wire:target="loadMore">{{ __('search.actions.load_more') }}</span>
                            <span wire:loading wire:target="loadMore">{{ __('search.actions.loading_more') }}</span>
                        </flux:button>
                    </div>
                @endif
            @endif
        </main>
    </section>

    @if($filtersOpen)
        <div class="fixed inset-0 z-40">
            <button type="button" class="absolute inset-0 bg-zinc-950/50" wire:click="$set('filtersOpen', false)" aria-label="{{ __('search.filters_sheet.close') }}"></button>

            <section class="absolute inset-x-0 bottom-0 max-h-[86vh] overflow-y-auto rounded-t-xl bg-white p-4 shadow-2xl dark:bg-zinc-950 sm:bottom-4 sm:left-auto sm:right-4 sm:top-4 sm:w-full sm:max-w-sm sm:rounded-xl">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <flux:heading size="lg">{{ __('search.filters') }}</flux:heading>
                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('search.filters_sheet.helper') }}</flux:text>
                    </div>
                    <flux:button type="button" variant="ghost" size="sm" wire:click="$set('filtersOpen', false)">
                        {{ __('search.filters_sheet.close') }}
                    </flux:button>
                </div>

                <div class="space-y-5 pb-4">
                    @include('livewire.search.partials.sleeping-place-filters')
                </div>

                <div class="sticky bottom-0 -mx-4 border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                    <flux:button type="button" variant="primary" class="w-full" wire:click="$set('filtersOpen', false)">
                        {{ __('search.actions.show_results', ['count' => $results['showing']]) }}
                    </flux:button>
                </div>
            </section>
        </div>
    @endif
</div>
