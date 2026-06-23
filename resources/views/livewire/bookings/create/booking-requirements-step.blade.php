<flux:card class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('bookings.cards.requirements') }}</span>
        </span>
    </flux:heading>

    <div class="space-y-2">
        @forelse ($requirements as $requirement)
            <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                <flux:text size="sm">{{ __('bookings.requirements.'.$requirement->requirement_key) }}</flux:text>
                <flux:badge size="sm" icon="calendar-days">{{ __('bookings.requirement_statuses.'.$requirement->status) }}</flux:badge>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('bookings.empty_states.no_requirements') }}</flux:text>
        @endforelse
    </div>
</flux:card>
