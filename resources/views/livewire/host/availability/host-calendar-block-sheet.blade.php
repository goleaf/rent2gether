<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('calendar.block_sheet.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm">{{ __('calendar.block_sheet.helper') }}</flux:text>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('calendar.fields.starts_at') }}</span>
    </span>
</flux:label>
            <flux:input type="date" wire:model.change="startsAt" icon="calendar-days" />
            <flux:error name="startsAt" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('calendar.fields.ends_at') }}</span>
    </span>
</flux:label>
            <flux:input type="date" wire:model.change="endsAt" icon="calendar-days" />
            <flux:error name="endsAt" />
        </flux:field>
    </div>

    <flux:field>
        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('calendar.fields.block_type') }}</span>
    </span>
</flux:label>
        <flux:select wire:model.change="blockType">
            <flux:select.option value="closed_by_host">{{ __('availability.block_types.closed_by_host') }}</flux:select.option>
            <flux:select.option value="cleaning">{{ __('availability.block_types.cleaning') }}</flux:select.option>
            <flux:select.option value="repair">{{ __('availability.block_types.repair') }}</flux:select.option>
            <flux:select.option value="request_only">{{ __('availability.block_types.request_only') }}</flux:select.option>
            <flux:select.option value="hidden">{{ __('availability.block_types.hidden') }}</flux:select.option>
        </flux:select>
        <flux:error name="blockType" />
    </flux:field>

    <flux:field>
        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('calendar.fields.note') }}</span>
    </span>
</flux:label>
        <flux:textarea wire:model.blur="note" />
        <flux:error name="note" />
    </flux:field>

    <flux:button type="button" variant="primary" class="w-full" wire:click="create" icon="calendar-days">
        {{ __('calendar.actions.create_block') }}
    </flux:button>
</flux:card>
