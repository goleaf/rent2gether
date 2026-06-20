<form wire:submit="save" class="space-y-4">
    <flux:heading size="md">{{ __('host_profile.title') }}</flux:heading>

    <flux:input wire:model.blur="hostDisplayName" :label="__('host_profile.fields.host_display_name')" />
    <flux:checkbox wire:model.change="publicPhoneVisible" :label="__('host_profile.fields.public_phone_visible')" />

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
