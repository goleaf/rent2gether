<section class="space-y-3">
    <flux:card class="space-y-2">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:badge color="zinc" icon="user">{{ __('inventory.title') }}</flux:badge>
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="cube" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('inventory.panels.items') }}</span>
                    </span>
                </flux:heading>
            </div>
            <flux:button size="sm" variant="primary" icon="plus">{{ __('inventory.actions.add_item') }}</flux:button>
        </div>

        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('inventory.messages.panel_ready') }}
        </flux:text>
    </flux:card>

    <div class="flex gap-2 overflow-x-auto pb-1">
        @foreach ($filters as $filter)
            <flux:button size="sm" variant="ghost" wire:loading.attr="disabled" icon="funnel">
                {{ __('inventory.filters.'.$filter) }}
            </flux:button>
        @endforeach
    </div>

    <div class="space-y-2">
        @forelse ($items as $item)
            <flux:card class="space-y-2 p-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 space-y-1">
                        <flux:text class="truncate font-medium">{{ $item->name }}</flux:text>
                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                            {{ __('inventory.item_types.'.$item->item_type) }} · {{ __('inventory.scopes.'.$item->inventory_scope) }}
                        </flux:text>
                    </div>
                    <flux:badge color="zinc" icon="user">{{ __('inventory.statuses.'.$item->status) }}</flux:badge>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if ($item->is_returnable)
                        <flux:badge color="blue" icon="user">{{ __('inventory.fields.is_returnable') }}</flux:badge>
                    @endif
                    @if ($item->is_required_for_readiness)
                        <flux:badge color="amber" icon="exclamation-triangle">{{ __('inventory.fields.is_required_for_readiness') }}</flux:badge>
                    @endif
                    @if ($item->is_promised_in_listing)
                        <flux:badge color="green" icon="check-circle">{{ __('inventory.fields.is_promised_in_listing') }}</flux:badge>
                    @endif
                </div>
            </flux:card>
        @empty
            <flux:card>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ __('inventory.empty.no_items') }}
                </flux:text>
            </flux:card>
        @endforelse
    </div>
</section>
