<section>
    <flux:card class="space-y-2">
        <flux:heading size="base">{{ __('inventory.panels.units') }}</flux:heading>
        @forelse ($units as $unit)
            <div class="flex items-center justify-between gap-3">
                <flux:text>{{ $unit->unit_label ?? $unit->unit_number }}</flux:text>
                <flux:badge color="zinc">{{ __('inventory.statuses.'.$unit->status) }}</flux:badge>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.empty.no_items') }}</flux:text>
        @endforelse
    </flux:card>
</section>
