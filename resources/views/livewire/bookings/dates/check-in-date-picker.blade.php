<div>
    <flux:field>
        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('booking_dates.fields.check_in_date') }}</span>
    </span>
</flux:label>
        <flux:input type="date" min="{{ $minDate }}" wire:model.change="checkInDate" icon="calendar-days" />
    </flux:field>
</div>
