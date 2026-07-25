<x-ui.page class="space-y-4">
    <flux:card class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('bookings.create.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('bookings.create.subtitle') }}</flux:text>
            </div>
            <flux:badge icon="calendar-days">{{ __('bookings.create.step', ['step' => $step]) }}</flux:badge>
        </div>

        @if ($sleepingPlaceTitle)
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <flux:text size="sm">{{ __('bookings.create.selected_place') }}</flux:text>
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $sleepingPlaceTitle }}</span>
                    </span>
                </flux:heading>
            </div>
        @endif

        @if ($quote && ! $summary)
            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                <flux:text size="sm">{{ $quote['quote_number'] }}</flux:text>
                <flux:heading size="md">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $quote['total_payable'] }}</span>
                    </span>
                </flux:heading>
            </div>

            <flux:button variant="primary" icon="calendar-days" wire:click="confirmInstantBooking" wire:loading.attr="disabled" class="w-full">
                {{ __('bookings.actions.confirm') }}
            </flux:button>
        @endif

        @if ($summary)
            <flux:badge color="{{ $summary['status_color'] }}" icon="calendar-days">{{ $summary['status'] }}</flux:badge>
        @endif
    </flux:card>

    @if ($sleepingPlaceId && ! $quote && ! $summary)
        <livewire:bookings.dates.date-selection-panel
            :sleeping-place="$sleepingPlaceId"
            :check-in-date="$checkInDate"
            :check-out-date="$checkOutDate"
            :guests-count="$guestsCount"
            :key="'booking-dates-'.$sleepingPlaceId"
        />
    @endif

    @if ($summary)
        <livewire:bookings.create.booking-confirmation-step :booking-id="$summary['id']" :key="'confirmation-'.$summary['id']" />
    @endif
</x-ui.page>
