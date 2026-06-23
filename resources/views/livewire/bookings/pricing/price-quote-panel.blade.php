<div class="space-y-4">
    <flux:card class="space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('pricing.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $summary['quote_number'] }}</flux:text>
            </div>
            <flux:badge size="sm" icon="calendar-days">{{ $summary['pricing_status'] }}</flux:badge>
        </div>

        <div class="rounded-lg bg-zinc-50 px-3 py-3 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('pricing.fields.total_payable') }}</flux:text>
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="banknotes" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $summary['total_payable'] }}</span>
                </span>
            </flux:heading>
        </div>

        <div class="grid gap-2 sm:grid-cols-3">
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <flux:text size="sm">{{ __('pricing.fields.nights_count') }}</flux:text>
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $summary['nights_count'] }}</span>
                    </span>
                </flux:heading>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <flux:text size="sm">{{ __('pricing.fields.total_without_deposit') }}</flux:text>
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $summary['total_without_deposit'] }}</span>
                    </span>
                </flux:heading>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <flux:text size="sm">{{ __('pricing.fields.refundable_amount') }}</flux:text>
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $summary['refundable_amount'] }}</span>
                    </span>
                </flux:heading>
            </div>
        </div>

        @if ($summary['requires_host_time_approval'])
            <flux:callout color="amber" icon="exclamation-triangle">
                <flux:callout.heading>{{ __('pricing.messages.host_time_approval_required') }}</flux:callout.heading>
            </flux:callout>
        @endif
    </flux:card>

    <livewire:bookings.pricing.price-breakdown :quote-id="$quoteId" />
</div>
