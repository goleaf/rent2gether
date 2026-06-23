<flux:card class="space-y-3">
    <div class="space-y-1">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('availability.nearest_dates.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm">{{ __('availability.nearest_dates.helper') }}</flux:text>
    </div>

    <div class="space-y-2">
        @forelse($this->ranges as $range)
            <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                {{ __('availability.nearest_dates.range', [
                    'check_in' => $range['check_in'],
                    'check_out' => $range['check_out'],
                    'nights' => $range['nights'],
                ]) }}
            </div>
        @empty
            <flux:text size="sm">{{ __('availability.empty.nearest_dates') }}</flux:text>
        @endforelse
    </div>
</flux:card>
