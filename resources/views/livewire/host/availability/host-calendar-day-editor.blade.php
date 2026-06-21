<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="sm">{{ __('calendar.day_editor.title') }}</flux:heading>
        <flux:text size="sm">{{ __('calendar.day_editor.helper') }}</flux:text>
    </div>

    <div class="space-y-3">
        <flux:field>
            <flux:label>{{ __('calendar.fields.date') }}</flux:label>
            <flux:input type="date" wire:model.change="date" />
            <flux:error name="date" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('calendar.fields.status') }}</flux:label>
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
            <flux:label>{{ __('calendar.fields.note') }}</flux:label>
            <flux:textarea wire:model.blur="note" />
            <flux:error name="note" />
        </flux:field>
    </div>

    <flux:button type="button" variant="primary" class="w-full" wire:click="save">
        {{ __('calendar.actions.save_day') }}
    </flux:button>
</flux:card>
