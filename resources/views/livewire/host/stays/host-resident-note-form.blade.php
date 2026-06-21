<flux:card class="space-y-3">
    <flux:heading size="md">{{ __('stays.actions.add_note') }}</flux:heading>
    <flux:textarea wire:model.blur="note" :label="__('stays.fields.host_note')" />
    <flux:button variant="primary" wire:click="save">{{ __('stays.actions.save_note') }}</flux:button>
</flux:card>
