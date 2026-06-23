<flux:card class="space-y-3">
    <flux:heading size="sm">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('booking_quotes.timeline.title') }}</span>
        </span>
    </flux:heading>

    <div class="space-y-2">
        @foreach($dates as $date)
            <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                <div>
                    <flux:text size="sm" class="font-medium">{{ $date['label'] }}</flux:text>
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $date['status'] }}</flux:text>
                </div>
                <flux:badge size="sm" icon="calendar-days">{{ $date['scheduled_at'] }}</flux:badge>
            </div>
        @endforeach
    </div>
</flux:card>
