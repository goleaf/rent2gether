<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="md">{{ __('listing_calendar.quick_open') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_calendar.quick_open_helper') }}</flux:text>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <flux:field>
            <flux:label>{{ __('listing_calendar.fields.start_date') }}</flux:label>
            <flux:input type="date" wire:model.change="start" />
        </flux:field>
        <flux:field>
            <flux:label>{{ __('listing_calendar.fields.end_date') }}</flux:label>
            <flux:input type="date" wire:model.change="end" />
        </flux:field>
        <flux:field>
            <flux:label>{{ __('listing_calendar.fields.price') }}</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="price" />
        </flux:field>
        <flux:field>
            <flux:label>{{ __('listing_calendar.fields.min_nights') }}</flux:label>
            <flux:input type="number" min="1" wire:model.blur="minNights" />
        </flux:field>
        <flux:field>
            <flux:label>{{ __('listing_calendar.fields.max_nights') }}</flux:label>
            <flux:input type="number" min="1" wire:model.blur="maxNights" />
        </flux:field>
    </div>

    <div class="grid gap-2 sm:grid-cols-2">
        <flux:button type="button" variant="primary" wire:click="openDates" wire:loading.attr="disabled">
            {{ __('listing_calendar.actions.open_dates') }}
        </flux:button>
        <flux:button type="button" variant="ghost" wire:click="closeDates" wire:loading.attr="disabled">
            {{ __('listing_calendar.actions.close_dates') }}
        </flux:button>
    </div>
</flux:card>
