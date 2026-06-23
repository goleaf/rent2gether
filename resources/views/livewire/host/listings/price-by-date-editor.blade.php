<flux:card class="space-y-4">
    <div class="grid gap-4 sm:grid-cols-3">
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_calendar.fields.start_date') }}</span>
    </span>
</flux:label>
            <flux:input type="date" wire:model.change="start" icon="calendar-days" />
        </flux:field>
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_calendar.fields.end_date') }}</span>
    </span>
</flux:label>
            <flux:input type="date" wire:model.change="end" icon="calendar-days" />
        </flux:field>
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_calendar.fields.price') }}</span>
    </span>
</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="price" icon="banknotes" />
        </flux:field>
    </div>

    <flux:button type="button" variant="primary" wire:click="save" wire:loading.attr="disabled" icon="calendar-days">
        {{ __('listing_calendar.actions.save_price') }}
    </flux:button>
</flux:card>
