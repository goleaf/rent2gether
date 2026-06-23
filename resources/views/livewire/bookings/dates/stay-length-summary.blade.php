<flux:card class="space-y-3">
    <flux:heading size="sm">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('booking_dates.summary.title') }}</span>
        </span>
    </flux:heading>

    <div class="grid grid-cols-3 gap-2">
        <div class="rounded-lg bg-zinc-50 px-3 py-2 text-center dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_dates.fields.nights_count') }}</flux:text>
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $summary['nights'] }}</span>
                </span>
            </flux:heading>
        </div>

        <div class="rounded-lg bg-zinc-50 px-3 py-2 text-center dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_dates.fields.chargeable_days_count') }}</flux:text>
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $summary['chargeable_days'] }}</span>
                </span>
            </flux:heading>
        </div>

        <div class="rounded-lg bg-zinc-50 px-3 py-2 text-center dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_dates.fields.calendar_presence_days_count') }}</flux:text>
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $summary['calendar_presence_days'] }}</span>
                </span>
            </flux:heading>
        </div>
    </div>
</flux:card>
