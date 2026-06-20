@php
    $money = static fn (float|int|string $amount, string $currency): string => \Illuminate\Support\Number::currency((float) $amount, $currency, app()->getLocale());
@endphp

<div class="mx-auto max-w-3xl space-y-5 px-4 py-4 pb-28 sm:px-6">
    <div class="space-y-2">
        <flux:button
            variant="ghost"
            size="sm"
            icon="arrow-left"
            href="{{ route('places.show', ['locale' => app()->getLocale(), 'sleepingPlace' => $sleepingPlaceId]) }}"
            wire:navigate
        >
            {{ __('booking.flow.actions.back_to_place') }}
        </flux:button>

        <flux:badge color="emerald">{{ $bookingMode }}</flux:badge>
        <flux:heading size="xl" level="1">{{ __('booking.flow.title') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('booking.flow.helper', ['title' => $placeTitle]) }}</flux:text>
    </div>

    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="lg">{{ __('booking.flow.dates.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('booking.flow.dates.helper') }}</flux:text>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('booking.date_selector.fields.check_in') }}</flux:label>
                <flux:input type="date" min="{{ now()->toDateString() }}" wire:model.change="checkIn" />
                <flux:error name="checkIn" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('booking.date_selector.fields.check_out') }}</flux:label>
                <flux:input type="date" min="{{ $checkIn ?: now()->toDateString() }}" wire:model.change="checkOut" />
                <flux:error name="checkOut" />
            </flux:field>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <flux:field>
                <flux:label>{{ __('booking.date_selector.fields.guests_count') }}</flux:label>
                <flux:input type="number" min="1" inputmode="numeric" wire:model.change="guestsCount" />
                <flux:error name="guestsCount" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('booking.flow.fields.check_in_time') }}</flux:label>
                <flux:input type="time" wire:model.change="checkInTime" />
                <flux:error name="checkInTime" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('booking.flow.fields.arrival_time') }}</flux:label>
                <flux:input type="time" wire:model.change="arrivalTime" />
                <flux:error name="arrivalTime" />
            </flux:field>
        </div>

        <div wire:loading.delay wire:target="checkIn,checkOut,guestsCount,refreshQuote" class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
            {{ __('booking.flow.dates.loading') }}
        </div>

        @if($availabilityWarning)
            <flux:callout color="amber" icon="exclamation-triangle">
                <flux:callout.heading>{{ __('booking.flow.dates.warning_title') }}</flux:callout.heading>
                <flux:callout.text>{{ $availabilityWarning }}</flux:callout.text>
            </flux:callout>

            @if($unavailableDates)
                <div class="flex flex-wrap gap-2">
                    @foreach($unavailableDates as $date)
                        <flux:badge size="sm">{{ \Carbon\CarbonImmutable::parse($date)->translatedFormat('d M') }}</flux:badge>
                    @endforeach
                </div>
            @endif
        @endif
    </flux:card>

    @if($quote)
        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('booking.flow.price.title') }}</flux:heading>
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
                    <div class="text-zinc-500">{{ __('booking.deposit') }}</div>
                    <div class="font-medium">{{ $money($quote['deposit_amount'], $quote['currency']) }}</div>
                </div>
            </div>

            <div class="space-y-2 text-sm">
                @foreach($quote['line_items'] as $line)
                    <div class="flex items-start justify-between gap-3 {{ $line['type'] === 'total' ? 'border-t border-zinc-200 pt-2 text-base font-semibold dark:border-zinc-700' : '' }}">
                        <span>{{ __($line['label_key']) }}</span>
                        <span class="shrink-0">{{ $money($line['amount'], $line['currency']) }}</span>
                    </div>
                @endforeach
            </div>

            <flux:callout icon="information-circle">
                <flux:callout.heading>{{ __('booking.flow.price.refund_title') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('booking.date_selector.price.refund_summary', [
                        'refundable' => $money($quote['refundable_amount'], $quote['currency']),
                        'non_refundable' => $money($quote['non_refundable_amount'], $quote['currency']),
                    ]) }}
                </flux:callout.text>
            </flux:callout>
        </flux:card>
    @else
        <div class="rounded-lg bg-zinc-50 px-3 py-3 text-sm text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">
            {{ __('booking.flow.price.empty') }}
        </div>
    @endif

    <livewire:hints.before-booking-hints
        :sleeping-place-id="$sleepingPlaceId"
        :check-in="$checkIn"
        :check-out="$checkOut"
        :key="'before-booking-hints-'.$sleepingPlaceId.'-'.$checkIn.'-'.$checkOut"
        lazy
    />

    <livewire:bookings.guest-intake.guest-intake-wizard :sleeping-place-id="$sleepingPlaceId" :key="'guest-intake-'.$sleepingPlaceId" />

    <livewire:booking.booking-rules-accept :sleeping-place-id="$sleepingPlaceId" :key="'booking-rules-'.$sleepingPlaceId" />
    <flux:error name="rulesAccepted" />

    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="lg">{{ __('booking.flow.message.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('booking.flow.message.helper') }}</flux:text>
        </div>

        <flux:field>
            <flux:label>{{ __('booking.flow.fields.guest_message') }}</flux:label>
            <flux:textarea rows="4" wire:model.blur="guestMessage" />
            <flux:error name="guestMessage" />
        </flux:field>
    </flux:card>

    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="lg">{{ __('booking.flow.profile.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('booking.flow.profile.helper') }}</flux:text>
        </div>

        <div class="grid gap-2">
            @foreach($profileChecklist as $item)
                <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                    <span>{{ $item['label'] }}</span>
                    <flux:badge color="{{ $item['done'] ? 'green' : 'zinc' }}">
                        {{ $item['done'] ? __('booking.flow.profile.ready') : __('booking.flow.profile.can_update_later') }}
                    </flux:badge>
                </div>
            @endforeach
        </div>

        <flux:checkbox wire:model.change="profileReady" label="{{ __('booking.flow.fields.profile_ready') }}" />
        <flux:error name="profileReady" />
    </flux:card>

    <div class="fixed inset-x-0 bottom-0 z-30 border-t border-zinc-200 bg-white/95 px-4 py-3 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95 sm:static sm:rounded-lg sm:border sm:px-0 sm:py-0 sm:backdrop-blur-none">
        <div class="mx-auto flex max-w-3xl items-center gap-3 sm:block">
            <div class="min-w-0 flex-1 sm:hidden">
                <div class="text-xs text-zinc-500">{{ __('booking.total') }}</div>
                <div class="truncate text-sm font-semibold">
                    @if($quote)
                        {{ $money($quote['total_amount'], $quote['currency']) }}
                    @else
                        {{ __('booking.flow.price.pending') }}
                    @endif
                </div>
            </div>

            <flux:button
                type="button"
                variant="primary"
                class="w-full data-loading:opacity-70 sm:w-full"
                wire:click="submit"
                wire:loading.attr="disabled"
                wire:target="submit"
            >
                <span wire:loading.remove wire:target="submit">{{ __('booking.flow.actions.submit') }}</span>
                <span wire:loading wire:target="submit">{{ __('booking.flow.actions.submitting') }}</span>
            </flux:button>
        </div>
    </div>
</div>
