<section>
    <flux:card class="space-y-2">
        <flux:heading size="base">{{ __('inventory.panels.guest_assignment') }}</flux:heading>
        @if ($assignment)
            <flux:text class="font-medium">{{ $assignment->inventoryItem?->name }}</flux:text>
            <flux:badge color="zinc">{{ __('inventory.assignment_statuses.'.$assignment->status) }}</flux:badge>
        @else
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.empty.no_assignments') }}</flux:text>
        @endif
    </flux:card>
</section>
