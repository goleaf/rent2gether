<form wire:submit="save" class="space-y-4">
    <flux:heading size="md">{{ __('profiles.sections.notifications') }}</flux:heading>

    <flux:input wire:model.blur="category" :label="__('profiles.fields.notification_category')" />
    <flux:input wire:model.blur="channel" :label="__('profiles.fields.notification_channel')" />
    <flux:checkbox wire:model.change="enabled" :label="__('profiles.fields.notifications_enabled')" />

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
