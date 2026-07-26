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
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking.check_in') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="date" wire:model.change="checkIn" :error="$errors->first('checkIn')" icon="calendar-days" />
                    <flux:error name="checkIn" />
                </flux:field>
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking.check_out') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="date" wire:model.change="checkOut" :error="$errors->first('checkOut')" icon="calendar-days" />
                    <flux:error name="checkOut" />
                </flux:field>
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking.guests') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="number" wire:model.change="guestsCount" min="1" :error="$errors->first('guestsCount')" icon="user" />
                    <flux:error name="guestsCount" />
                </flux:field>
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking.message_to_host') }}</span>
                        </span>
                    </flux:label>
                    <flux:textarea wire:model.blur="guestMessage" rows="3" />
                    <flux:error name="guestMessage" />
                </flux:field>

                @if($errors->has('availability'))
                    <flux:badge color="red" icon="exclamation-triangle">{{ $errors->first('availability') }}</flux:badge>
                @endif

                <flux:button type="submit" variant="primary" class="w-full" icon="calendar-days">
                    {{ $bed->instant_book ? __('booking.book_now') : __('booking.request_booking') }}
                </flux:button>
            </form>
        </div>

        <div class="space-y-4">
            @if($priceSummary)
                <flux:card class="space-y-3">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking.price_breakdown') }}</span>
                        </span>
                    </flux:heading>
                    <div class="space-y-1 text-sm">
                        @foreach($priceSummary['rows'] as $line)
                            <div class="{{ $line['class'] }}">
                                <span>{{ $line['label'] }}</span>
                                <span>{{ $line['amount'] }}</span>
                            </div>
                        @endforeach
                        <flux:separator />
                        <div class="flex justify-between font-semibold text-base">
                            <span>{{ $priceSummary['total_label'] }}</span>
                            <span>{{ $priceSummary['total_amount'] }}</span>
                        </div>
                    </div>
                </flux:card>
            @endif

            @if($compatibilitySummary)
                <flux:card class="space-y-2">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking.compatibility') }}</span>
                        </span>
                    </flux:heading>
                    <div class="flex items-center gap-2">
                        <div class="text-2xl font-bold {{ $compatibilitySummary['score_class'] }}">
                            {{ $compatibilitySummary['score_label'] }}
                        </div>
                    </div>
                    @foreach($compatibilitySummary['warnings'] as $warning)
                        <flux:badge color="yellow" size="sm" icon="exclamation-triangle">{{ $warning }}</flux:badge>
                    @endforeach
                    @foreach($compatibilitySummary['matches'] as $match)
                        <flux:badge color="green" size="sm" icon="check-circle">{{ $match }}</flux:badge>
                    @endforeach
                </flux:card>
            @endif
        </div>
    </div>
</x-ui.page>
