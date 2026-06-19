<div class="max-w-2xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('booking.extension.title') }}</flux:heading>

    <flux:card class="space-y-2">
        <flux:text class="font-medium">{{ $booking->bed->title }}</flux:text>
        <flux:text size="sm" class="text-zinc-500">
            {{ __('booking.extension.current_checkout') }}: {{ $booking->check_out->translatedFormat('d M Y') }}
        </flux:text>
    </flux:card>

    <form wire:submit="submit" class="space-y-4">
        <flux:input type="date" wire:model.live="newCheckOut" label="{{ __('booking.extension.new_checkout_date') }}" :error="$errors->first('newCheckOut')" min="{{ $booking->check_out->addDay()->format('Y-m-d') }}" />

        @if($preview)
            <flux:card class="space-y-2">
                <flux:heading size="sm">{{ __('booking.extension.preview') }}</flux:heading>
                <div class="text-sm space-y-1">
                    <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.extension.extra_nights') }}</span><span>{{ $preview['extra_nights'] }}</span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.extension.extra_cost') }}</span><span>&euro;{{ number_format($preview['extra_amount'], 2) }}</span></div>
                    <flux:separator />
                    <div class="flex justify-between font-semibold"><span>{{ __('booking.extension.new_total') }}</span><span>&euro;{{ number_format($preview['new_total'], 2) }}</span></div>
                </div>
            </flux:card>
        @endif

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">{{ __('booking.extension.request') }}</flux:button>
            <flux:button href="{{ route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" variant="ghost">{{ __('app.actions.back') }}</flux:button>
        </div>
    </form>
</div>
