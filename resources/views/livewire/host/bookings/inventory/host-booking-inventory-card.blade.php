<section>
    <flux:card class="space-y-2">
        <flux:heading size="base">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('inventory.panels.booking_card') }}</span>
            </span>
        </flux:heading>
        @forelse ($assignments as $assignment)
            <div class="flex items-center justify-between gap-3">
                <flux:text>{{ $assignment->inventoryItem?->name }}</flux:text>
                <flux:badge color="zinc" icon="calendar-days">{{ __('inventory.assignment_statuses.'.$assignment->status) }}</flux:badge>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.empty.no_assignments') }}</flux:text>
        @endforelse
    </flux:card>
</section>
