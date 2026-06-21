<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="sm">{{ __('calendar.turnover.title') }}</flux:heading>
        <flux:text size="sm">{{ __('calendar.turnover.helper') }}</flux:text>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:field>
            <flux:label>{{ __('availability.fields.min_gap_between_guests') }}</flux:label>
            <flux:input type="number" min="0" wire:model.blur="minGapMinutes" />
            <flux:error name="minGapMinutes" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('availability.fields.cleaning_gap_minutes') }}</flux:label>
            <flux:input type="number" min="0" wire:model.blur="cleaningGapMinutes" />
            <flux:error name="cleaningGapMinutes" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('availability.fields.inspection_gap_minutes') }}</flux:label>
            <flux:input type="number" min="0" wire:model.blur="inspectionGapMinutes" />
            <flux:error name="inspectionGapMinutes" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('availability.fields.earliest_new_check_in_time') }}</flux:label>
            <flux:input type="time" wire:model.change="earliestNewCheckInTime" />
            <flux:error name="earliestNewCheckInTime" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('availability.fields.latest_previous_check_out_time') }}</flux:label>
            <flux:input type="time" wire:model.change="latestPreviousCheckOutTime" />
            <flux:error name="latestPreviousCheckOutTime" />
        </flux:field>
    </div>

    <div class="space-y-3">
        <flux:field>
            <flux:label>{{ __('availability.fields.cleaning_required_between_guests') }}</flux:label>
            <flux:switch wire:model.change="cleaningRequiredBetweenGuests" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('availability.fields.inspection_required_after_checkout') }}</flux:label>
            <flux:switch wire:model.change="inspectionRequiredAfterCheckout" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('availability.fields.same_day_turnover_allowed') }}</flux:label>
            <flux:switch wire:model.change="sameDayTurnoverAllowed" />
        </flux:field>
    </div>

    <flux:button type="button" variant="primary" class="w-full" wire:click="save">
        {{ __('calendar.actions.save_turnover') }}
    </flux:button>
</flux:card>
