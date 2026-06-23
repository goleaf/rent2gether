<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('calendar.bulk.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm">{{ __('calendar.bulk.helper') }}</flux:text>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:field>
            <flux:label>{{ __('calendar.fields.from') }}</flux:label>
            <flux:input type="date" wire:model.change="from" icon="calendar-days" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('calendar.fields.to') }}</flux:label>
            <flux:input type="date" wire:model.change="to" icon="calendar-days" />
        </flux:field>
    </div>

    <div class="grid gap-2 sm:grid-cols-2">
        <flux:button type="button" wire:click="open" icon="calendar-days">{{ __('availability.actions.open_dates') }}</flux:button>
        <flux:button type="button" wire:click="close" icon="x-mark">{{ __('availability.actions.close_dates') }}</flux:button>
        <flux:button type="button" wire:click="requestOnly" icon="plus">{{ __('availability.actions.set_request_only') }}</flux:button>
        <flux:button type="button" wire:click="markRepair" icon="calendar-days">{{ __('availability.actions.mark_repair') }}</flux:button>
        <flux:button type="button" wire:click="markCleaning" icon="calendar-days">{{ __('availability.actions.mark_cleaning') }}</flux:button>
    </div>
</flux:card>
