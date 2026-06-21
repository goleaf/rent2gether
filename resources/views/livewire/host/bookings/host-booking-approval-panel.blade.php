<flux:card class="space-y-4">
    <flux:heading size="md">{{ __('bookings.host.approval') }}</flux:heading>

    <flux:textarea wire:model.blur="message" rows="3" placeholder="{{ __('bookings.fields.host_response') }}" />
    <flux:textarea wire:model.blur="rejectionReason" rows="3" placeholder="{{ __('bookings.fields.rejection_reason') }}" />

    <div class="grid gap-2 sm:grid-cols-2">
        <flux:button variant="primary" icon="check" wire:click="approve" wire:loading.attr="disabled">
            {{ __('bookings.actions.confirm') }}
        </flux:button>
        <flux:button variant="filled" icon="x" wire:click="reject" wire:loading.attr="disabled">
            {{ __('bookings.actions.reject') }}
        </flux:button>
    </div>
</flux:card>
