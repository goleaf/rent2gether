<div class="space-y-4">
    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.date_selector.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('booking.date_selector.helper') }}</flux:text>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('booking.date_selector.fields.check_in') }}</span>
    </span>
</flux:label>
                <flux:input type="date" min="{{ now()->toDateString() }}" wire:model.change="checkIn" icon="calendar-days" />
                <flux:error name="checkIn" />
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('booking.date_selector.fields.check_out') }}</span>
    </span>
</flux:label>
                <flux:input type="date" min="{{ $checkIn ?: now()->toDateString() }}" wire:model.change="checkOut" icon="calendar-days" />
                <flux:error name="checkOut" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('booking.date_selector.fields.guests_count') }}</span>
    </span>
</flux:label>
            <flux:input type="number" min="1" inputmode="numeric" wire:model.change="guestsCount" icon="users" />
            <flux:error name="guestsCount" />
        </flux:field>

        <div wire:loading.delay wire:target="checkIn,checkOut,guestsCount,refreshQuote" class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
            {{ __('booking.date_selector.loading') }}
        </div>
    </flux:card>

    @if($unavailableDates)
        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.heading icon="exclamation-triangle" icon:variant="mini">{{ __('booking.date_selector.unavailable.title') }}</flux:callout.heading>
            <flux:callout.text>{{ __('booking.date_selector.unavailable.helper') }}</flux:callout.text>
        </flux:callout>

        <div class="space-y-2">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.date_selector.unavailable.dates') }}</span>
                </span>
            </flux:heading>
            <div class="flex flex-wrap gap-2">
                @foreach($unavailableDates as $date)
                    <flux:badge size="sm" icon="calendar-days">{{ \Carbon\CarbonImmutable::parse($date)->translatedFormat('d M') }}</flux:badge>
                @endforeach
            </div>
        </div>
    @endif

    @if($nearestRanges)
        <div class="space-y-2">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.date_selector.unavailable.nearest_ranges') }}</span>
                </span>
            </flux:heading>
            <div class="space-y-2">
                @foreach($nearestRanges as $range)
                    <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                        {{ __('booking.date_selector.unavailable.range_label', [
                            'check_in' => \Carbon\CarbonImmutable::parse($range['check_in'])->translatedFormat('d M'),
                            'check_out' => \Carbon\CarbonImmutable::parse($range['check_out'])->translatedFormat('d M'),
                            'nights' => $range['nights'],
                        ]) }}
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($quote)
        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('booking.date_selector.summary.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ trans_choice('booking.date_selector.summary.selected_nights', $quote['nights_count'], ['count' => $quote['nights_count']]) }}
                </flux:text>
            </div>

            <div class="grid grid-cols-2 gap-2 text-sm">
                <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                    <div class="text-zinc-500">{{ __('booking.date_selector.summary.calendar_days') }}</div>
                    <div class="font-medium">{{ trans_choice('booking.date_selector.summary.calendar_days_count', $quote['calendar_days_count'], ['count' => $quote['calendar_days_count']]) }}</div>
                </div>
                <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                    <div class="text-zinc-500">{{ __('booking.date_selector.summary.weekdays_weekends') }}</div>
                    <div class="font-medium">{{ __('booking.date_selector.summary.weekday_weekend_counts', ['weekdays' => $quote['weekday_count'], 'weekends' => $quote['weekend_count']]) }}</div>
                </div>
                <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                    <div class="text-zinc-500">{{ __('booking.date_selector.summary.check_in_weekday') }}</div>
                    <div class="font-medium">{{ \Carbon\CarbonImmutable::parse($checkIn)->translatedFormat('l') }}</div>
                </div>
                <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                    <div class="text-zinc-500">{{ __('booking.date_selector.summary.check_out_weekday') }}</div>
                    <div class="font-medium">{{ \Carbon\CarbonImmutable::parse($checkOut)->translatedFormat('l') }}</div>
                </div>
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('booking.date_selector.price.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('booking.date_selector.price.helper') }}</flux:text>
            </div>

            <div class="space-y-2 text-sm">
                @foreach($quote['line_items'] as $line)
                    <div class="flex items-start justify-between gap-3 {{ $line['type'] === 'total' ? 'border-t border-zinc-200 pt-2 text-base font-semibold dark:border-zinc-700' : '' }}">
                        <span>{{ __($line['label_key']) }}</span>
                        <span class="shrink-0">{{ $this->money($line['amount'], $line['currency']) }}</span>
                    </div>
                @endforeach
            </div>

            <div class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800 dark:bg-green-950/40 dark:text-green-200">
                {{ __('booking.date_selector.price.refund_summary', [
                    'refundable' => $this->money($quote['refundable_amount'], $quote['currency']),
                    'non_refundable' => $this->money($quote['non_refundable_amount'], $quote['currency']),
                ]) }}
            </div>
        </flux:card>

        @if($hasAdjustedDates)
            <flux:card class="space-y-3">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('booking.date_selector.date_prices.title') }}</span>
                    </span>
                </flux:heading>
                <div class="space-y-2 text-sm">
                    @foreach($quote['date_prices'] as $datePrice)
                        @if($datePrice['source'] !== 'base')
                            <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                                <span>
                                    {{ \Carbon\CarbonImmutable::parse($datePrice['date'])->translatedFormat('d M, l') }}
                                    <span class="block text-xs text-zinc-500">{{ __('booking.date_selector.date_prices.sources.'.$datePrice['source']) }}</span>
                                </span>
                                <span class="font-medium">{{ $this->money($datePrice['price'], $quote['currency']) }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </flux:card>
        @endif

        <flux:callout icon="calendar-days">
            <flux:callout.heading icon="calendar-days" icon:variant="mini">{{ __('booking.date_selector.deadlines.title') }}</flux:callout.heading>
            <flux:callout.text>
                {{ __('booking.date_selector.deadlines.cancellation', ['deadline' => \Carbon\CarbonImmutable::parse($quote['cancellation_deadline'])->translatedFormat('d M, H:i')]) }}
                {{ __('booking.date_selector.deadlines.payment', ['deadline' => \Carbon\CarbonImmutable::parse($quote['payment_deadline'])->translatedFormat('d M, H:i')]) }}
            </flux:callout.text>
        </flux:callout>

        @if($quote['warnings'])
            <div class="space-y-2">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="exclamation-triangle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('booking.date_selector.warnings.title') }}</span>
                    </span>
                </flux:heading>
                <div class="space-y-2">
                    @foreach($quote['warnings'] as $warning)
                        <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-700 dark:border-zinc-700 dark:text-zinc-300">
                            {{ __($warning) }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>
