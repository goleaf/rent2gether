<form wire:submit="save" class="space-y-4">
    <flux:heading size="md">{{ __('profiles.forms.user_profile') }}</flux:heading>

    <flux:input wire:model.blur="displayName" :label="__('profiles.fields.display_name')" />
    <flux:input wire:model.blur="publicName" :label="__('profiles.fields.public_name')" />
    <flux:input wire:model.blur="publicCityName" :label="__('profiles.fields.public_city_name')" />

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
