<section class="mx-auto w-full max-w-xl space-y-4 px-4 py-4">
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex items-start justify-between gap-3">
            <div>
                <flux:badge color="red">{{ __('cancellations.components.host_' . $variant) }}</flux:badge>
                <flux:heading size="lg" class="mt-3">{{ __('cancellations.host_title') }}</flux:heading>
                <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-300">
                    {{ __('cancellations.messages.host_payout_notice') }}
                </flux:text>
            </div>

            @if ($cancellation)
                <flux:badge color="blue">{{ __('cancellations.statuses.' . $cancellation->status) }}</flux:badge>
            @endif
        </div>

        @if ($booking)
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.guest') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">{{ $booking->guest?->name ?? __('cancellations.empty.unknown_guest') }}</span>
                </div>

                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.sleeping_place') }}</span>
                    {{ $booking->sleepingPlace?->display_name ?? $booking->sleepingPlace?->title ?? __('cancellations.empty.unknown_place') }}
                </div>
            </div>

            <div class="mt-4 grid gap-3">
                <flux:select wire:model.change="reasonKey" :label="__('cancellations.fields.reason')">
                    <flux:select.option value="maintenance">{{ __('cancellations.reasons.maintenance') }}</flux:select.option>
                    <flux:select.option value="housing_problem">{{ __('cancellations.reasons.housing_problem') }}</flux:select.option>
                    <flux:select.option value="other">{{ __('cancellations.reasons.other') }}</flux:select.option>
                </flux:select>
                <flux:textarea wire:model.blur="hostComment" :label="__('cancellations.fields.comment')" rows="3" />
                <flux:button variant="danger" wire:click="cancelBooking" wire:loading.attr="disabled">
                    {{ __('cancellations.actions.cancel_booking') }}
                </flux:button>
            </div>
        @endif

        @if ($cancellation)
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.guest') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">{{ $cancellation->guest?->name ?? __('cancellations.empty.unknown_guest') }}</span>
                </div>

                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.total_refund') }}</span>
                    {{ number_format((float) $cancellation->total_refund_amount, 2) }} {{ $cancellation->currency }}
                </div>

                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.host_payout_adjustment') }}</span>
                    {{ number_format((float) $cancellation->host_payout_adjustment_amount, 2) }} {{ $cancellation->currency }}
                </div>

                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.calendar_release_status') }}</span>
                    {{ __('cancellations.calendar_release_statuses.' . $cancellation->calendar_release_status) }}
                </div>
            </div>
        @endif
    </div>

    <div class="space-y-3">
        @forelse ($cancellations as $item)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 text-sm dark:border-zinc-800 dark:bg-zinc-950">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-medium text-zinc-950 dark:text-white">{{ $item->guest?->name ?? __('cancellations.empty.unknown_guest') }}</p>
                        <p class="text-zinc-600 dark:text-zinc-300">
                            {{ $item->sleepingPlace?->display_name ?? $item->sleepingPlace?->title ?? __('cancellations.empty.unknown_place') }}
                        </p>
                    </div>
                    <flux:badge>{{ __('cancellations.statuses.' . $item->status) }}</flux:badge>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-zinc-300 p-4 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                {{ __('cancellations.empty.no_cancellations') }}
            </div>
        @endforelse
    </div>
</section>
