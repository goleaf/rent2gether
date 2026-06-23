<form wire:submit="save" class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="user" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('profiles.forms.user_profile') }}</span>
        </span>
    </flux:heading>

    <flux:input wire:model.blur="displayName" :label="__('profiles.fields.display_name')" icon="user" />
    <flux:input wire:model.blur="publicName" :label="__('profiles.fields.public_name')" icon="user" />
    <flux:input wire:model.blur="publicCityName" :label="__('profiles.fields.public_city_name')" icon="map-pin" />

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50" icon="check">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
