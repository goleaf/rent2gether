<form wire:submit="save" class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('host_profile.representatives.title') }}</span>
        </span>
    </flux:heading>

        <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('host_profile.representatives.fields.name') }}</span>
            </span>
        </flux:label>
        <flux:input wire:model.blur="name" icon="user" />
        <flux:error name="name" />
    </flux:field>
        <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('host_profile.representatives.fields.phone') }}</span>
            </span>
        </flux:label>
        <flux:input wire:model.blur="phone" icon="phone" />
        <flux:error name="phone" />
    </flux:field>
        <flux:field variant="inline">
        <flux:checkbox wire:model.change="canHelpWithCheckIn" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('host_profile.representatives.fields.can_help_with_check_in') }}</span>
            </span>
        </flux:label>
        <flux:error name="canHelpWithCheckIn" />
    </flux:field>

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50" icon="check">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
