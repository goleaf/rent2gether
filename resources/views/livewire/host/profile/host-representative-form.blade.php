<form wire:submit="save" class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('host_profile.representatives.title') }}</span>
        </span>
    </flux:heading>

    <flux:input wire:model.blur="name" :label="__('host_profile.representatives.fields.name')" icon="user" />
    <flux:input wire:model.blur="phone" :label="__('host_profile.representatives.fields.phone')" icon="phone" />
    <flux:checkbox wire:model.change="canHelpWithCheckIn" :label="__('host_profile.representatives.fields.can_help_with_check_in')" />

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50" icon="check">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
