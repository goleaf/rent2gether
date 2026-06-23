<div class="space-y-3">
    <flux:autocomplete
        type="search"
        clearable
        wire:model.live.debounce.500ms="search"
        label="{{ __('host.rules.picker.search_label') }}"
        description="{{ __('host.rules.picker.helper') }}"
        placeholder="{{ __('host.rules.picker.search_placeholder') }}"
        container:class="max-h-80" icon="magnifying-glass">
        @if(mb_strlen(trim($search)) >= 2)
            @foreach($this->groups as $group)
                @foreach($group['options'] as $option)
                    <flux:autocomplete.item wire:key="rule-search-suggestion-{{ $option['id'] }}">
                        {{ $option['label'] }}
                    </flux:autocomplete.item>
                @endforeach
            @endforeach
        @endif
    </flux:autocomplete>

    <div wire:loading.delay class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
        {{ __('host.rules.picker.loading') }}
    </div>

    <div class="space-y-4">
        @forelse($this->groups as $group)
            <section class="space-y-2" wire:key="rule-group-{{ $group['category'] }}">
                <div class="flex items-center justify-between gap-3">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ $group['category_label'] }}</span>
                        </span>
                    </flux:heading>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ trans_choice('host.rules.picker.group_count', count($group['options']), ['count' => count($group['options'])]) }}
                    </span>
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach($group['options'] as $option)
                        <flux:button
                            type="button"
                            size="sm"
                            variant="{{ $option['selected'] ? 'primary' : 'outline' }}"
                            wire:key="rule-option-{{ $option['id'] }}"
                            wire:click="toggle({{ $option['id'] }})"
                            class="h-auto min-h-11 w-full justify-start whitespace-normal text-left"
                            aria-pressed="{{ $option['selected'] ? 'true' : 'false' }}"
                         icon="plus">
                            <span class="min-w-0">
                                <span class="block font-medium">{{ $option['label'] }}</span>
                                @if($option['description'])
                                    <span class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">{{ $option['description'] }}</span>
                                @endif
                            </span>
                        </flux:button>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="rounded-lg border border-dashed border-zinc-200 px-3 py-4 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                {{ __('host.rules.picker.empty') }}
            </div>
        @endforelse
    </div>

    <div class="flex items-center justify-between gap-3 text-xs text-zinc-500 dark:text-zinc-400">
        <span>{{ trans_choice('host.rules.picker.selected_count', count($selectedIds), ['count' => count($selectedIds)]) }}</span>
        @if($search !== '')
            <flux:button type="button" size="xs" variant="ghost" wire:click="clearSearch" icon="x-mark">
                {{ __('host.rules.picker.clear_search') }}
            </flux:button>
        @endif
    </div>
</div>
