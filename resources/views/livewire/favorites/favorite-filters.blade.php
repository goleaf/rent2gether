<flux:field>
    <flux:label>{{ __('favorites.filters.title') }}</flux:label>
    <flux:select wire:model.change="filter">
        @foreach(['all', 'available', 'price_changed', 'price_dropped', 'with_note', 'high_priority', 'almost_chosen', 'backup'] as $filterOption)
            <flux:select.option value="{{ $filterOption }}">{{ __('favorites.filters.options.'.$filterOption) }}</flux:select.option>
        @endforeach
    </flux:select>
</flux:field>
