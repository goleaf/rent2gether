<div>
    <flux:field>
        <flux:label>{{ __('booking_dates.fields.check_in_date') }}</flux:label>
        <flux:input type="date" min="{{ $minDate }}" wire:model.change="checkInDate" icon="calendar-days" />
    </flux:field>
</div>
