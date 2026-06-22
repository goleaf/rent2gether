<section>
    <flux:card class="space-y-3">
        <flux:heading size="base">{{ __('inventory.panels.details') }}</flux:heading>
        @if ($item)
            <div class="space-y-1">
                <flux:text class="font-medium">{{ $item->name }}</flux:text>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ __('inventory.fields.inventory_number') }}: {{ $item->inventory_number }}
                </flux:text>
                <flux:badge color="zinc">{{ __('inventory.conditions.'.$item->condition_status) }}</flux:badge>
            </div>
        @else
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.empty.no_items') }}</flux:text>
        @endif
    </flux:card>
</section>
