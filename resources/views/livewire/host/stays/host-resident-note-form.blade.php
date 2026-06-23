<flux:card class="space-y-3">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('stays.actions.add_note') }}</span>
        </span>
    </flux:heading>
        <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('stays.fields.host_note') }}</span>
            </span>
        </flux:label>
        <flux:textarea wire:model.blur="note" />
        <flux:error name="note" />
    </flux:field>
    <flux:button variant="primary" wire:click="save" icon="calendar-days">{{ __('stays.actions.save_note') }}</flux:button>
</flux:card>
