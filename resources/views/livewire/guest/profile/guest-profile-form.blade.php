<form wire:submit="save" class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="user" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('guest_profile.title') }}</span>
        </span>
    </flux:heading>

    <flux:checkbox wire:model.change="needsQuietPlace" :label="__('guest_profile.fields.needs_quiet_place')" />
    <flux:checkbox wire:model.change="needsFastWifi" :label="__('guest_profile.fields.needs_fast_wifi')" />
    <flux:checkbox wire:model.change="acceptsSharedRoom" :label="__('guest_profile.fields.accepts_shared_room')" />

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50" icon="check">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
