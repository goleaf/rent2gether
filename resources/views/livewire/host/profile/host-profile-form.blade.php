<form wire:submit="save" class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('host_profile.title') }}</span>
        </span>
    </flux:heading>

    <flux:input wire:model.blur="hostDisplayName" :label="__('host_profile.fields.host_display_name')" icon="user" />
        <flux:field variant="inline">
        <flux:checkbox wire:model.change="publicPhoneVisible" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('host_profile.fields.public_phone_visible') }}</span>
            </span>
        </flux:label>
        <flux:error name="publicPhoneVisible" />
    </flux:field>

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50" icon="check">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
