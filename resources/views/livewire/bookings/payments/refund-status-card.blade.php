<flux:card class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="md">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('payments.cards.refund') }}</span>
            </span>
        </flux:heading>
        <flux:badge color="{{ $summary['status_color'] }}" icon="calendar-days">{{ $summary['status'] }}</flux:badge>
    </div>

    <div class="grid gap-2">
        <div class="flex items-center justify-between gap-3">
            <flux:text size="sm">{{ __('payments.fields.refund_number') }}</flux:text>
            <flux:text size="sm">{{ $summary['refund_number'] }}</flux:text>
        </div>
        <div class="flex items-center justify-between gap-3">
            <flux:text size="sm">{{ $summary['type'] }}</flux:text>
            <flux:text size="sm">{{ $summary['amount'] }}</flux:text>
        </div>
    </div>
</flux:card>
