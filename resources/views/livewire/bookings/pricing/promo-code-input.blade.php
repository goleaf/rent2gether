<flux:card class="space-y-3">
    <flux:heading size="sm">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('pricing.fields.promo_code') }}</span>
        </span>
    </flux:heading>

    <flux:field>
        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('pricing.fields.promo_code') }}</span>
    </span>
</flux:label>
        <flux:input wire:model.blur="promoCode" icon="tag" />
        <flux:error name="promoCode" />
    </flux:field>

    <div class="flex justify-end">
        <flux:button variant="primary" wire:click="apply" wire:loading.attr="disabled" icon="check">
            {{ __('pricing.actions.apply_promo') }}
        </flux:button>
    </div>

    @if ($messageKey)
        <flux:callout color="blue" icon="information-circle">
            <flux:callout.heading>{{ __($messageKey) }}</flux:callout.heading>
        </flux:callout>
    @endif
</flux:card>
