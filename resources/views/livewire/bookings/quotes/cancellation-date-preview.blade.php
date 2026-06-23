<flux:card class="space-y-3">
    <flux:heading size="sm">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('booking_quotes.cancellation.title') }}</span>
        </span>
    </flux:heading>

    <div class="space-y-2">
        <div class="flex items-center justify-between gap-3">
            <flux:text size="sm">{{ __('booking_quotes.timeline.free_cancellation_until') }}</flux:text>
            <flux:badge size="sm" icon="check-circle">{{ $preview['free_cancellation_until'] ?: __('booking_quotes.cancellation.not_available') }}</flux:badge>
        </div>
        <div class="flex items-center justify-between gap-3">
            <flux:text size="sm">{{ __('booking_quotes.timeline.cancellation_penalty_starts') }}</flux:text>
            <flux:badge size="sm" icon="check-circle">{{ $preview['cancellation_penalty_starts_at'] ?: __('booking_quotes.cancellation.not_available') }}</flux:badge>
        </div>
    </div>
</flux:card>
