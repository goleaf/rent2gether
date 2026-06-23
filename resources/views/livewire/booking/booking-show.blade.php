<x-ui.page class="space-y-6">
    <flux:heading size="xl">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('booking.title') }} #{{ $booking->id }}</span>
        </span>
    </flux:heading>

    @if(session('success'))
        <flux:badge color="green" icon="check-circle">{{ session('success') }}</flux:badge>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:card class="space-y-3">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.details') }}</span>
                </span>
            </flux:heading>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.status') }}</span><flux:badge icon="calendar-days">{{ $booking->status->label() }}</flux:badge></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.payment_page.summary.payment_status') }}</span><flux:badge icon="calendar-days">{{ $booking->payment_status->label() }}</flux:badge></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.bed') }}</span><span>{{ $placeTitle }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.room') }}</span><span>{{ $roomTitle }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.property') }}</span><span>{{ $propertyTitle }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.check_in') }}</span><span>{{ $booking->check_in->translatedFormat('d M Y') }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.check_out') }}</span><span>{{ $booking->check_out->translatedFormat('d M Y') }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.nights') }}</span><span>{{ $booking->nights }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.guests') }}</span><span>{{ $booking->guests_count }}</span></div>
            </div>
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.payment') }}</span>
                </span>
            </flux:heading>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.subtotal') }}</span><span>{{ $this->money($booking->subtotal_amount ?: $booking->subtotal, $booking->currency) }}</span></div>
                @if($booking->discount_amount > 0)
                    <div class="flex justify-between text-green-600"><span>{{ __('booking.discount') }}</span><span>-{{ $this->money($booking->discount_amount, $booking->currency) }}</span></div>
                @endif
                @if(($booking->cleaning_fee_amount ?: $booking->cleaning_fee) > 0)
                    <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.cleaning') }}</span><span>{{ $this->money($booking->cleaning_fee_amount ?: $booking->cleaning_fee, $booking->currency) }}</span></div>
                @endif
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.service_fee') }}</span><span>{{ $this->money($booking->service_fee_amount ?: $booking->service_fee, $booking->currency) }}</span></div>
                <flux:separator />
                <div class="flex justify-between font-semibold"><span>{{ __('booking.total') }}</span><span>{{ $this->money($booking->total_amount ?: $booking->total, $booking->currency) }}</span></div>
            </div>
        </flux:card>
    </div>

    @if($booking->guest_message)
        <flux:card>
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.your_message') }}</span>
                </span>
            </flux:heading>
            <flux:text>{{ $booking->guest_message }}</flux:text>
        </flux:card>
    @endif

    <div class="flex flex-wrap gap-3">
        @if($this->canCancel)
            <div>
                @if($cancellationPreview)
                    <flux:card class="mb-3 space-y-1">
                        <flux:text size="sm">{{ __('booking.refund') }}: {{ $this->money($cancellationPreview['refund_amount'], $booking->currency) }}</flux:text>
                        <flux:text size="sm" class="text-zinc-500">{{ $cancellationPreview['reason'] }}</flux:text>
                    </flux:card>
                @endif
                <flux:button href="{{ route('guest.bookings.cancel', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="danger" icon="x-mark">
                    {{ __('booking.cancel_booking') }}
                </flux:button>
            </div>
        @endif

        @if($this->canCheckIn)
            <flux:button wire:click="confirmCheckIn" variant="primary" icon="key">{{ __('booking.checkin.action') }}</flux:button>
        @endif

        @if($this->canCheckOut)
            <flux:button wire:click="confirmCheckOut" variant="primary" icon="clipboard-document-check">{{ __('booking.checkout.action') }}</flux:button>
        @endif

        @if($this->canPay)
            <flux:button href="{{ route('guest.bookings.payment', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" variant="primary" wire:navigate icon="credit-card">
                {{ __('booking.payment_page.actions.open_payment') }}
            </flux:button>
        @endif

        <flux:button href="{{ route('guest.bookings.index', ['locale' => app()->getLocale()]) }}" variant="ghost" wire:navigate icon="arrow-left">
            {{ __('booking.back_to_bookings') }}
        </flux:button>
    </div>
</x-ui.page>
