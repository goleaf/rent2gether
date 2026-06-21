<flux:card class="space-y-4">
    <flux:heading size="md">{{ __('bookings.cards.requirements') }}</flux:heading>

    <div class="space-y-2">
        @forelse ($requirements as $requirement)
            <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                <flux:text size="sm">{{ __('bookings.requirements.'.$requirement->requirement_key) }}</flux:text>
                <flux:badge size="sm">{{ __('bookings.requirement_statuses.'.$requirement->status) }}</flux:badge>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('bookings.empty_states.no_requirements') }}</flux:text>
        @endforelse
    </div>
</flux:card>
