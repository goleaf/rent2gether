<flux:card class="space-y-4">
    <flux:heading size="md">{{ __('payments.fields.payment_method') }}</flux:heading>

    <flux:select wire:model.change="paymentMethod">
        @foreach ($methods as $method)
            <flux:select.option value="{{ $method }}">{{ __('payments.methods.'.$method) }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:button type="button" variant="ghost" class="w-full" wire:click="save">
        {{ __('payments.actions.change_payment_method') }}
    </flux:button>
</flux:card>
