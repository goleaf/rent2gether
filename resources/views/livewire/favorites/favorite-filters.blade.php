<flux:field>
    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="heart" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('favorites.filters.title') }}</span>
    </span>
</flux:label>
    <flux:select wire:model.change="filter">
        @foreach(['all', 'available', 'price_changed', 'price_dropped', 'with_note', 'high_priority', 'almost_chosen', 'backup'] as $filterOption)
            <flux:select.option value="{{ $filterOption }}">{{ __('favorites.filters.options.'.$filterOption) }}</flux:select.option>
        @endforeach
    </flux:select>
</flux:field>
