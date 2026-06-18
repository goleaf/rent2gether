<div class="max-w-4xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('My Bookings') }}</flux:heading>

    <flux:tabs wire:model="tab">
        <flux:tab name="upcoming">{{ __('Upcoming') }}</flux:tab>
        <flux:tab name="active">{{ __('Active') }}</flux:tab>
        <flux:tab name="past">{{ __('Past') }}</flux:tab>
        <flux:tab name="cancelled">{{ __('Cancelled') }}</flux:tab>
    </flux:tabs>

    <div class="space-y-4">
        @forelse($this->bookings as $booking)
            <flux:card class="flex items-center justify-between">
                <div class="space-y-1">
                    <flux:heading size="sm">{{ $booking->bed->title }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-500">
                        {{ $booking->check_in->format('M d') }} - {{ $booking->check_out->format('M d, Y') }}
                        &middot; {{ $booking->nights }} {{ __('nights') }}
                    </flux:text>
                    <flux:text size="sm" class="text-zinc-500">
                        {{ $booking->bed->room->property->city }}
                    </flux:text>
                </div>
                <div class="flex items-center gap-3">
                    <flux:badge>{{ $booking->status->label() }}</flux:badge>
                    <flux:text class="font-semibold">&euro;{{ number_format($booking->total, 2) }}</flux:text>
                    <flux:button size="sm" href="{{ route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $booking]) }}">
                        {{ __('View') }}
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <flux:card>
                <flux:text class="text-center text-zinc-500 py-8">{{ __('No bookings found.') }}</flux:text>
            </flux:card>
        @endforelse

        {{ $this->bookings->links() }}
    </div>
</div>
