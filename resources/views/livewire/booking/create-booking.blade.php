<x-ui.page class="space-y-6">
    <flux:heading size="xl">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('booking.book') }}: {{ $bed->title }}</span>
        </span>
    </flux:heading>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="space-y-4">
            <flux:card>
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $bed->room->property->title }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-500">{{ $bed->room->title }} &middot; {{ $bed->type->label() }}</flux:text>
                <flux:text size="sm" class="text-zinc-500">
                    <flux:icon name="map-pin" variant="mini" class="size-3.5 inline" />
                    {{ $bed->room->property->city }}
                </flux:text>
            </flux:card>

            <form wire:submit="book" class="space-y-4">
                <flux:input type="date" wire:model.change="checkIn" label="{{ __('booking.check_in') }}" :error="$errors->first('checkIn')" icon="calendar-days" />
                <flux:input type="date" wire:model.change="checkOut" label="{{ __('booking.check_out') }}" :error="$errors->first('checkOut')" icon="calendar-days" />
                <flux:input type="number" wire:model.change="guestCount" label="{{ __('booking.guests') }}" min="1" :error="$errors->first('guestCount')" icon="user" />
                <flux:textarea wire:model.blur="guestMessage" label="{{ __('booking.message_to_host') }}" rows="3" />

                @if($errors->has('availability'))
                    <flux:badge color="red" icon="exclamation-triangle">{{ $errors->first('availability') }}</flux:badge>
                @endif

                <flux:button type="submit" variant="primary" class="w-full" icon="calendar-days">
                    {{ $bed->instant_book ? __('booking.book_now') : __('booking.request_booking') }}
                </flux:button>
            </form>
        </div>

        <div class="space-y-4">
            @if($priceBreakdown)
                <flux:card class="space-y-3">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking.price_breakdown') }}</span>
                        </span>
                    </flux:heading>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span>{{ $priceBreakdown['nights'] }} {{ __('booking.nights') }}</span>
                            <span>&euro;{{ number_format($priceBreakdown['subtotal'], 2) }}</span>
                        </div>
                        @if($priceBreakdown['discount'] > 0)
                            <div class="flex justify-between text-green-600">
                                <span>{{ __('booking.discount') }}</span>
                                <span>-&euro;{{ number_format($priceBreakdown['discount'], 2) }}</span>
                            </div>
                        @endif
                        @if($priceBreakdown['cleaning_fee'] > 0)
                            <div class="flex justify-between">
                                <span>{{ __('booking.cleaning_fee') }}</span>
                                <span>&euro;{{ number_format($priceBreakdown['cleaning_fee'], 2) }}</span>
                            </div>
                        @endif
                        @if($priceBreakdown['deposit'] > 0)
                            <div class="flex justify-between">
                                <span>{{ __('booking.deposit') }}</span>
                                <span>&euro;{{ number_format($priceBreakdown['deposit'], 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span>{{ __('booking.service_fee') }}</span>
                            <span>&euro;{{ number_format($priceBreakdown['service_fee'], 2) }}</span>
                        </div>
                        <flux:separator />
                        <div class="flex justify-between font-semibold text-base">
                            <span>{{ __('booking.total') }}</span>
                            <span>&euro;{{ number_format($priceBreakdown['total'], 2) }}</span>
                        </div>
                    </div>
                </flux:card>
            @endif

            @if($compatibility)
                <flux:card class="space-y-2">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking.compatibility') }}</span>
                        </span>
                    </flux:heading>
                    <div class="flex items-center gap-2">
                        <div class="text-2xl font-bold {{ $compatibility['score'] >= 70 ? 'text-green-600' : ($compatibility['score'] >= 40 ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ $compatibility['score'] }}%
                        </div>
                    </div>
                    @foreach($compatibility['warnings'] as $warning)
                        <flux:badge color="yellow" size="sm" icon="exclamation-triangle">{{ $warning }}</flux:badge>
                    @endforeach
                    @foreach($compatibility['matches'] as $match)
                        <flux:badge color="green" size="sm" icon="check-circle">{{ $match }}</flux:badge>
                    @endforeach
                </flux:card>
            @endif
        </div>
    </div>
</x-ui.page>
