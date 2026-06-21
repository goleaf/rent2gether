<flux:card class="space-y-4">
    <flux:heading size="md">{{ __('bookings.fields.guest_message') }}</flux:heading>

    <flux:textarea wire:model.blur="guestMessage" rows="4" />

    <flux:button variant="primary" icon="check" wire:click="saveMessage" wire:loading.attr="disabled">
        {{ __('bookings.actions.save_message') }}
    </flux:button>
</flux:card>
