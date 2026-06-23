<div class="space-y-4">
    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking_dates.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('booking_dates.messages.select_dates_helper') }}</flux:text>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('booking_dates.fields.check_in_date') }}</flux:label>
                <flux:input type="date" min="{{ now()->toDateString() }}" wire:model.change="checkInDate" icon="calendar-days" />
                <flux:error name="checkInDate" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('booking_dates.fields.check_out_date') }}</flux:label>
                <flux:input type="date" min="{{ $checkInDate ?: now()->toDateString() }}" wire:model.change="checkOutDate" icon="calendar-days" />
                <flux:error name="checkOutDate" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>{{ __('booking_dates.fields.guests_count') }}</flux:label>
            <flux:input type="number" min="1" inputmode="numeric" wire:model.change="guestsCount" icon="users" />
        </flux:field>

        <div class="grid gap-3 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('booking_dates.fields.check_in_time') }}</flux:label>
                <flux:input type="time" wire:model.change="checkInTime" icon="calendar-days" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('booking_dates.fields.check_out_time') }}</flux:label>
                <flux:input type="time" wire:model.change="checkOutTime" icon="calendar-days" />
            </flux:field>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <flux:switch wire:model.change="earlyCheckInRequested" :label="__('booking_dates.fields.early_check_in')" />
            <flux:switch wire:model.change="lateCheckOutRequested" :label="__('booking_dates.fields.late_check_out')" />
            <flux:switch wire:model.change="flexibleCheckIn" :label="__('booking_dates.fields.flexible_check_in')" />
            <flux:switch wire:model.change="flexibleCheckOut" :label="__('booking_dates.fields.flexible_check_out')" />
        </div>

        <flux:field>
            <flux:label>{{ __('booking_dates.fields.check_in_comment') }}</flux:label>
            <flux:textarea rows="2" wire:model.blur="checkInComment" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('booking_dates.fields.check_out_comment') }}</flux:label>
            <flux:textarea rows="2" wire:model.blur="checkOutComment" />
        </flux:field>

        <div wire:loading.delay wire:target="checkInDate,checkOutDate,guestsCount,earlyCheckInRequested,lateCheckOutRequested,flexibleCheckIn,flexibleCheckOut,recalculateQuote">
            <flux:skeleton class="h-20 w-full" />
        </div>
    </flux:card>

    @if($checkInDate !== '' && $checkOutDate === '')
        <flux:card class="space-y-3">
            <div class="space-y-1">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('booking_dates.messages.available_check_out_dates') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('booking_dates.messages.choose_checkout_helper') }}</flux:text>
            </div>

            @if($availableCheckoutDates)
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach($availableCheckoutDates as $date)
                        <flux:button type="button" variant="outline" wire:click="selectCheckoutDate('{{ $date['check_out'] }}')" class="justify-between" icon="chat-bubble-left-right">
                            <span>{{ $date['check_out'] }}</span>
                            <flux:badge size="sm" icon="calendar-days">{{ trans_choice('booking_dates.messages.nights_short', $date['nights'], ['count' => $date['nights']]) }}</flux:badge>
                        </flux:button>
                    @endforeach
                </div>
            @else
                <flux:callout variant="warning" icon="exclamation-triangle" :text="__('booking_dates.empty.no_checkout_dates')" />
            @endif
        </flux:card>
    @endif

    @if($quote)
        <livewire:bookings.dates.stay-length-summary :quote-id="$quote->id" :key="'stay-length-'.$quote->id" />
        <livewire:bookings.quotes.booking-quote-summary :quote-id="$quote->id" :key="'quote-summary-'.$quote->id" />
        <livewire:bookings.quotes.booking-quote-line-breakdown :quote-id="$quote->id" :key="'quote-lines-'.$quote->id" />
        <livewire:bookings.quotes.booking-quote-validation-messages :quote-id="$quote->id" :key="'quote-validation-'.$quote->id" />
        <livewire:bookings.quotes.cancellation-date-preview :quote-id="$quote->id" :key="'quote-cancellation-'.$quote->id" />
        <livewire:bookings.quotes.timeline-date-preview :quote-id="$quote->id" :key="'quote-timeline-'.$quote->id" />
        <livewire:bookings.quotes.quote-expired-banner :quote-id="$quote->id" :key="'quote-expired-'.$quote->id" />
        <livewire:bookings.dates.date-suggestions-panel :quote-id="$quote->id" :key="'date-suggestions-'.$quote->id" />

        <flux:card class="sticky bottom-3 z-10 space-y-3 border-zinc-200 bg-white/95 backdrop-blur dark:border-zinc-700 dark:bg-zinc-950/95">
            <div class="flex items-center justify-between gap-3">
                <flux:text size="sm">{{ __('booking_quotes.price.total_payable') }}</flux:text>
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="banknotes" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $formattedTotal }}</span>
                    </span>
                </flux:heading>
            </div>

            @if((float) $quote->deposit_amount > 0)
                <flux:callout variant="success" :text="__('booking_quotes.messages.deposit_refundable', ['amount' => $formattedDeposit])"  icon="check-circle" />
            @endif

            @if($isRequestOnly)
                <flux:button type="button" variant="primary" class="w-full" wire:click="sendRequest" wire:loading.attr="disabled" icon="paper-airplane">
                    {{ __('booking_quotes.actions.send_request') }}
                </flux:button>
            @else
                <flux:button type="button" variant="primary" class="w-full" wire:click="confirmBooking" wire:loading.attr="disabled" icon="arrow-right">
                    {{ __('booking_quotes.actions.continue_to_booking') }}
                </flux:button>
            @endif
        </flux:card>
    @endif
</div>
