<div class="space-y-3">
        <flux:field variant="inline">
        <flux:checkbox wire:model.change="enabled" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="bell" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('notifications.settings.quiet_hours') }}</span>
            </span>
        </flux:label>
        <flux:error name="enabled" />
    </flux:field>
    <div class="grid grid-cols-2 gap-3">
        <flux:input wire:model.blur="start" label="{{ __('notifications.fields.quiet_hours_start') }}" icon="clock" />
        <flux:input wire:model.blur="end" label="{{ __('notifications.fields.quiet_hours_end') }}" icon="clock" />
    </div>
</div>
