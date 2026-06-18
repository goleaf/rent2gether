<div class="max-w-2xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('Extend Stay') }}</flux:heading>

    <flux:card class="space-y-2">
        <flux:text class="font-medium">{{ $booking->bed->title }}</flux:text>
        <flux:text size="sm" class="text-zinc-500">
            {{ __('Current checkout') }}: {{ $booking->check_out->format('M d, Y') }}
        </flux:text>
    </flux:card>

    <form wire:submit="submit" class="space-y-4">
        <flux:input type="date" wire:model.live="newCheckOut" label="{{ __('New checkout date') }}" :error="$errors->first('newCheckOut')" min="{{ $booking->check_out->addDay()->format('Y-m-d') }}" />

        @if($preview)
            <flux:card class="space-y-2">
                <flux:heading size="sm">{{ __('Preview') }}</flux:heading>
                <div class="text-sm space-y-1">
                    <div class="flex justify-between"><span class="text-zinc-500">{{ __('Extra nights') }}</span><span>{{ $preview['extra_nights'] }}</span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">{{ __('Extra cost') }}</span><span>&euro;{{ number_format($preview['extra_amount'], 2) }}</span></div>
                    <flux:separator />
                    <div class="flex justify-between font-semibold"><span>{{ __('New total') }}</span><span>&euro;{{ number_format($preview['new_total'], 2) }}</span></div>
                </div>
            </flux:card>
        @endif

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">{{ __('Request extension') }}</flux:button>
            <flux:button href="{{ route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" variant="ghost">{{ __('Back') }}</flux:button>
        </div>
    </form>
</div>
