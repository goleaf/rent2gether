<x-ui.page>
    <section class="space-y-3">
        <flux:badge color="amber" icon="exclamation-triangle">{{ __('booking.cancellation.eyebrow') }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.cancellation.title') }}</span>
                </span>
            </flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ __('booking.cancellation.helper') }}
            </flux:text>
        </div>
    </section>

    <flux:card class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $placeTitle }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ __('booking.trips.reference') }} {{ $booking->reference }}
                </flux:text>
            </div>
            <flux:badge color="zinc" icon="calendar-days">{{ $booking->status->label() }}</flux:badge>
        </div>

        <div class="grid grid-cols-2 gap-2 text-sm">
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.check_in') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $booking->check_in_date?->translatedFormat('d M Y') }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.check_out') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $booking->check_out_date?->translatedFormat('d M Y') }}</div>
            </div>
        </div>
    </flux:card>

    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.cancellation.estimate.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ __($estimate['explanation_key']) }}
            </flux:text>
        </div>

        <div class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-3">
            <div class="rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-800">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.cancellation.estimate.paid_amount') }}</div>
                <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->money($estimate['paid_amount'], $estimate['currency']) }}</div>
            </div>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 dark:border-emerald-900 dark:bg-emerald-950">
                <div class="text-xs text-emerald-700 dark:text-emerald-300">{{ __('booking.cancellation.estimate.refund_amount') }}</div>
                <div class="font-semibold text-emerald-900 dark:text-emerald-100">{{ $this->money($estimate['refund_amount'], $estimate['currency']) }}</div>
            </div>
            <div class="rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-800">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.cancellation.estimate.non_refundable_amount') }}</div>
                <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->money($estimate['non_refundable_amount'], $estimate['currency']) }}</div>
            </div>
        </div>

        <div class="space-y-2 text-sm">
            @foreach($estimate['lines'] as $line)
                <div class="flex items-start justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                    <div>
                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ __($line['label_key']) }}</div>
                        @if($line['is_refundable'])
                            <div class="text-xs text-emerald-700 dark:text-emerald-300">{{ __('booking.cancellation.estimate.refundable') }}</div>
                        @else
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.cancellation.estimate.not_refundable') }}</div>
                        @endif
                    </div>
                    <div class="shrink-0 font-medium">{{ $this->money($line['amount'], $line['currency']) }}</div>
                </div>
            @endforeach
        </div>
    </flux:card>

    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.cancellation.reason.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ __('booking.cancellation.reason.helper') }}
            </flux:text>
        </div>

                <flux:field>
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.cancellation.reason.label') }}</span>
                </span>
            </flux:label>
            <flux:select wire:model.change="reason">
            <flux:select.option value="">{{ __('booking.cancellation.reason.placeholder') }}</flux:select.option>
            @foreach($reasons as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
            <flux:error name="reason" />
        </flux:field>

                <flux:field>
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.cancellation.reason.details') }}</span>
                </span>
            </flux:label>
            <flux:textarea
                wire:model.blur="details"
                rows="3"
                maxlength="500" />
            <flux:error name="details" />
        </flux:field>

                <flux:field variant="inline">
            <flux:checkbox wire:model.change="confirmed" />
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.cancellation.confirm.label') }}</span>
                </span>
            </flux:label>
            <flux:error name="confirmed" />
        </flux:field>
    </flux:card>

    <div class="fixed inset-x-0 bottom-0 z-30 border-t border-zinc-200 bg-white/95 px-4 py-3 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95 sm:static sm:rounded-lg sm:border sm:backdrop-blur-none">
        <div class="mx-auto grid w-full max-w-5xl grid-cols-2 gap-2">
            <flux:button href="{{ route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="ghost" class="w-full" icon="arrow-left">
                {{ __('booking.cancellation.actions.back') }}
            </flux:button>
            <flux:button wire:click="submitCancellation" wire:loading.attr="disabled" wire:target="submitCancellation" variant="danger" class="w-full" icon="x-mark">
                <span wire:loading.remove wire:target="submitCancellation">{{ __('booking.cancellation.actions.confirm') }}</span>
                <span wire:loading wire:target="submitCancellation">{{ __('booking.cancellation.actions.confirming') }}</span>
            </flux:button>
        </div>
    </div>
</x-ui.page>
