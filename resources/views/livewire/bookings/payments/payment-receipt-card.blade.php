<flux:card class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="md">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('payments.cards.receipt') }}</span>
            </span>
        </flux:heading>
        <flux:badge icon="calendar-days">{{ $receipt['status'] }}</flux:badge>
    </div>

    @if ($receipt['receipt_number'])
        <flux:text size="sm">{{ $receipt['receipt_number'] }}</flux:text>
    @else
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('payments.empty_states.no_receipt') }}</flux:text>
    @endif
</flux:card>
