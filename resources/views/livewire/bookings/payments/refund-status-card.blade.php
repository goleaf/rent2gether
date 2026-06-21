<flux:card class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="md">{{ __('payments.cards.refund') }}</flux:heading>
        <flux:badge color="{{ $summary['status_color'] }}">{{ $summary['status'] }}</flux:badge>
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
