<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('booking_dates.time_preferences.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('booking_dates.time_preferences.helper') }}</flux:text>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:field>
            <flux:label>{{ __('booking_dates.fields.check_in_time') }}</flux:label>
            <flux:input type="time" wire:model.change="checkInTime" icon="calendar-days" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('booking_dates.fields.check_out_time') }}</flux:label>
            <flux:input type="time" wire:model.change="checkOutTime" icon="calendar-days" />
        </flux:field>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:switch wire:model.change="earlyCheckInRequested" :label="__('booking_dates.fields.early_check_in')" />
        <flux:switch wire:model.change="lateCheckOutRequested" :label="__('booking_dates.fields.late_check_out')" />
        <flux:switch wire:model.change="flexibleCheckIn" :label="__('booking_dates.fields.flexible_check_in')" />
        <flux:switch wire:model.change="flexibleCheckOut" :label="__('booking_dates.fields.flexible_check_out')" />
    </div>

    <flux:field>
        <flux:label>{{ __('booking_dates.fields.check_in_comment') }}</flux:label>
        <flux:textarea rows="2" wire:model.blur="checkInComment" />
    </flux:field>

    <flux:field>
        <flux:label>{{ __('booking_dates.fields.check_out_comment') }}</flux:label>
        <flux:textarea rows="2" wire:model.blur="checkOutComment" />
    </flux:field>
</flux:card>
</div>
