<flux:card class="space-y-3">
    <flux:heading size="sm">{{ __('pricing.fields.promo_code') }}</flux:heading>

    <flux:field>
        <flux:label>{{ __('pricing.fields.promo_code') }}</flux:label>
        <flux:input wire:model.blur="promoCode" />
        <flux:error name="promoCode" />
    </flux:field>

    <div class="flex justify-end">
        <flux:button variant="primary" wire:click="apply" wire:loading.attr="disabled">
            {{ __('pricing.actions.apply_promo') }}
        </flux:button>
    </div>

    @if ($messageKey)
        <flux:callout color="blue">
            <flux:callout.heading>{{ __($messageKey) }}</flux:callout.heading>
        </flux:callout>
    @endif
</flux:card>
