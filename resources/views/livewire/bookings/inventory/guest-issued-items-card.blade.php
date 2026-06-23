<section>
    <flux:card class="space-y-3">
        <flux:heading size="base">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('inventory.guest.issued_items') }}</span>
            </span>
        </flux:heading>
        @forelse ($assignments as $assignment)
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <flux:text class="truncate">{{ $assignment->inventoryItem?->name }}</flux:text>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                        {{ $assignment->expected_return ? __('inventory.guest.return_before_checkout') : __('inventory.assignment_types.'.$assignment->assignment_type) }}
                    </flux:text>
                </div>
                <flux:badge color="zinc" icon="calendar-days">{{ __('inventory.assignment_statuses.'.$assignment->status) }}</flux:badge>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.guest.no_items') }}</flux:text>
        @endforelse
    </flux:card>
</section>
