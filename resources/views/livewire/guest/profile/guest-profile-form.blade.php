<form wire:submit="save" class="space-y-4">
    <flux:heading size="md">{{ __('guest_profile.title') }}</flux:heading>

    <flux:checkbox wire:model.change="needsQuietPlace" :label="__('guest_profile.fields.needs_quiet_place')" />
    <flux:checkbox wire:model.change="needsFastWifi" :label="__('guest_profile.fields.needs_fast_wifi')" />
    <flux:checkbox wire:model.change="acceptsSharedRoom" :label="__('guest_profile.fields.accepts_shared_room')" />

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
