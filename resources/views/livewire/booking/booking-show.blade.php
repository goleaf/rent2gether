<div class="max-w-3xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('Booking') }} #{{ $booking->id }}</flux:heading>

    @if(session('success'))
        <flux:badge color="green">{{ session('success') }}</flux:badge>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('Details') }}</flux:heading>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('Status') }}</span><flux:badge>{{ $booking->status->label() }}</flux:badge></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('Bed') }}</span><span>{{ $booking->bed->title }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('Room') }}</span><span>{{ $booking->bed->room->title }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('Property') }}</span><span>{{ $booking->bed->room->property->title }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('Check-in') }}</span><span>{{ $booking->check_in->format('M d, Y') }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('Check-out') }}</span><span>{{ $booking->check_out->format('M d, Y') }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('Nights') }}</span><span>{{ $booking->nights }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('Guests') }}</span><span>{{ $booking->guest_count }}</span></div>
            </div>
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('Payment') }}</flux:heading>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('Subtotal') }}</span><span>&euro;{{ number_format($booking->subtotal, 2) }}</span></div>
                @if($booking->discount > 0)
                    <div class="flex justify-between text-green-600"><span>{{ __('Discount') }}</span><span>-&euro;{{ number_format($booking->discount, 2) }}</span></div>
                @endif
                @if($booking->cleaning_fee > 0)
                    <div class="flex justify-between"><span class="text-zinc-500">{{ __('Cleaning') }}</span><span>&euro;{{ number_format($booking->cleaning_fee, 2) }}</span></div>
                @endif
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('Service fee') }}</span><span>&euro;{{ number_format($booking->service_fee, 2) }}</span></div>
                <flux:separator />
                <div class="flex justify-between font-semibold"><span>{{ __('Total') }}</span><span>&euro;{{ number_format($booking->total, 2) }}</span></div>
            </div>
        </flux:card>
    </div>

    @if($booking->guest_message)
        <flux:card>
            <flux:heading size="sm">{{ __('Your message') }}</flux:heading>
            <flux:text>{{ $booking->guest_message }}</flux:text>
        </flux:card>
    @endif

    <div class="flex flex-wrap gap-3">
        @if($this->canCancel)
            <div>
                @if($cancellationPreview)
                    <flux:card class="mb-3 space-y-1">
                        <flux:text size="sm">{{ __('Refund') }}: &euro;{{ number_format($cancellationPreview['refund_amount'], 2) }}</flux:text>
                        <flux:text size="sm" class="text-zinc-500">{{ $cancellationPreview['reason'] }}</flux:text>
                    </flux:card>
                @endif
                <flux:button wire:click="cancel" variant="danger" wire:confirm="{{ __('Cancel this booking?') }}">
                    {{ __('Cancel booking') }}
                </flux:button>
            </div>
        @endif

        @if($this->canCheckIn)
            <flux:button wire:click="checkIn" variant="primary">{{ __('Check in') }}</flux:button>
        @endif

        @if($this->canCheckOut)
            <flux:button wire:click="checkOut" variant="primary">{{ __('Check out') }}</flux:button>
        @endif

        <flux:button href="{{ route('guest.bookings.index', ['locale' => app()->getLocale()]) }}" variant="ghost">
            {{ __('Back to bookings') }}
        </flux:button>
    </div>
</div>
