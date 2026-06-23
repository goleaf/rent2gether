<flux:card class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <flux:heading size="md">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('bookings.create.confirmation') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $summary['booking_number'] }}</flux:text>
        </div>
        <flux:badge color="{{ $summary['status_color'] }}" icon="calendar-days">{{ $summary['status'] }}</flux:badge>
    </div>

    <div class="grid gap-2">
        <flux:text>{{ $summary['sleeping_place'] }}</flux:text>
        <flux:text size="sm">{{ $summary['dates'] }} · {{ trans_choice('bookings.units.nights', $summary['nights_count'], ['count' => $summary['nights_count']]) }}</flux:text>
        <flux:text size="sm">{{ __('bookings.fields.total_payable') }}: {{ $summary['total_payable'] }}</flux:text>
    </div>
</flux:card>
