<section class="space-y-4">
    @if(session('success'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('success') }}</flux:callout.text>
        </flux:callout>
    @endif

    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:badge color="emerald" icon="check-circle">{{ __('booking.extension.eyebrow') }}</flux:badge>
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.extension.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ __('booking.extension.helper', ['place' => $placeTitle]) }}
            </flux:text>
        </div>

        <div class="grid grid-cols-2 gap-2 text-sm">
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.extension.current_checkout') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $booking->check_out_date?->translatedFormat('d M Y') }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.nights') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $booking->nights_count }}</div>
            </div>
        </div>

        @if($statusValue)
            <flux:badge icon="tag">{{ __('statuses.extension.'.$statusValue) }}</flux:badge>
        @endif

        @error('booking')
            <flux:callout color="amber" icon="exclamation-triangle">
                <flux:callout.text>{{ $message }}</flux:callout.text>
            </flux:callout>
        @enderror
        @error('extension')
            <flux:callout color="amber" icon="exclamation-triangle">
                <flux:callout.text>{{ $message }}</flux:callout.text>
            </flux:callout>
        @enderror

        @if(! $extension || $extension->status === \App\Enums\BookingExtensionStatus::AwaitingHostApproval || $extension->status === \App\Enums\BookingExtensionStatus::AwaitingPayment)
            <div class="space-y-4">
                <flux:field>
                    <flux:label>{{ __('booking.extension.fields.requested_new_checkout') }}</flux:label>
                    <flux:input type="date" wire:model.change="requestedNewCheckout" icon="calendar-days" />
                    <flux:error name="requestedNewCheckout" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('booking.extension.fields.guest_message') }}</flux:label>
                    <flux:textarea rows="3" wire:model.blur="guestMessage" />
                    <flux:description>{{ __('booking.extension.guest_message_helper') }}</flux:description>
                    <flux:error name="guestMessage" />
                </flux:field>
            </div>
        @endif

        @if($preview)
            <div class="space-y-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                <div class="flex items-center justify-between gap-3">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking.extension.preview') }}</span>
                        </span>
                    </flux:heading>
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                        {{ __('booking.extension.additional_nights_count', ['count' => $preview['additional_nights']]) }}
                    </flux:text>
                </div>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <span class="text-zinc-500 dark:text-zinc-400">{{ __('booking.extension.additional_amount') }}</span>
                        <span>{{ number_format((float) $preview['additional_amount'], 2) }} {{ $preview['currency'] }}</span>
                    </div>
                    @if((float) $preview['discount_amount'] > 0)
                        <div class="flex justify-between gap-3">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('booking.discount') }}</span>
                            <span>-{{ number_format((float) $preview['discount_amount'], 2) }} {{ $preview['currency'] }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between gap-3">
                        <span class="text-zinc-500 dark:text-zinc-400">{{ __('booking.service_fee') }}</span>
                        <span>{{ number_format((float) $preview['service_fee_amount'], 2) }} {{ $preview['currency'] }}</span>
                    </div>
                    <div class="flex justify-between gap-3 font-semibold text-zinc-950 dark:text-zinc-50">
                        <span>{{ __('booking.extension.payment_required') }}</span>
                        <span>{{ number_format((float) $preview['total_extra'], 2) }} {{ $preview['currency'] }}</span>
                    </div>
                    <div class="flex justify-between gap-3 text-sm">
                        <span class="text-zinc-500 dark:text-zinc-400">{{ __('booking.extension.new_total') }}</span>
                        <span>{{ number_format((float) $preview['new_total'], 2) }} {{ $preview['currency'] }}</span>
                    </div>
                </div>

                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ $preview['requires_host_approval']
                        ? __('booking.extension.host_approval_note')
                        : __('booking.extension.instant_note') }}
                </flux:text>
            </div>
        @endif

        @if($extension?->status === \App\Enums\BookingExtensionStatus::AwaitingHostApproval)
            <flux:callout color="amber" icon="exclamation-triangle">
                <flux:callout.text>{{ __('booking.extension.waiting_host') }}</flux:callout.text>
            </flux:callout>
        @elseif($extension?->status === \App\Enums\BookingExtensionStatus::AwaitingPayment)
            <flux:callout color="blue" icon="information-circle">
                <flux:callout.text>{{ __('booking.extension.waiting_payment') }}</flux:callout.text>
            </flux:callout>
        @elseif($extension?->status === \App\Enums\BookingExtensionStatus::Approved)
            <flux:callout color="green" icon="check-circle">
                <flux:callout.text>{{ __('booking.extension.approved_note') }}</flux:callout.text>
            </flux:callout>
        @endif

        <div class="grid gap-2 sm:grid-cols-2">
            @if(! $extension)
                <flux:button wire:click="submit" variant="primary" class="w-full data-loading:opacity-70" :disabled="$preview === null" icon="eye">
                    <span wire:loading.remove wire:target="submit">{{ __('booking.extension.actions.request') }}</span>
                    <span wire:loading wire:target="submit">{{ __('booking.extension.actions.requesting') }}</span>
                </flux:button>
            @elseif($extension->status === \App\Enums\BookingExtensionStatus::AwaitingPayment && $canUseDemoPayment)
                <flux:button wire:click="payExtension" variant="primary" class="w-full data-loading:opacity-70" icon="credit-card">
                    <span wire:loading.remove wire:target="payExtension">{{ __('booking.extension.actions.mark_paid') }}</span>
                    <span wire:loading wire:target="payExtension">{{ __('booking.extension.actions.marking_paid') }}</span>
                </flux:button>
            @endif

            @if($extension && in_array($statusValue, ['awaiting_host_approval', 'awaiting_payment'], true))
                <flux:button wire:click="cancelExtension" variant="ghost" class="w-full" icon="x-mark">
                    {{ __('booking.extension.actions.cancel') }}
                </flux:button>
            @endif
        </div>
    </flux:card>
</section>
