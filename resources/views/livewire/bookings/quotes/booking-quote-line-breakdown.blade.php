<flux:card class="space-y-3">
    <div class="space-y-1">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('booking_quotes.lines.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('booking_quotes.lines.helper') }}</flux:text>
    </div>

    <div class="space-y-2">
        @foreach($lines as $line)
            <div class="flex items-start justify-between gap-3 rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                <div>
                    <flux:text size="sm" class="font-medium">{{ $line['label'] }}</flux:text>
                    @if($line['date'])
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $line['date'] }}</flux:text>
                    @endif
                </div>
                <div class="text-right">
                    <flux:text size="sm" class="{{ $line['is_discount'] ? 'text-green-700 dark:text-green-300' : 'font-medium' }}">{{ $line['amount'] }}</flux:text>
                    @if($line['is_refundable'])
                        <flux:badge size="sm" color="lime" icon="calendar-days">{{ __('booking_quotes.lines.refundable') }}</flux:badge>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</flux:card>
