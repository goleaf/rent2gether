<form wire:submit="save" class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="user" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('guest_profile.title') }}</span>
        </span>
    </flux:heading>

        <flux:field variant="inline">
        <flux:checkbox wire:model.change="needsQuietPlace" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('guest_profile.fields.needs_quiet_place') }}</span>
            </span>
        </flux:label>
        <flux:error name="needsQuietPlace" />
    </flux:field>
        <flux:field variant="inline">
        <flux:checkbox wire:model.change="needsFastWifi" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('guest_profile.fields.needs_fast_wifi') }}</span>
            </span>
        </flux:label>
        <flux:error name="needsFastWifi" />
    </flux:field>
        <flux:field variant="inline">
        <flux:checkbox wire:model.change="acceptsSharedRoom" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('guest_profile.fields.accepts_shared_room') }}</span>
            </span>
        </flux:label>
        <flux:error name="acceptsSharedRoom" />
    </flux:field>

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50" icon="check">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
