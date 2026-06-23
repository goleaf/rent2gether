<x-ui.page class="space-y-6">
    <flux:heading size="xl">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('booking.my_bookings') }}</span>
        </span>
    </flux:heading>

    <flux:tabs wire:model="tab">
        <flux:tab name="upcoming" icon="calendar-days">{{ __('booking.tabs.upcoming') }}</flux:tab>
        <flux:tab name="active" icon="play-circle">{{ __('booking.tabs.active') }}</flux:tab>
        <flux:tab name="past" icon="archive-box">{{ __('booking.tabs.past') }}</flux:tab>
        <flux:tab name="cancelled" icon="x-circle">{{ __('booking.tabs.cancelled') }}</flux:tab>
    </flux:tabs>

    <div class="space-y-4">
        @forelse($this->bookings as $booking)
            <flux:card class="flex items-center justify-between">
                <div class="space-y-1">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ $booking->bed->title }}</span>
                        </span>
                    </flux:heading>
                    <flux:text size="sm" class="text-zinc-500">
                        {{ $booking->check_in->translatedFormat('d M') }} - {{ $booking->check_out->translatedFormat('d M Y') }}
                        &middot; {{ $booking->nights }} {{ __('booking.nights') }}
                    </flux:text>
                    <flux:text size="sm" class="text-zinc-500">
                        {{ $booking->bed->room->property->city }}
                    </flux:text>
                </div>
                <div class="flex items-center gap-3">
                    <flux:badge icon="calendar-days">{{ $booking->status->label() }}</flux:badge>
                    <flux:text class="font-semibold">&euro;{{ number_format($booking->total, 2) }}</flux:text>
                    <flux:button size="sm" href="{{ route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate icon="eye">
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
