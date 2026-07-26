<flux:card class="space-y-3">
    <flux:heading size="sm">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('booking_dates.messages.available_check_out_dates') }}</span>
        </span>
    </flux:heading>

    @if(($earliestCheckoutDate ?? null) || ($latestCheckoutDate ?? null))
        <div class="grid gap-2 sm:grid-cols-2">
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">{{ __('booking_dates.messages.earliest_checkout') }}</flux:text>
                <flux:text size="sm" class="font-medium">{{ $earliestCheckoutDate ?: __('booking_dates.empty.no_checkout_dates') }}</flux:text>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">{{ __('booking_dates.messages.latest_checkout') }}</flux:text>
                <flux:text size="sm" class="font-medium">{{ $latestCheckoutDate ?: __('booking_dates.empty.no_checkout_dates') }}</flux:text>
            </div>
        </div>
    @endif

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

    @if($unavailableCheckoutDates ?? [])
        <div class="space-y-2">
            <flux:text size="sm" class="font-medium">{{ __('booking_dates.messages.blocked_checkout_dates') }}</flux:text>
            @foreach($unavailableCheckoutDates as $date)
                <flux:callout
                    variant="secondary"
                    icon="information-circle"
                    :text="__('booking_dates.messages.checkout_unavailable_reason', [
                        'date' => $date['check_out'],
                        'reason' => __($date['message_keys'][0] ?? 'booking_dates.validation.sleeping_place_unavailable'),
                    ])"
                />
            @endforeach
        </div>
    @endif
</flux:card>
