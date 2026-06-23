<div class="space-y-2">
        <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('search.city_autocomplete.label') }}</span>
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
            :id="$inputId"
            type="search" icon="map-pin"
            clearable
            wire:model.live.debounce.500ms="query"
            :placeholder="__('search.city_autocomplete.placeholder')"
            container:class="max-h-80"
            >
        @foreach($results as $result)
            <flux:autocomplete.item
                wire:key="city-result-{{ $result['id'] }}"
                wire:click="selectCity({{ $result['id'] }})"
            >
                {{ $result['country'] ? $result['name'].', '.$result['country'] : $result['name'] }}
            </flux:autocomplete.item>
        @endforeach
    </flux:autocomplete>
        <flux:error name="query" />
    </flux:field>

    <div
        wire:loading.delay
        wire:target="query"
        class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-600 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
    >
        {{ __('search.city_autocomplete.loading') }}
    </div>

    @if($this->hasEnoughCharacters && $isOpen && $results === [])
        <div
            wire:loading.remove
            wire:target="query"
            class="rounded-lg border border-zinc-200 bg-white px-3 py-4 text-sm text-zinc-600 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
        >
            <p class="font-medium text-zinc-900 dark:text-zinc-100">
                {{ __('search.city_autocomplete.no_results') }}
            </p>
            <p class="mt-1">
                {{ __('search.city_autocomplete.no_results_text') }}
            </p>
        </div>
    @endif
</div>
