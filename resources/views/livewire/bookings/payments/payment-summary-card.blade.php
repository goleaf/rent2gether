<flux:card class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-1">
            <flux:heading size="md">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('payments.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $summary['sleeping_place'] }} - {{ $summary['room'] }}</flux:text>
            <flux:text size="sm">{{ $summary['dates'] }}</flux:text>
        </div>
        <flux:badge color="{{ $summary['status_color'] }}" icon="calendar-days">{{ $summary['status'] }}</flux:badge>
    </div>

    <div class="grid gap-2">
        <div class="flex items-center justify-between gap-3">
            <flux:text size="sm">{{ __('payments.fields.payment_number') }}</flux:text>
            <flux:text size="sm">{{ $summary['payment_number'] }}</flux:text>
        </div>
        <div class="flex items-center justify-between gap-3">
            <flux:text size="sm">{{ __('payments.fields.amount') }}</flux:text>
            <flux:text size="sm">{{ $summary['amount'] }}</flux:text>
        </div>
        <div class="flex items-center justify-between gap-3">
            <flux:text size="sm">{{ __('payments.fields.remaining_amount') }}</flux:text>
            <flux:text size="sm">{{ $summary['remaining_amount'] }}</flux:text>
        </div>
    </div>
</flux:card>
