<div class="grid gap-4">
    <div class="space-y-2">
                <flux:field>
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $countryLabel ?? __('geo.fields.country') }}</span>
                </span>
            </flux:label>
            <flux:description>
                        <span class="inline-flex min-w-0 items-start gap-1.5">
                            <flux:icon name="information-circle" variant="mini" class="mt-0.5 size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">
                                {{ $countryDescription ?? __('geo.helpers.country') }}
                            </span>
                        </span>
                    </flux:description>
            <flux:autocomplete
                type="search"
                clearable icon="map-pin"
                wire:model.live.debounce.500ms="countryQuery"
                placeholder="{{ $countryPlaceholder ?? __('geo.placeholders.country') }}"
                container:class="max-h-80"
                >
            @foreach($this->countryResults as $result)
                <flux:autocomplete.item
                    wire:key="{{ $autocompleteKey ?? 'geo' }}-country-{{ $result['id'] }}"
                    wire:click="selectCountry({{ $result['id'] }})"
                >
                    {{ $result['code'] ? $result['name'].' · '.$result['code'] : $result['name'] }}
                </flux:autocomplete.item>
            @endforeach
        </flux:autocomplete>
            <flux:error name="countryQuery" />
        </flux:field>
        <flux:error name="countryId" />

        <div
            wire:loading.delay
            wire:target="countryQuery"
            class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300"
        >
            {{ __('geo.loading.country') }}
        </div>

        @if($countrySearchOpen && $this->countryQueryHasEnoughCharacters && $this->countryResults === [])
            <div
                wire:loading.remove
                wire:target="countryQuery"
                class="rounded-lg border border-zinc-200 px-3 py-4 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300"
            >
                {{ __('geo.empty.country') }}
            </div>
        @endif
    </div>

    <div class="space-y-2">
                <flux:field>
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $cityLabel ?? __('geo.fields.city') }}</span>
                </span>
            </flux:label>
            <flux:description>
                        <span class="inline-flex min-w-0 items-start gap-1.5">
                            <flux:icon name="information-circle" variant="mini" class="mt-0.5 size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">
                                {{ $cityDescription ?? ($this->cityAutocompleteDisabled ? __('geo.helpers.city_disabled') : __('geo.helpers.city')) }}
                            </span>
                        </span>
                    </flux:description>
            <flux:autocomplete
                type="search"
                clearable icon="map-pin"
                wire:key="{{ $autocompleteKey ?? 'geo' }}-city-{{ $countryId ?: 'none' }}"
                wire:model.live.debounce.500ms="cityQuery"
                placeholder="{{ $cityPlaceholder ?? ($this->cityAutocompleteDisabled ? __('geo.placeholders.city_disabled') : __('geo.placeholders.city')) }}"
                :disabled="$this->cityAutocompleteDisabled"
                container:class="max-h-80"
                >
            @foreach($this->cityResults as $result)
                <flux:autocomplete.item
                    wire:key="{{ $autocompleteKey ?? 'geo' }}-city-result-{{ $result['id'] }}"
                    wire:click="selectCity({{ $result['id'] }})"
                >
                    {{ ($result['region'] ?: $result['country']) ? $result['name'].', '.($result['region'] ?: $result['country']) : $result['name'] }}
                </flux:autocomplete.item>
            @endforeach
        </flux:autocomplete>
            <flux:error name="cityQuery" />
        </flux:field>
        <flux:error name="cityId" />

        <div
            wire:loading.delay
            wire:target="cityQuery"
            class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300"
        >
            {{ __('geo.loading.city') }}
        </div>

        @if(! $this->cityAutocompleteDisabled && $citySearchOpen && $this->cityQueryHasEnoughCharacters && $this->cityResults === [])
            <div
                wire:loading.remove
                wire:target="cityQuery"
                class="rounded-lg border border-zinc-200 px-3 py-4 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300"
            >
                {{ __('geo.empty.city') }}
            </div>
        @endif
    </div>
</div>
