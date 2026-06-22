<section>
    <flux:card class="space-y-2">
        <flux:heading size="base">{{ __('inventory.panels.booking_card') }}</flux:heading>
        @forelse ($assignments as $assignment)
            <div class="flex items-center justify-between gap-3">
                <flux:text>{{ $assignment->inventoryItem?->name }}</flux:text>
                <flux:badge color="zinc">{{ __('inventory.assignment_statuses.'.$assignment->status) }}</flux:badge>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.empty.no_assignments') }}</flux:text>
        @endforelse
    </flux:card>
</section>
