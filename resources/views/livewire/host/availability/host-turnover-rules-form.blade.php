<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('calendar.turnover.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm">{{ __('calendar.turnover.helper') }}</flux:text>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.fields.min_gap_between_guests') }}</span>
    </span>
</flux:label>
            <flux:input type="number" min="0" wire:model.blur="minGapMinutes" icon="numbered-list" />
            <flux:error name="minGapMinutes" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.fields.cleaning_gap_minutes') }}</span>
    </span>
</flux:label>
            <flux:input type="number" min="0" wire:model.blur="cleaningGapMinutes" icon="numbered-list" />
            <flux:error name="cleaningGapMinutes" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.fields.inspection_gap_minutes') }}</span>
    </span>
</flux:label>
            <flux:input type="number" min="0" wire:model.blur="inspectionGapMinutes" icon="numbered-list" />
            <flux:error name="inspectionGapMinutes" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.fields.earliest_new_check_in_time') }}</span>
    </span>
</flux:label>
            <flux:input type="time" wire:model.change="earliestNewCheckInTime" icon="calendar-days" />
            <flux:error name="earliestNewCheckInTime" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.fields.latest_previous_check_out_time') }}</span>
    </span>
</flux:label>
            <flux:input type="time" wire:model.change="latestPreviousCheckOutTime" icon="calendar-days" />
            <flux:error name="latestPreviousCheckOutTime" />
        </flux:field>
    </div>

    <div class="space-y-3">
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.fields.cleaning_required_between_guests') }}</span>
    </span>
</flux:label>
            <flux:switch wire:model.change="cleaningRequiredBetweenGuests" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.fields.inspection_required_after_checkout') }}</span>
    </span>
</flux:label>
            <flux:switch wire:model.change="inspectionRequiredAfterCheckout" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.fields.same_day_turnover_allowed') }}</span>
    </span>
</flux:label>
            <flux:switch wire:model.change="sameDayTurnoverAllowed" />
        </flux:field>
    </div>

    <flux:button type="button" variant="primary" class="w-full" wire:click="save" icon="calendar-days">
        {{ __('calendar.actions.save_turnover') }}
    </flux:button>
</flux:card>
