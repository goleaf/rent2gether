<flux:card class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="md">{{ __('payments.cards.receipt') }}</flux:heading>
        <flux:badge>{{ $receipt['status'] }}</flux:badge>
    </div>

    @if ($receipt['receipt_number'])
        <flux:text size="sm">{{ $receipt['receipt_number'] }}</flux:text>
    @else
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('payments.empty_states.no_receipt') }}</flux:text>
    @endif
</flux:card>
