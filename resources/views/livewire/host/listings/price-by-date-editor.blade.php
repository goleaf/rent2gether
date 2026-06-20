<flux:card class="space-y-4">
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
    </div>

    <flux:button type="button" variant="primary" wire:click="save" wire:loading.attr="disabled">
        {{ __('listing_calendar.actions.save_price') }}
    </flux:button>
</flux:card>
