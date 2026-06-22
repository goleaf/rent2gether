<x-ui.page>
    <section class="space-y-2">
        <flux:badge color="emerald">{{ __('booking.payment_page.eyebrow') }}</flux:badge>
        <flux:heading size="xl" level="1">{{ __('booking.payment_page.title') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">
            {{ __('booking.payment_page.helper') }}
        </flux:text>
    </section>

    @if (session('payment-status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
            {{ session('payment-status') }}
        </div>
    @endif

    <flux:card class="space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 space-y-1">
                <flux:heading size="lg">{{ __('booking.payment_page.summary.title') }}</flux:heading>
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('booking.payment_page.summary.reference', ['reference' => $booking->reference]) }}
                </flux:text>
                <div class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $placeTitle }}</div>
            </div>

            <flux:badge color="amber">{{ $booking->payment_status->label() }}</flux:badge>
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

            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.nights') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">
                    {{ trans_choice('booking.nights_count', (int) $booking->nights_count, ['count' => (int) $booking->nights_count]) }}
                </div>
            </div>

            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.total') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->money($booking->total_amount, $booking->currency) }}</div>
            </div>
        </div>

        @if ($booking->payment_deadline_at && $canPay)
            <flux:callout icon="clock" color="amber">
                <flux:callout.heading>{{ __('booking.payment_page.deadline.title') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('booking.payment_page.deadline.text', ['deadline' => $booking->payment_deadline_at->translatedFormat('d M Y, H:i')]) }}
                </flux:callout.text>
            </flux:callout>
        @endif
    </flux:card>

    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="lg">{{ __('booking.payment_page.price.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ __('booking.payment_page.price.helper') }}
            </flux:text>
        </div>

        <div class="space-y-2 text-sm">
            @foreach ($lineItems as $line)
                <div class="flex items-start justify-between gap-3 {{ $loop->last ? 'border-t border-zinc-200 pt-2 text-base font-semibold dark:border-zinc-700' : '' }}">
                    <div class="min-w-0">
                        <div>{{ $line['label'] }}</div>
                        @if ($line['refundable'])
                            <div class="text-xs text-emerald-700 dark:text-emerald-300">{{ __('booking.payment_page.price.refundable') }}</div>
                        @endif
                    </div>
                    <div class="shrink-0">{{ $this->money($line['amount'], $line['currency']) }}</div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-2 text-sm">
            <div class="rounded-lg border border-zinc-200 px-3 py-3 dark:border-zinc-800">
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ __('booking.payment_page.deposit.title') }}</div>
                <div class="mt-1 text-zinc-600 dark:text-zinc-400">
                    {{ __('booking.payment_page.deposit.text', ['amount' => $this->money($booking->deposit_amount, $booking->currency)]) }}
                </div>
            </div>

            <div class="rounded-lg border border-zinc-200 px-3 py-3 dark:border-zinc-800">
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ __('booking.payment_page.refund.title') }}</div>
                <div class="mt-1 text-zinc-600 dark:text-zinc-400">
                    {{ __('booking.payment_page.refund.text', [
                        'refundable' => $this->money($booking->refundable_amount, $booking->currency),
                        'non_refundable' => $this->money($booking->non_refundable_amount, $booking->currency),
                    ]) }}
                </div>
            </div>
        </div>
    </flux:card>

    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="lg">{{ __('booking.payment_page.method.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ __('booking.payment_page.method.helper') }}
            </flux:text>
        </div>

        @if ($canPay)
            <div class="rounded-lg border border-dashed border-zinc-300 px-3 py-3 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                {{ __('booking.payment_page.method.placeholder') }}
            </div>

            @if ($canUseDemoDriver)
                <flux:callout icon="beaker" color="sky">
                    <flux:callout.heading>{{ __('booking.payment_page.demo.title') }}</flux:callout.heading>
                    <flux:callout.text>{{ __('booking.payment_page.demo.helper') }}</flux:callout.text>
                </flux:callout>
            @else
                <flux:callout icon="lock-closed" color="zinc">
                    <flux:callout.heading>{{ __('booking.payment_page.production.title') }}</flux:callout.heading>
                    <flux:callout.text>{{ __('booking.payment_page.production.helper') }}</flux:callout.text>
                </flux:callout>
            @endif
        @else
            <flux:callout icon="check-circle" color="emerald">
                <flux:callout.heading>{{ __('booking.payment_page.complete.title') }}</flux:callout.heading>
                <flux:callout.text>{{ __('booking.payment_page.complete.helper') }}</flux:callout.text>
            </flux:callout>
        @endif
    </flux:card>

    @if ($accessDetails)
        <flux:card class="space-y-3">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('booking.payment_page.access.title') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ __('booking.payment_page.access.helper') }}
                </flux:text>
            </div>

            @if ($accessDetails['address'])
                <div class="rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.payment_page.access.address') }}</div>
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $accessDetails['address'] }}</div>
                </div>
            @endif

            @if ($accessDetails['instructions'])
                <div class="rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.payment_page.access.instructions') }}</div>
                    <div class="whitespace-pre-line text-zinc-900 dark:text-zinc-100">{{ $accessDetails['instructions'] }}</div>
                </div>
            @endif
        </flux:card>
    @endif

    <div class="fixed inset-x-0 bottom-0 z-30 border-t border-zinc-200 bg-white/95 px-4 py-3 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95 sm:static sm:rounded-lg sm:border sm:backdrop-blur-none">
        <div class="mx-auto flex w-full max-w-5xl items-center gap-3 sm:block">
            <div class="min-w-0 flex-1 sm:hidden">
                <div class="text-xs text-zinc-500">{{ __('booking.total') }}</div>
                <div class="truncate text-sm font-semibold">{{ $this->money($booking->total_amount, $booking->currency) }}</div>
            </div>

            @if ($canPay && $canUseDemoDriver)
                <flux:button
                    wire:click="markAsPaid"
                    wire:loading.attr="disabled"
                    wire:target="markAsPaid"
                    variant="primary"
                    class="w-full data-loading:opacity-70"
                >
                    <span wire:loading.remove wire:target="markAsPaid">{{ __('booking.payment_page.actions.mark_paid') }}</span>
                    <span wire:loading wire:target="markAsPaid">{{ __('booking.payment_page.actions.marking_paid') }}</span>
                </flux:button>
            @else
                <flux:button href="{{ route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="ghost" class="w-full">
                    {{ __('booking.payment_page.actions.back_to_booking') }}
                </flux:button>
            @endif
        </div>
    </div>
</x-ui.page>
