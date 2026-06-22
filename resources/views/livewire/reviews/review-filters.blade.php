<div class="flex gap-2 overflow-x-auto pb-1">
    @foreach(['all', 'cleanliness', 'noise', 'internet', 'roommates', 'photos'] as $filterOption)
        <flux:button type="button" size="sm" :variant="$filter === $filterOption ? 'primary' : 'ghost'" wire:click="setFilter('{{ $filterOption }}')">
            {{ __('reviews.filters.'.$filterOption) }}
        </flux:button>
    @endforeach
</div>
