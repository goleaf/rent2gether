<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="md">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('listing_calendar.quick_open') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_calendar.quick_open_helper') }}</flux:text>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_calendar.fields.start_date') }}</span>
    </span>
</flux:label>
            <flux:input type="date" wire:model.change="start" icon="calendar-days" />
            <flux:error name="start" />
        </flux:field>
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_calendar.fields.end_date') }}</span>
    </span>
</flux:label>
            <flux:input type="date" wire:model.change="end" icon="calendar-days" />
            <flux:error name="end" />
        </flux:field>
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_calendar.fields.price') }}</span>
    </span>
</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="price" icon="banknotes" />
            <flux:error name="price" />
        </flux:field>
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_calendar.fields.min_nights') }}</span>
    </span>
</flux:label>
            <flux:input type="number" min="1" wire:model.blur="minNights" icon="numbered-list" />
            <flux:error name="minNights" />
        </flux:field>
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_calendar.fields.max_nights') }}</span>
    </span>
</flux:label>
            <flux:input type="number" min="1" wire:model.blur="maxNights" icon="numbered-list" />
            <flux:error name="maxNights" />
        </flux:field>
    </div>

    <div class="grid gap-2 sm:grid-cols-2">
        <flux:button type="button" variant="primary" wire:click="openDates" wire:loading.attr="disabled" wire:target="openDates" icon="calendar-days">
            <span wire:loading.remove wire:target="openDates">{{ __('listing_calendar.actions.open_dates') }}</span>
            <span wire:loading wire:target="openDates">{{ __('listing_wizard.actions.saving') }}</span>
        </flux:button>
        <flux:button type="button" variant="ghost" wire:click="closeDates" wire:loading.attr="disabled" wire:target="closeDates" icon="x-mark">
            <span wire:loading.remove wire:target="closeDates">{{ __('listing_calendar.actions.close_dates') }}</span>
            <span wire:loading wire:target="closeDates">{{ __('listing_wizard.actions.saving') }}</span>
        </flux:button>
    </div>
</flux:card>
