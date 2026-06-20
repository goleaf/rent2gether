<div class="space-y-3">
    <flux:autocomplete
        type="search"
        clearable
        wire:model.live.debounce.500ms="search"
        label="{{ __('host.amenities.picker.search_label') }}"
        description="{{ __('host.amenities.picker.helper') }}"
        placeholder="{{ __('host.amenities.picker.search_placeholder') }}"
        container:class="max-h-80"
    >
        @if(mb_strlen(trim($search)) >= 2)
            @foreach($this->groups as $group)
                @foreach($group['options'] as $option)
                    <flux:autocomplete.item wire:key="amenity-search-suggestion-{{ $option['id'] }}">
                        {{ $option['label'] }}
                    </flux:autocomplete.item>
                @endforeach
            @endforeach
        @endif
    </flux:autocomplete>

    <div wire:loading.delay class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
        {{ __('host.amenities.picker.loading') }}
    </div>

    <div class="space-y-4">
        @forelse($this->groups as $group)
            <section class="space-y-2" wire:key="amenity-group-{{ $group['category'] }}">
                <div class="flex items-center justify-between gap-3">
                    <flux:heading size="sm">{{ $group['category_label'] }}</flux:heading>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ trans_choice('host.amenities.picker.group_count', count($group['options']), ['count' => count($group['options'])]) }}
                    </span>
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach($group['options'] as $option)
                        <button
                            type="button"
                            wire:key="amenity-option-{{ $option['id'] }}"
                            wire:click="toggle({{ $option['id'] }})"
                            @class([
                                'flex min-h-11 items-start gap-3 rounded-lg border px-3 py-2 text-left text-sm transition',
                                'border-emerald-500 bg-emerald-50 text-emerald-950 dark:border-emerald-400 dark:bg-emerald-950/30 dark:text-emerald-50' => $option['selected'],
                                'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:border-zinc-600' => ! $option['selected'],
                            ])
                            aria-pressed="{{ $option['selected'] ? 'true' : 'false' }}"
                        >
                            <span @class([
                                'mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded border',
                                'border-emerald-500 bg-emerald-500' => $option['selected'],
                                'border-zinc-300 dark:border-zinc-600' => ! $option['selected'],
                            ])>
                                <span @class([
                                    'h-2.5 w-2.5 rounded-sm bg-white',
                                    'hidden' => ! $option['selected'],
                                ])></span>
                            </span>
                            <span class="min-w-0">
                                <span class="block font-medium">{{ $option['label'] }}</span>
                                @if($option['description'])
                                    <span class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">{{ $option['description'] }}</span>
                                @endif
                            </span>
                        </button>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="rounded-lg border border-dashed border-zinc-200 px-3 py-4 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                {{ __('host.amenities.picker.empty') }}
            </div>
        @endforelse
    </div>

    <div class="flex items-center justify-between gap-3 text-xs text-zinc-500 dark:text-zinc-400">
        <span>{{ trans_choice('host.amenities.picker.selected_count', count($selectedIds), ['count' => count($selectedIds)]) }}</span>
        @if($search !== '')
            <flux:button type="button" size="xs" variant="ghost" wire:click="clearSearch">
                {{ __('host.amenities.picker.clear_search') }}
            </flux:button>
        @endif
    </div>
</div>
