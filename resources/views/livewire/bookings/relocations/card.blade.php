<x-ui.section>
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex items-start justify-between gap-3">
            <div>
                <flux:badge color="amber">{{ __('booking_relocations.components.guest_' . $variant) }}</flux:badge>
                <flux:heading size="lg" class="mt-3">{{ __('booking_relocations.title') }}</flux:heading>
                <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-300">
                    {{ __('booking_relocations.messages.history_preserved') }}
                </flux:text>
            </div>

            @if ($relocation)
                <flux:badge color="blue">{{ __('booking_relocations.statuses.' . $relocation->status) }}</flux:badge>
            @endif
        </div>

        @if ($booking)
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('booking_relocations.fields.current_sleeping_place') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">
                        {{ $booking->sleepingPlace?->display_name ?? $booking->sleepingPlace?->title ?? __('booking_relocations.empty.unknown_place') }}
                    </span>
                </div>

                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('booking_relocations.fields.current_room') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">
                        {{ $booking->room?->title ?? __('booking_relocations.empty.unknown_room') }}
                    </span>
                </div>
            </div>
        @endif

        <div class="mt-4 grid gap-3">
            <flux:select wire:model.change="reason" :label="__('booking_relocations.fields.reason')">
                @foreach (['noisy_neighbors', 'uncomfortable_bed', 'conflict_with_occupant', 'breakdown', 'guest_wants_more_private_room', 'guest_wants_cheaper', 'guest_wants_more_comfort', 'other'] as $reasonKey)
                    <flux:select.option value="{{ $reasonKey }}">{{ __('booking_relocations.reasons.' . $reasonKey) }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input type="date" wire:model.change="relocationDate" :label="__('booking_relocations.fields.relocation_date')" />
            <flux:textarea wire:model.blur="guestComment" :label="__('booking_relocations.fields.guest_comment')" rows="3" />

            <flux:button variant="primary" wire:click="requestRelocation" wire:loading.attr="disabled">
                {{ __('booking_relocations.actions.request_relocation') }}
            </flux:button>
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($relocations as $item)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-medium text-zinc-950 dark:text-white">{{ $item->relocation_number }}</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-300">
                            {{ __('booking_relocations.fields.relocation_date') }}:
                            {{ $item->relocation_date?->toDateString() }}
                        </p>
                    </div>
                    <flux:badge>{{ __('booking_relocations.statuses.' . $item->status) }}</flux:badge>
                </div>

                <div class="mt-3 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('booking_relocations.fields.current_sleeping_place') }}</span>
                        {{ $item->currentSleepingPlace?->display_name ?? $item->currentSleepingPlace?->title ?? __('booking_relocations.empty.unknown_place') }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('booking_relocations.fields.new_sleeping_place') }}</span>
                        {{ $item->newSleepingPlace?->display_name ?? $item->newSleepingPlace?->title ?? __('booking_relocations.empty.not_selected') }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('booking_relocations.fields.price_difference') }}</span>
                        {{ number_format((float) $item->price_difference_amount, 2) }} {{ $item->currency }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('booking_relocations.fields.price_difference_payer') }}</span>
                        {{ __('booking_relocations.payer_types.' . $item->price_difference_payer) }}
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-zinc-300 p-4 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                {{ __('booking_relocations.empty.no_relocations') }}
            </div>
        @endforelse
    </div>
</x-ui.section>
