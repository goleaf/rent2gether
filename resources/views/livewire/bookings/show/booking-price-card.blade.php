<flux:card class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="md">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('bookings.cards.price') }}</span>
            </span>
        </flux:heading>
        <flux:badge icon="calendar-days">{{ $summary['total_payable'] }}</flux:badge>
    </div>

    <div class="grid gap-2">
        <div class="flex items-center justify-between gap-3">
            <flux:text size="sm">{{ __('bookings.fields.total_without_deposit') }}</flux:text>
            <flux:text size="sm">{{ $summary['total_without_deposit'] }}</flux:text>
        </div>
        <div class="flex items-center justify-between gap-3">
            <flux:text size="sm">{{ __('bookings.fields.deposit_amount') }}</flux:text>
            <flux:text size="sm">{{ $summary['deposit_amount'] }}</flux:text>
        </div>
        <div class="flex items-center justify-between gap-3">
            <flux:text size="sm">{{ __('bookings.fields.cleaning_fee_amount') }}</flux:text>
            <flux:text size="sm">{{ $summary['cleaning_fee_amount'] }}</flux:text>
        </div>
        <div class="flex items-center justify-between gap-3">
            <flux:text size="sm">{{ __('bookings.fields.service_fee_amount') }}</flux:text>
            <flux:text size="sm">{{ $summary['service_fee_amount'] }}</flux:text>
        </div>
        <div class="flex items-center justify-between gap-3 border-t border-zinc-200 pt-2 dark:border-zinc-800">
            <flux:text size="sm">{{ __('bookings.fields.refundable_amount') }}</flux:text>
            <flux:text size="sm">{{ $summary['refundable_amount'] }}</flux:text>
        </div>
    </div>
</flux:card>
