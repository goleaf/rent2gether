<div>
    <flux:field>
        <flux:label>{{ __('booking_dates.fields.check_out_date') }}</flux:label>
        <flux:input type="date" min="{{ $minDate }}" wire:model.change="checkOutDate" icon="calendar-days" />
    </flux:field>
</div>
