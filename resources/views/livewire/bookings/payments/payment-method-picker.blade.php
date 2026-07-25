<flux:card class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('payments.fields.payment_method') }}</span>
        </span>
    </flux:heading>

    <flux:select wire:model.change="paymentMethod">
        @forelse ($methods as $method)
            <flux:select.option value="{{ $method }}">{{ __('payments.methods.'.$method) }}</flux:select.option>
        @empty
            <flux:select.option value="internal_test">{{ __('payments.empty_states.no_payment_methods') }}</flux:select.option>
        @endforelse
    </flux:select>

    <flux:button type="button" variant="ghost" class="w-full" wire:click="save" icon="credit-card">
        {{ __('payments.actions.change_payment_method') }}
    </flux:button>
</flux:card>
