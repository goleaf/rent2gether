<x-ui.section>
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex items-start justify-between gap-3">
            <div>
                <flux:badge color="amber">{{ __('booking_relocations.components.host_' . $variant) }}</flux:badge>
                <flux:heading size="lg" class="mt-3">{{ __('booking_relocations.host_title') }}</flux:heading>
                <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-300">
                    {{ __('booking_relocations.messages.old_place_not_released_until_checked') }}
                </flux:text>
            </div>

            @if ($relocation)
                <flux:badge color="blue">{{ __('booking_relocations.statuses.' . $relocation->status) }}</flux:badge>
            @endif
        </div>

        @if ($relocation)
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('booking_relocations.fields.guest') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">{{ $relocation->guest?->name ?? __('booking_relocations.empty.unknown_guest') }}</span>
                </div>

                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('booking_relocations.fields.reason') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">{{ __('booking_relocations.reasons.' . $relocation->reason) }}</span>
                </div>

                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('booking_relocations.fields.current_sleeping_place') }}</span>
                    {{ $relocation->currentSleepingPlace?->display_name ?? $relocation->currentSleepingPlace?->title ?? __('booking_relocations.empty.unknown_place') }}
                </div>

                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('booking_relocations.fields.new_sleeping_place') }}</span>
                    {{ $relocation->newSleepingPlace?->display_name ?? $relocation->newSleepingPlace?->title ?? __('booking_relocations.empty.not_selected') }}
                </div>
            </div>

            <div class="mt-4 rounded-md bg-zinc-50 p-3 text-sm dark:bg-zinc-900">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('booking_relocations.fields.price_difference') }}</span>
                        {{ number_format((float) $relocation->price_difference_amount, 2) }} {{ $relocation->currency }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('booking_relocations.fields.additional_payment') }}</span>
                        {{ number_format((float) $relocation->additional_payment_amount, 2) }} {{ $relocation->currency }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('booking_relocations.fields.refund_amount') }}</span>
                        {{ number_format((float) $relocation->refund_amount, 2) }} {{ $relocation->currency }}
                    </div>
                </div>
            </div>

            <div class="mt-4 grid gap-3">
                <flux:textarea wire:model.blur="hostMessage" :label="__('booking_relocations.fields.host_comment')" rows="3" />
                <div class="grid grid-cols-2 gap-2">
                    <flux:button variant="primary" wire:click="approve" wire:loading.attr="disabled">
                        {{ __('booking_relocations.actions.approve') }}
                    </flux:button>
                    <flux:button variant="danger" wire:click="reject" wire:loading.attr="disabled">
                        {{ __('booking_relocations.actions.reject') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </div>

    <div class="space-y-3">
        @forelse ($relocations as $item)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-medium text-zinc-950 dark:text-white">{{ $item->guest?->name ?? __('booking_relocations.empty.unknown_guest') }}</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $item->currentRoom?->title ?? __('booking_relocations.empty.unknown_room') }}
                            ·
                            {{ $item->currentSleepingPlace?->display_name ?? $item->currentSleepingPlace?->title ?? __('booking_relocations.empty.unknown_place') }}
                        </p>
                    </div>
                    <flux:badge>{{ __('booking_relocations.statuses.' . $item->status) }}</flux:badge>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-zinc-300 p-4 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                {{ __('booking_relocations.empty.no_relocations') }}
            </div>
        @endforelse
    </div>
</x-ui.section>
