<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="sm">{{ __('calendar.bulk.title') }}</flux:heading>
        <flux:text size="sm">{{ __('calendar.bulk.helper') }}</flux:text>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:field>
            <flux:label>{{ __('calendar.fields.from') }}</flux:label>
            <flux:input type="date" wire:model.change="from" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('calendar.fields.to') }}</flux:label>
            <flux:input type="date" wire:model.change="to" />
        </flux:field>
    </div>

    <div class="grid gap-2 sm:grid-cols-2">
        <flux:button type="button" wire:click="open">{{ __('availability.actions.open_dates') }}</flux:button>
        <flux:button type="button" wire:click="close">{{ __('availability.actions.close_dates') }}</flux:button>
        <flux:button type="button" wire:click="requestOnly">{{ __('availability.actions.set_request_only') }}</flux:button>
        <flux:button type="button" wire:click="markRepair">{{ __('availability.actions.mark_repair') }}</flux:button>
        <flux:button type="button" wire:click="markCleaning">{{ __('availability.actions.mark_cleaning') }}</flux:button>
    </div>
</flux:card>
