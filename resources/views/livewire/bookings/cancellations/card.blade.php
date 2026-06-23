<x-ui.section>
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex items-start justify-between gap-3">
            <div>
                <flux:badge color="red" icon="exclamation-triangle">{{ __('cancellations.components.guest_' . $variant) }}</flux:badge>
                <flux:heading size="lg" class="mt-3">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('cancellations.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-300">
                    {{ __('cancellations.messages.refund_preview_notice') }}
                </flux:text>
            </div>

            @if ($cancellation)
                <flux:badge color="blue" icon="calendar-days">{{ __('cancellations.statuses.' . $cancellation->status) }}</flux:badge>
            @elseif ($preview)
                <flux:badge color="amber" icon="exclamation-triangle">{{ __('cancellations.preview_statuses.' . $preview->status) }}</flux:badge>
            @endif
        </div>

        @if ($booking)
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.sleeping_place') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">
                        {{ $booking->sleepingPlace?->display_name ?? $booking->sleepingPlace?->title ?? __('cancellations.empty.unknown_place') }}
                    </span>
                </div>

                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.dates') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">
                        {{ $booking->check_in_date?->toDateString() }} - {{ $booking->check_out_date?->toDateString() }}
                    </span>
                </div>
            </div>
        @endif

        @if (! $preview && ! $cancellation)
            <div class="mt-4 grid gap-3">
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('cancellations.fields.reason') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="reasonKey">
                    @foreach ($reasonOptions as $reason)
                        <flux:select.option value="{{ $reason }}">{{ __('cancellations.reasons.' . $reason) }}</flux:select.option>
                    @endforeach
                </flux:select>
                    <flux:error name="reasonKey" />
                </flux:field>

                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('cancellations.fields.comment') }}</span>
                        </span>
                    </flux:label>
                    <flux:textarea wire:model.blur="comment" rows="3" />
                    <flux:error name="comment" />
                </flux:field>

                <flux:button variant="primary" wire:click="createPreview" wire:loading.attr="disabled" icon="x-mark">
                    {{ __('cancellations.actions.create_preview') }}
                </flux:button>
            </div>
        @endif

        @if ($preview)
            <div class="mt-4 rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                <div class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.accommodation_refund') }}</span>
                        {{ number_format((float) $preview->accommodation_refund_amount, 2) }} {{ $preview->currency }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.cleaning_fee_refund') }}</span>
                        {{ number_format((float) $preview->cleaning_fee_refund_amount, 2) }} {{ $preview->currency }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.service_fee_refund') }}</span>
                        {{ number_format((float) $preview->service_fee_refund_amount, 2) }} {{ $preview->currency }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.deposit_refund') }}</span>
                        {{ number_format((float) $preview->deposit_refund_amount, 2) }} {{ $preview->currency }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.total_refund') }}</span>
                        <span class="font-medium text-zinc-950 dark:text-white">{{ number_format((float) $preview->total_refund_amount, 2) }} {{ $preview->currency }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.non_refundable_amount') }}</span>
                        {{ number_format((float) $preview->total_non_refundable_amount, 2) }} {{ $preview->currency }}
                    </div>
                </div>
            </div>

            <flux:button class="mt-4 w-full" variant="danger" wire:click="confirmCancellation" wire:loading.attr="disabled" icon="x-mark">
                {{ __('cancellations.actions.confirm_cancellation') }}
            </flux:button>
        @endif

        @if ($cancellation)
            <div class="mt-4 rounded-md bg-zinc-50 p-3 text-sm dark:bg-zinc-900">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.total_refund') }}</span>
                        {{ number_format((float) $cancellation->total_refund_amount, 2) }} {{ $cancellation->currency }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.calendar_release_status') }}</span>
                        {{ __('cancellations.calendar_release_statuses.' . $cancellation->calendar_release_status) }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.refund_status') }}</span>
                        {{ __('cancellations.refund_statuses.' . $cancellation->refund_status) }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('cancellations.fields.reason') }}</span>
                        {{ __('cancellations.reasons.' . $cancellation->reason_key) }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="space-y-3">
        @forelse ($cancellations as $item)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 text-sm dark:border-zinc-800 dark:bg-zinc-950">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-medium text-zinc-950 dark:text-white">{{ $item->cancellation_number }}</p>
                        <p class="text-zinc-600 dark:text-zinc-300">{{ __('cancellations.reasons.' . $item->reason_key) }}</p>
                    </div>
                    <flux:badge icon="calendar-days">{{ __('cancellations.statuses.' . $item->status) }}</flux:badge>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-zinc-300 p-4 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                {{ __('cancellations.empty.no_cancellations') }}
            </div>
        @endforelse
    </div>
</x-ui.section>
