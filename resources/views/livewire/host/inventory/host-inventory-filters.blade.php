<section class="flex gap-2 overflow-x-auto pb-1">
    @foreach ($filters as $filter)
        <flux:button size="sm" variant="ghost" icon="funnel">{{ __('inventory.filters.'.$filter) }}</flux:button>
    @endforeach
</section>
