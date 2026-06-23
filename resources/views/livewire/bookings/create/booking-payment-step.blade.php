<flux:card class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="md">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('bookings.cards.payment') }}</span>
            </span>
        </flux:heading>
        <flux:badge icon="calendar-days">{{ $summary['payment_status'] }}</flux:badge>
    </div>

    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
        <flux:text size="sm">{{ __('bookings.fields.total_payable') }}</flux:text>
        <flux:heading size="md">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ $summary['total_payable'] }}</span>
            </span>
        </flux:heading>
    </div>

    <flux:button variant="primary" icon="credit-card" wire:click="markPaid" wire:loading.attr="disabled" class="w-full">
        {{ __('bookings.actions.mark_paid_mvp') }}
    </flux:button>
</flux:card>
