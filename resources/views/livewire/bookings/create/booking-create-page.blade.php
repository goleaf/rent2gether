<div class="mx-auto w-full max-w-3xl space-y-4 px-4 py-4">
    <flux:card class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('bookings.create.title') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('bookings.create.subtitle') }}</flux:text>
            </div>
            <flux:badge>{{ __('bookings.create.step', ['step' => $step]) }}</flux:badge>
        </div>

        @if ($quote && ! $summary)
            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                <flux:text size="sm">{{ $quote['quote_number'] }}</flux:text>
                <flux:heading size="md">{{ $quote['total_payable'] }}</flux:heading>
            </div>

            <flux:button variant="primary" icon="check" wire:click="confirmInstantBooking" wire:loading.attr="disabled" class="w-full">
                {{ __('bookings.actions.confirm') }}
            </flux:button>
        @endif

        @if ($summary)
            <flux:badge color="{{ $summary['status_color'] }}">{{ $summary['status'] }}</flux:badge>
        @endif
    </flux:card>

    @if ($summary)
        <livewire:bookings.create.booking-confirmation-step :booking-id="$summary['id']" :key="'confirmation-'.$summary['id']" />
    @endif
</div>
