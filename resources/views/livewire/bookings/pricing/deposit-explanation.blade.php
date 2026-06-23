<flux:callout color="blue" icon="information-circle">
    <flux:callout.heading icon="banknotes" icon:variant="mini">{{ __('pricing.fields.deposit') }} · {{ $deposit['amount'] }}</flux:callout.heading>
    <flux:callout.text>{{ __('pricing.messages.deposit_refundable', ['amount' => $deposit['refundable']]) }}</flux:callout.text>
</flux:callout>
