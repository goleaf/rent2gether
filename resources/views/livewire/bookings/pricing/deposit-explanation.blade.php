<flux:callout color="blue">
    <flux:callout.heading>{{ __('pricing.fields.deposit') }} · {{ $deposit['amount'] }}</flux:callout.heading>
    <flux:callout.text>{{ __('pricing.messages.deposit_refundable', ['amount' => $deposit['refundable']]) }}</flux:callout.text>
</flux:callout>
