<x-ui.page class="space-y-6">
    <flux:heading size="xl">{{ __('booking.my_bookings') }}</flux:heading>

    <flux:tabs wire:model="tab">
        <flux:tab name="upcoming">{{ __('booking.tabs.upcoming') }}</flux:tab>
        <flux:tab name="active">{{ __('booking.tabs.active') }}</flux:tab>
        <flux:tab name="past">{{ __('booking.tabs.past') }}</flux:tab>
        <flux:tab name="cancelled">{{ __('booking.tabs.cancelled') }}</flux:tab>
    </flux:tabs>

    <div class="space-y-4">
        @forelse($this->bookings as $booking)
            <flux:card class="flex items-center justify-between">
                <div class="space-y-1">
                    <flux:heading size="sm">{{ $booking->bed->title }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-500">
                        {{ $booking->check_in->translatedFormat('d M') }} - {{ $booking->check_out->translatedFormat('d M Y') }}
                        &middot; {{ $booking->nights }} {{ __('booking.nights') }}
                    </flux:text>
                    <flux:text size="sm" class="text-zinc-500">
                        {{ $booking->bed->room->property->city }}
                    </flux:text>
                </div>
                <div class="flex items-center gap-3">
                    <flux:badge>{{ $booking->status->label() }}</flux:badge>
                    <flux:text class="font-semibold">&euro;{{ number_format($booking->total, 2) }}</flux:text>
                    <flux:button size="sm" href="{{ route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate>
                        {{ __('app.actions.view') }}
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <flux:card>
                <flux:text class="text-center text-zinc-500 py-8">{{ __('booking.empty') }}</flux:text>
            </flux:card>
        @endforelse

        {{ $this->bookings->links() }}
    </div>
</x-ui.page>
