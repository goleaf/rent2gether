<section class="mx-auto w-full max-w-xl space-y-4 px-4 py-4">
    <livewire:bookings.payments.payment-summary-card :payment-id="$payment->id" />

    <livewire:bookings.payments.payment-deadline-banner :payment-id="$payment->id" />

    <livewire:bookings.payments.payment-breakdown :payment-id="$payment->id" />

    <livewire:bookings.payments.payment-method-picker :payment-id="$payment->id" />

    <div class="grid gap-2">
        <flux:button type="button" variant="primary" class="w-full" wire:click="pay" wire:loading.attr="disabled">
            {{ __('payments.actions.pay') }}
        </flux:button>
        <flux:button type="button" variant="ghost" class="w-full">
            {{ __('payments.actions.cancel_payment') }}
        </flux:button>
    </div>

    <livewire:bookings.payments.payment-attempts-list :payment-id="$payment->id" />
    <livewire:bookings.payments.payment-receipt-card :payment-id="$payment->id" />
</section>
