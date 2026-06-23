<form wire:submit="save" class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="bell" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('profiles.sections.notifications') }}</span>
        </span>
    </flux:heading>

        <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('profiles.fields.notification_category') }}</span>
            </span>
        </flux:label>
        <flux:input wire:model.blur="category" icon="user" />
        <flux:error name="category" />
    </flux:field>
        <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('profiles.fields.notification_channel') }}</span>
            </span>
        </flux:label>
        <flux:input wire:model.blur="channel" icon="user" />
        <flux:error name="channel" />
    </flux:field>
        <flux:field variant="inline">
        <flux:checkbox wire:model.change="enabled" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="bell" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('profiles.fields.notifications_enabled') }}</span>
            </span>
        </flux:label>
        <flux:error name="enabled" />
    </flux:field>

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50" icon="check">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
