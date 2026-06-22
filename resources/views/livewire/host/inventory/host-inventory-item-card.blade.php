<section>
    <flux:card class="space-y-2">
        <flux:heading size="base">{{ __('inventory.panels.item_card') }}</flux:heading>
        @if ($item)
            <flux:text class="font-medium">{{ $item->name }}</flux:text>
            <flux:badge color="zinc">{{ __('inventory.statuses.'.$item->status) }}</flux:badge>
        @else
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.empty.no_items') }}</flux:text>
        @endif
    </flux:card>
</section>
