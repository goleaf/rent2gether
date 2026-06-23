<flux:card class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('bookings.create.summary') }}</span>
        </span>
    </flux:heading>

    @if ($summary)
        <div class="grid gap-2">
            <div class="flex items-center justify-between gap-3">
                <flux:text size="sm">{{ __('bookings.fields.booking_number') }}</flux:text>
                <flux:text size="sm">{{ $summary['booking_number'] }}</flux:text>
            </div>
            <div class="flex items-center justify-between gap-3">
                <flux:text size="sm">{{ __('bookings.fields.check_in_date') }}</flux:text>
                <flux:text size="sm">{{ $summary['dates'] }}</flux:text>
            </div>
            <div class="flex items-center justify-between gap-3">
                <flux:text size="sm">{{ __('bookings.fields.total_payable') }}</flux:text>
                <flux:text size="sm">{{ $summary['total_payable'] }}</flux:text>
            </div>
        </div>
    @else
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('bookings.empty_states.no_booking_selected') }}</flux:text>
    @endif
</flux:card>
