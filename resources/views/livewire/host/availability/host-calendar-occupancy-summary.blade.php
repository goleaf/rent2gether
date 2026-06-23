<flux:card class="space-y-3">
    <div class="space-y-1">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('calendar.occupancy.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm">{{ __('calendar.occupancy.helper') }}</flux:text>
    </div>

    <div class="grid grid-cols-3 gap-2">
        <div class="rounded-lg border border-zinc-200 p-3 text-center dark:border-zinc-700">
            <div class="text-lg font-semibold">{{ $this->summary['available'] }}</div>
            <flux:text size="sm">{{ __('availability.statuses.available') }}</flux:text>
        </div>
        <div class="rounded-lg border border-zinc-200 p-3 text-center dark:border-zinc-700">
            <div class="text-lg font-semibold">{{ $this->summary['request_only'] }}</div>
            <flux:text size="sm">{{ __('availability.statuses.request_only') }}</flux:text>
        </div>
        <div class="rounded-lg border border-zinc-200 p-3 text-center dark:border-zinc-700">
            <div class="text-lg font-semibold">{{ $this->summary['blocked'] }}</div>
            <flux:text size="sm">{{ __('calendar.occupancy.blocked') }}</flux:text>
        </div>
    </div>
</flux:card>
