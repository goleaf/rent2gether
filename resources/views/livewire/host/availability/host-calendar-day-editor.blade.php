<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('calendar.day_editor.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm">{{ __('calendar.day_editor.helper') }}</flux:text>
    </div>

    <div class="space-y-3">
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('calendar.fields.date') }}</span>
    </span>
</flux:label>
            <flux:input type="date" wire:model.change="date" icon="calendar-days" />
            <flux:error name="date" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('calendar.fields.status') }}</span>
    </span>
</flux:label>
            <flux:select wire:model.change="status">
                <flux:select.option value="available">{{ __('availability.statuses.available') }}</flux:select.option>
                <flux:select.option value="closed_by_host">{{ __('availability.statuses.closed_by_host') }}</flux:select.option>
                <flux:select.option value="repair">{{ __('availability.statuses.repair') }}</flux:select.option>
                <flux:select.option value="cleaning">{{ __('availability.statuses.cleaning') }}</flux:select.option>
                <flux:select.option value="request_only">{{ __('availability.statuses.request_only') }}</flux:select.option>
                <flux:select.option value="temporarily_hidden">{{ __('availability.statuses.temporarily_hidden') }}</flux:select.option>
            </flux:select>
            <flux:error name="status" />
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
    </div>

    <flux:button type="button" variant="primary" class="w-full" wire:click="save" icon="calendar-days">
        {{ __('calendar.actions.save_day') }}
    </flux:button>
</flux:card>
