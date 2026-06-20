<form wire:submit="save" class="space-y-4">
    <flux:heading size="md">{{ __('host_profile.representatives.title') }}</flux:heading>

    <flux:input wire:model.blur="name" :label="__('host_profile.representatives.fields.name')" />
    <flux:input wire:model.blur="phone" :label="__('host_profile.representatives.fields.phone')" />
    <flux:checkbox wire:model.change="canHelpWithCheckIn" :label="__('host_profile.representatives.fields.can_help_with_check_in')" />

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
