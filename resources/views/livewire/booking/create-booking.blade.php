<div class="max-w-3xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('Book') }}: {{ $bed->title }}</flux:heading>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="space-y-4">
            <flux:card>
                <flux:heading size="sm">{{ $bed->room->property->title }}</flux:heading>
                <flux:text size="sm" class="text-zinc-500">{{ $bed->room->title }} &middot; {{ $bed->type->label() }}</flux:text>
                <flux:text size="sm" class="text-zinc-500">
                    <flux:icon name="map-pin" class="size-3.5 inline" />
                    {{ $bed->room->property->city }}
                </flux:text>
            </flux:card>

            <form wire:submit="book" class="space-y-4">
                <flux:input type="date" wire:model.live="checkIn" label="{{ __('Check-in') }}" :error="$errors->first('checkIn')" />
                <flux:input type="date" wire:model.live="checkOut" label="{{ __('Check-out') }}" :error="$errors->first('checkOut')" />
                <flux:input type="number" wire:model.live="guestCount" label="{{ __('Guests') }}" min="1" :error="$errors->first('guestCount')" />
                <flux:textarea wire:model="guestMessage" label="{{ __('Message to host') }}" rows="3" />

                @if($errors->has('availability'))
                    <flux:badge color="red">{{ $errors->first('availability') }}</flux:badge>
                @endif

                <flux:button type="submit" variant="primary" class="w-full">
                    {{ $bed->instant_book ? __('Book now') : __('Request booking') }}
                </flux:button>
            </form>
        </div>

        <div class="space-y-4">
            @if($priceBreakdown)
                <flux:card class="space-y-3">
                    <flux:heading size="sm">{{ __('Price breakdown') }}</flux:heading>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span>{{ $priceBreakdown['nights'] }} {{ __('nights') }}</span>
                            <span>&euro;{{ number_format($priceBreakdown['subtotal'], 2) }}</span>
                        </div>
                        @if($priceBreakdown['discount'] > 0)
                            <div class="flex justify-between text-green-600">
                                <span>{{ __('Discount') }}</span>
                                <span>-&euro;{{ number_format($priceBreakdown['discount'], 2) }}</span>
                            </div>
                        @endif
                        @if($priceBreakdown['cleaning_fee'] > 0)
                            <div class="flex justify-between">
                                <span>{{ __('Cleaning fee') }}</span>
                                <span>&euro;{{ number_format($priceBreakdown['cleaning_fee'], 2) }}</span>
                            </div>
                        @endif
                        @if($priceBreakdown['deposit'] > 0)
                            <div class="flex justify-between">
                                <span>{{ __('Deposit') }}</span>
                                <span>&euro;{{ number_format($priceBreakdown['deposit'], 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span>{{ __('Service fee') }}</span>
                            <span>&euro;{{ number_format($priceBreakdown['service_fee'], 2) }}</span>
                        </div>
                        <flux:separator />
                        <div class="flex justify-between font-semibold text-base">
                            <span>{{ __('Total') }}</span>
                            <span>&euro;{{ number_format($priceBreakdown['total'], 2) }}</span>
                        </div>
                    </div>
                </flux:card>
            @endif

            @if($compatibility)
                <flux:card class="space-y-2">
                    <flux:heading size="sm">{{ __('Compatibility') }}</flux:heading>
                    <div class="flex items-center gap-2">
                        <div class="text-2xl font-bold {{ $compatibility['score'] >= 70 ? 'text-green-600' : ($compatibility['score'] >= 40 ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ $compatibility['score'] }}%
                        </div>
                    </div>
                    @foreach($compatibility['warnings'] as $warning)
                        <flux:badge color="yellow" size="sm">{{ $warning }}</flux:badge>
                    @endforeach
                    @foreach($compatibility['matches'] as $match)
                        <flux:badge color="green" size="sm">{{ $match }}</flux:badge>
                    @endforeach
                </flux:card>
            @endif
        </div>
    </div>
</div>
