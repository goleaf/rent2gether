<flux:card class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="md">{{ __('bookings.cards.payment') }}</flux:heading>
        <flux:badge>{{ $summary['payment_status'] }}</flux:badge>
    </div>

    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
        <flux:text size="sm">{{ __('bookings.fields.total_payable') }}</flux:text>
        <flux:heading size="md">{{ $summary['total_payable'] }}</flux:heading>
    </div>

    <flux:button variant="primary" icon="credit-card" wire:click="markPaid" wire:loading.attr="disabled" class="w-full">
        {{ __('bookings.actions.mark_paid_mvp') }}
    </flux:button>
</flux:card>
