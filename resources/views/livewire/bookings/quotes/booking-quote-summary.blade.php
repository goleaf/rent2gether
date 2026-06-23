<flux:card class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking_quotes.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $summary['quote_number'] }}</flux:text>
        </div>
        <flux:badge color="lime" icon="calendar-days">{{ $summary['status'] }}</flux:badge>
    </div>

    <div class="grid gap-2 sm:grid-cols-3">
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_quotes.price.total_payable') }}</flux:text>
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $summary['total_payable'] }}</span>
                </span>
            </flux:heading>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_quotes.price.refundable_amount') }}</flux:text>
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $summary['refundable_amount'] }}</span>
                </span>
            </flux:heading>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_quotes.price.non_refundable_amount') }}</flux:text>
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $summary['non_refundable_amount'] }}</span>
                </span>
            </flux:heading>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        <flux:badge size="sm" icon="calendar-days">{{ $summary['availability_status'] }}</flux:badge>
        <flux:badge size="sm" icon="calendar-days">{{ $summary['validation_status'] }}</flux:badge>
        <flux:badge size="sm" icon="calendar-days">{{ $summary['pricing_status'] }}</flux:badge>
    </div>
</flux:card>
