<flux:card class="space-y-3">
    <flux:heading size="sm">{{ __('booking_quotes.cancellation.title') }}</flux:heading>

    <div class="space-y-2">
        <div class="flex items-center justify-between gap-3">
            <flux:text size="sm">{{ __('booking_quotes.timeline.free_cancellation_until') }}</flux:text>
            <flux:badge size="sm">{{ $preview['free_cancellation_until'] ?: __('booking_quotes.cancellation.not_available') }}</flux:badge>
        </div>
        <div class="flex items-center justify-between gap-3">
            <flux:text size="sm">{{ __('booking_quotes.timeline.cancellation_penalty_starts') }}</flux:text>
            <flux:badge size="sm">{{ $preview['cancellation_penalty_starts_at'] ?: __('booking_quotes.cancellation.not_available') }}</flux:badge>
        </div>
    </div>
</flux:card>
