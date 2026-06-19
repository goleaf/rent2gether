<div class="max-w-3xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('booking.title') }} #{{ $booking->id }}</flux:heading>

    @if(session('success'))
        <flux:badge color="green">{{ session('success') }}</flux:badge>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('booking.details') }}</flux:heading>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.status') }}</span><flux:badge>{{ $booking->status->label() }}</flux:badge></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.bed') }}</span><span>{{ $booking->bed->title }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.room') }}</span><span>{{ $booking->bed->room->title }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.property') }}</span><span>{{ $booking->bed->room->property->title }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.check_in') }}</span><span>{{ $booking->check_in->translatedFormat('d M Y') }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.check_out') }}</span><span>{{ $booking->check_out->translatedFormat('d M Y') }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.nights') }}</span><span>{{ $booking->nights }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.guests') }}</span><span>{{ $booking->guest_count }}</span></div>
            </div>
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('booking.payment') }}</flux:heading>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.subtotal') }}</span><span>&euro;{{ number_format($booking->subtotal, 2) }}</span></div>
                @if($booking->discount > 0)
                    <div class="flex justify-between text-green-600"><span>{{ __('booking.discount') }}</span><span>-&euro;{{ number_format($booking->discount, 2) }}</span></div>
                @endif
                @if($booking->cleaning_fee > 0)
                    <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.cleaning') }}</span><span>&euro;{{ number_format($booking->cleaning_fee, 2) }}</span></div>
                @endif
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.service_fee') }}</span><span>&euro;{{ number_format($booking->service_fee, 2) }}</span></div>
                <flux:separator />
                <div class="flex justify-between font-semibold"><span>{{ __('booking.total') }}</span><span>&euro;{{ number_format($booking->total, 2) }}</span></div>
            </div>
        </flux:card>
    </div>

    @if($booking->guest_message)
        <flux:card>
            <flux:heading size="sm">{{ __('booking.your_message') }}</flux:heading>
            <flux:text>{{ $booking->guest_message }}</flux:text>
        </flux:card>
    @endif

    <div class="flex flex-wrap gap-3">
        @if($this->canCancel)
            <div>
                @if($cancellationPreview)
                    <flux:card class="mb-3 space-y-1">
                        <flux:text size="sm">{{ __('booking.refund') }}: &euro;{{ number_format($cancellationPreview['refund_amount'], 2) }}</flux:text>
                        <flux:text size="sm" class="text-zinc-500">{{ $cancellationPreview['reason'] }}</flux:text>
                    </flux:card>
                @endif
                <flux:button wire:click="cancel" variant="danger" wire:confirm="{{ __('booking.cancel_confirmation') }}">
                    {{ __('booking.cancel_booking') }}
                </flux:button>
            </div>
        @endif

        @if($this->canCheckIn)
            <flux:button wire:click="checkIn" variant="primary">{{ __('booking.checkin.action') }}</flux:button>
        @endif

        @if($this->canCheckOut)
            <flux:button wire:click="checkOut" variant="primary">{{ __('booking.checkout.action') }}</flux:button>
        @endif

        <flux:button href="{{ route('guest.bookings.index', ['locale' => app()->getLocale()]) }}" variant="ghost">
            {{ __('booking.back_to_bookings') }}
        </flux:button>
    </div>
</div>
