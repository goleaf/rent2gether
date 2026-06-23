<flux:card class="space-y-3">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-1">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $summary['guest_name'] }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $summary['sleeping_place'] }} · {{ $summary['room'] }}</flux:text>
            <flux:text size="sm">{{ $summary['dates'] }}</flux:text>
        </div>
        <flux:badge color="{{ $summary['status_color'] }}" icon="calendar-days">{{ $summary['status'] }}</flux:badge>
    </div>

    <div class="flex items-center justify-between gap-3">
        <flux:text size="sm">{{ __('bookings.fields.total_payable') }}</flux:text>
        <flux:text size="sm">{{ $summary['total_payable'] }}</flux:text>
    </div>
</flux:card>
