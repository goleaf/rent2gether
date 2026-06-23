<flux:card class="space-y-3">
    <flux:heading size="sm">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('booking_dates.messages.available_check_out_dates') }}</span>
        </span>
    </flux:heading>

    @if($dates)
        <div class="grid gap-2 sm:grid-cols-2">
            @foreach($dates as $date)
                <div class="rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                    <flux:text size="sm">{{ $date['check_out'] }}</flux:text>
                    <flux:badge size="sm" icon="check-circle">{{ trans_choice('booking_dates.messages.nights_short', $date['nights'], ['count' => $date['nights']]) }}</flux:badge>
                </div>
            @endforeach
        </div>
    @else
        <flux:callout variant="secondary" :text="__('booking_dates.empty.select_check_in_first')"  icon="information-circle" />
    @endif
</flux:card>
