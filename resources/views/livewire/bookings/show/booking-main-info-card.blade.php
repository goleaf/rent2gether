<flux:card class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-1">
            <flux:heading size="lg">{{ $summary['status'] }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $summary['booking_number'] }}</flux:text>
        </div>
        <flux:badge color="{{ $summary['status_color'] }}">{{ $summary['payment_status'] }}</flux:badge>
    </div>

    <div class="space-y-2">
        <flux:text>{{ $summary['sleeping_place'] }}</flux:text>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $summary['room'] }} · {{ $summary['property'] }}</flux:text>
        <flux:text size="sm">{{ $summary['dates'] }} · {{ trans_choice('bookings.units.nights', $summary['nights_count'], ['count' => $summary['nights_count']]) }}</flux:text>
    </div>

    <div class="grid grid-cols-2 gap-2">
        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('bookings.fields.check_in_date') }}</flux:text>
            <flux:heading size="sm">{{ $summary['check_in_date'] }}</flux:heading>
        </div>
        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('bookings.fields.check_out_date') }}</flux:text>
            <flux:heading size="sm">{{ $summary['check_out_date'] }}</flux:heading>
        </div>
    </div>
</flux:card>
