<flux:card class="space-y-3">
    <flux:heading size="sm">{{ __('booking_dates.summary.title') }}</flux:heading>

    <div class="grid grid-cols-3 gap-2">
        <div class="rounded-lg bg-zinc-50 px-3 py-2 text-center dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_dates.fields.nights_count') }}</flux:text>
            <flux:heading size="sm">{{ $summary['nights'] }}</flux:heading>
        </div>

        <div class="rounded-lg bg-zinc-50 px-3 py-2 text-center dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_dates.fields.chargeable_days_count') }}</flux:text>
            <flux:heading size="sm">{{ $summary['chargeable_days'] }}</flux:heading>
        </div>

        <div class="rounded-lg bg-zinc-50 px-3 py-2 text-center dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_dates.fields.calendar_presence_days_count') }}</flux:text>
            <flux:heading size="sm">{{ $summary['calendar_presence_days'] }}</flux:heading>
        </div>
    </div>
</flux:card>
