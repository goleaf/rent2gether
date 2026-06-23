<flux:card class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('bookings.cards.status_timeline') }}</span>
        </span>
    </flux:heading>

    <div class="space-y-2">
        @forelse ($logs as $log)
            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                <flux:text size="sm">{{ __('bookings.statuses.'.$log->new_status) }}</flux:text>
                <flux:text size="xs" class="text-zinc-500">{{ $log->created_at?->translatedFormat('d M, H:i') }}</flux:text>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('bookings.empty_states.no_status_logs') }}</flux:text>
        @endforelse
    </div>
</flux:card>
