<section>
    <flux:card class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <flux:heading size="base">{{ __('inventory.panels.return_checklist') }}</flux:heading>
            <flux:badge color="zinc">{{ __('inventory.actions.mark_returned') }}</flux:badge>
        </div>

        @forelse ($assignments as $assignment)
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <flux:text class="truncate">{{ $assignment->inventoryItem?->name }}</flux:text>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                        {{ __('inventory.fields.expected_return_at') }}: {{ $assignment->expected_return_at?->toDateString() }}
                    </flux:text>
                </div>
                <flux:badge color="zinc">{{ __('inventory.assignment_statuses.'.$assignment->status) }}</flux:badge>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.empty.no_assignments') }}</flux:text>
        @endforelse
    </flux:card>
</section>
