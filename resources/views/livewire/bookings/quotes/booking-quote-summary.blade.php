<flux:card class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <flux:heading size="sm">{{ __('booking_quotes.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $summary['quote_number'] }}</flux:text>
        </div>
        <flux:badge color="lime">{{ $summary['status'] }}</flux:badge>
    </div>

    <div class="grid gap-2 sm:grid-cols-3">
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_quotes.price.total_payable') }}</flux:text>
            <flux:heading size="sm">{{ $summary['total_payable'] }}</flux:heading>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_quotes.price.refundable_amount') }}</flux:text>
            <flux:heading size="sm">{{ $summary['refundable_amount'] }}</flux:heading>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_quotes.price.non_refundable_amount') }}</flux:text>
            <flux:heading size="sm">{{ $summary['non_refundable_amount'] }}</flux:heading>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        <flux:badge size="sm">{{ $summary['availability_status'] }}</flux:badge>
        <flux:badge size="sm">{{ $summary['validation_status'] }}</flux:badge>
        <flux:badge size="sm">{{ $summary['pricing_status'] }}</flux:badge>
    </div>
</flux:card>
