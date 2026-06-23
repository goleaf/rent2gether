<flux:field>
    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="heart" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('favorites.sort.title') }}</span>
    </span>
</flux:label>
    <flux:select wire:model.change="sort">
        @foreach(['recent', 'oldest', 'available_first', 'unavailable_first', 'cheap_first', 'expensive_first', 'price_dropped', 'high_priority', 'almost_chosen'] as $sortOption)
            <flux:select.option value="{{ $sortOption }}">{{ __('favorites.sort.options.'.$sortOption) }}</flux:select.option>
        @endforeach
    </flux:select>
</flux:field>
