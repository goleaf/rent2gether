<section class="space-y-4" aria-labelledby="favorites-decision-title">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
            <flux:heading id="favorites-decision-title" size="lg">{{ __('decision.favorites.list_title') }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('decision.favorites.list_helper') }}</flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:button
                type="button"
                variant="primary"
                icon="scale"
                wire:click="compareSelected"
                wire:loading.attr="disabled"
                wire:target="compareSelected"
            >
                {{ __('decision.compare.action', ['count' => count($selectedForCompare)]) }}
            </flux:button>
        </div>
    </div>

    @error('selectedForCompare')
        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.text>{{ $message }}</flux:callout.text>
        </flux:callout>
    @enderror

    @if($collections->isNotEmpty())
        <div class="flex gap-2 overflow-x-auto pb-1">
            <flux:button
                type="button"
                size="sm"
                wire:click="$set('selectedCollection', '')"
                variant="{{ $selectedCollection === '' ? 'primary' : 'ghost' }}"
            >
                {{ __('decision.favorites.all') }}
            </flux:button>

            @foreach($collections as $collection)
                <flux:button
                    type="button"
                    size="sm"
                    wire:click="$set('selectedCollection', @js($collection))"
                    variant="{{ $selectedCollection === $collection ? 'primary' : 'ghost' }}"
                >
                    {{ $collection }}
                </flux:button>
            @endforeach
        </div>
    @endif

    <div wire:loading.delay wire:target="selectedCollection,remove,updateNote,updatePriority,toggleCompare" class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
        {{ __('decision.common.updating') }}
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        @forelse($cards as $card)
            @php($favorite = $card['favorite'])

            <flux:card class="space-y-4">
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
                                <a href="{{ $card['url'] }}" wire:navigate class="block">
                                    <flux:heading size="sm" class="truncate hover:text-emerald-700 dark:hover:text-emerald-300">
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
                                wire:click="remove({{ $favorite->id }})"
                                wire:confirm="{{ __('decision.favorites.remove_confirmation') }}"
                                aria-label="{{ __('decision.favorites.remove') }}"
                            />
                        </div>

                        <div class="flex flex-wrap gap-1">
                            <flux:badge size="sm">{{ $card['room_type'] }}</flux:badge>
                            <flux:badge size="sm">{{ $card['sleeping_place_type'] }}</flux:badge>
                        </div>
                    </div>
                </div>

                <div class="grid gap-2 text-sm">
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <div class="text-zinc-500">{{ __('decision.favorites.saved_dates') }}</div>
                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $card['dates'] }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-zinc-500">{{ __('decision.favorites.saved_price') }}</div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $card['saved_price'] ?? __('decision.favorites.no_saved_price') }}</div>
                        </div>
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-zinc-500">{{ $card['current_price_label'] }}</div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $card['current_price'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if($card['price_state'] === 'dropped')
                        <flux:badge color="green">{{ __('decision.favorites.price_dropped') }}</flux:badge>
                    @elseif($card['price_state'] === 'increased')
                        <flux:badge color="amber">{{ __('decision.favorites.price_increased') }}</flux:badge>
                    @else
                        <flux:badge color="zinc">{{ __('decision.favorites.price_same') }}</flux:badge>
                    @endif

                    @if($card['availability_changed'] === true)
                        <flux:badge color="amber">{{ __('decision.favorites.availability_changed') }}</flux:badge>
                    @elseif($card['availability_changed'] === false)
                        <flux:badge color="green">{{ __('decision.favorites.still_available') }}</flux:badge>
                    @endif
                </div>

                <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_6rem]">
                    <flux:field>
                        <flux:label>{{ __('decision.favorites.personal_note') }}</flux:label>
                        <flux:textarea
                            rows="2"
                            wire:change="updateNote({{ $favorite->id }}, $event.target.value)"
                            placeholder="{{ __('decision.favorites.personal_note_placeholder') }}"
                        >{{ $favorite->note }}</flux:textarea>
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('decision.favorites.priority') }}</flux:label>
                        <flux:input
                            type="number"
                            min="0"
                            max="9"
                            inputmode="numeric"
                            value="{{ $favorite->priority }}"
                            wire:change="updatePriority({{ $favorite->id }}, $event.target.value)"
                        />
                    </flux:field>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                    <flux:checkbox
                        wire:click="toggleCompare({{ $card['place_id'] }})"
                        :checked="in_array($card['place_id'], $selectedForCompare, true)"
                        label="{{ __('decision.compare.select_place') }}"
                    />

                    <flux:button href="{{ $card['url'] }}" size="sm" variant="ghost" icon="arrow-right" wire:navigate>
                        {{ __('decision.favorites.open_place') }}
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <flux:card class="sm:col-span-2">
                <div class="space-y-3 text-center">
                    <div class="mx-auto flex size-11 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                        <flux:icon name="heart" class="size-6" />
                    </div>
                    <div class="space-y-1">
                        <flux:heading size="lg">{{ __('decision.favorites.empty_title') }}</flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('decision.favorites.empty_helper') }}</flux:text>
                    </div>
                    <flux:button href="{{ route('search.index', ['locale' => app()->getLocale()]) }}" variant="primary" wire:navigate>
                        {{ __('decision.favorites.empty_action') }}
                    </flux:button>
                </div>
            </flux:card>
        @endforelse
    </div>
</section>
