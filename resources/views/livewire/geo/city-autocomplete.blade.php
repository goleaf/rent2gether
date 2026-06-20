<div class="space-y-2">
    <flux:field>
        <flux:label>{{ __('search.city_autocomplete.label') }}</flux:label>
        <flux:input
            :id="$inputId"
            type="search"
            icon="map-pin"
            autocomplete="off"
            wire:model.live.debounce.500ms="query"
            :placeholder="__('search.city_autocomplete.placeholder')"
        />
        <flux:description>{{ __('search.city_autocomplete.helper') }}</flux:description>
    </flux:field>

    <div
        wire:loading.delay
        wire:target="query"
        class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-600 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
    >
        {{ __('search.city_autocomplete.loading') }}
    </div>

    @if($this->hasEnoughCharacters && $isOpen)
        @php($results = $this->results)

        <div
            wire:loading.remove
            wire:target="query"
            class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
        >
            @if($results === [])
                <div class="px-3 py-4 text-sm text-zinc-600 dark:text-zinc-300">
                    <p class="font-medium text-zinc-900 dark:text-zinc-100">
                        {{ __('search.city_autocomplete.no_results') }}
                    </p>
                    <p class="mt-1">
                        {{ __('search.city_autocomplete.no_results_text') }}
                    </p>
                </div>
            @else
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($results as $result)
                        <li wire:key="city-result-{{ $result['id'] }}">
                            <button
                                type="button"
                                class="flex min-h-12 w-full items-center justify-between gap-3 px-3 py-3 text-left text-sm hover:bg-zinc-50 focus:bg-zinc-50 focus:outline-none dark:hover:bg-zinc-800 dark:focus:bg-zinc-800"
                                wire:click="selectCity({{ $result['id'] }})"
                            >
                                <span class="min-w-0">
                                    <span class="block truncate font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ $result['name'] }}
                                    </span>
                                    @if($result['country'])
                                        <span class="block truncate text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $result['country'] }}
                                        </span>
                                    @endif
                                </span>

                                <span class="shrink-0 text-xs text-zinc-400">
                                    {{ __('search.city_autocomplete.choose') }}
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
</div>
