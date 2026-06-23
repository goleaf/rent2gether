<section>
    <flux:card class="space-y-2">
        <flux:heading size="base">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="cube" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('inventory.panels.item_card') }}</span>
            </span>
        </flux:heading>
        @if ($item)
            <flux:text class="font-medium">{{ $item->name }}</flux:text>
            <flux:badge color="zinc" icon="user">{{ __('inventory.statuses.'.$item->status) }}</flux:badge>
        @else
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.empty.no_items') }}</flux:text>
        @endif
    </flux:card>
</section>
