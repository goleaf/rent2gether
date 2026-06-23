<form wire:submit="save" class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="user" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('profiles.forms.user_profile') }}</span>
        </span>
    </flux:heading>

        <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('profiles.fields.display_name') }}</span>
            </span>
        </flux:label>
        <flux:input wire:model.blur="displayName" icon="user" />
        <flux:error name="displayName" />
    </flux:field>
        <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('profiles.fields.public_name') }}</span>
            </span>
        </flux:label>
        <flux:input wire:model.blur="publicName" icon="user" />
        <flux:error name="publicName" />
    </flux:field>
        <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('profiles.fields.public_city_name') }}</span>
            </span>
        </flux:label>
        <flux:input wire:model.blur="publicCityName" icon="map-pin" />
        <flux:error name="publicCityName" />
    </flux:field>

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50" icon="check">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
