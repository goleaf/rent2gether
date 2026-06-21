<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="sm">{{ __('calendar.block_sheet.title') }}</flux:heading>
        <flux:text size="sm">{{ __('calendar.block_sheet.helper') }}</flux:text>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:field>
            <flux:label>{{ __('calendar.fields.starts_at') }}</flux:label>
            <flux:input type="date" wire:model.change="startsAt" />
            <flux:error name="startsAt" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('calendar.fields.ends_at') }}</flux:label>
            <flux:input type="date" wire:model.change="endsAt" />
            <flux:error name="endsAt" />
        </flux:field>
    </div>

    <flux:field>
        <flux:label>{{ __('calendar.fields.block_type') }}</flux:label>
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
        <flux:label>{{ __('calendar.fields.note') }}</flux:label>
        <flux:textarea wire:model.blur="note" />
        <flux:error name="note" />
    </flux:field>

    <flux:button type="button" variant="primary" class="w-full" wire:click="create">
        {{ __('calendar.actions.create_block') }}
    </flux:button>
</flux:card>
