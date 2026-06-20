<flux:field>
    <flux:label>{{ __('favorites.sort.title') }}</flux:label>
    <flux:select wire:model.change="sort">
        @foreach(['recent', 'oldest', 'available_first', 'unavailable_first', 'cheap_first', 'expensive_first', 'price_dropped', 'high_priority', 'almost_chosen'] as $sortOption)
            <flux:select.option value="{{ $sortOption }}">{{ __('favorites.sort.options.'.$sortOption) }}</flux:select.option>
        @endforeach
    </flux:select>
</flux:field>
