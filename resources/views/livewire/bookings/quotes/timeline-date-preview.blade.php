<flux:card class="space-y-3">
    <flux:heading size="sm">{{ __('booking_quotes.timeline.title') }}</flux:heading>

    <div class="space-y-2">
        @foreach($dates as $date)
            <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                <div>
                    <flux:text size="sm" class="font-medium">{{ $date['label'] }}</flux:text>
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $date['status'] }}</flux:text>
                </div>
                <flux:badge size="sm">{{ $date['scheduled_at'] }}</flux:badge>
            </div>
        @endforeach
    </div>
</flux:card>
