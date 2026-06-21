<div class="space-y-4">
    <flux:card class="space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:heading size="sm">{{ __('pricing.title') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $summary['quote_number'] }}</flux:text>
            </div>
            <flux:badge size="sm">{{ $summary['pricing_status'] }}</flux:badge>
        </div>

        <div class="rounded-lg bg-zinc-50 px-3 py-3 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('pricing.fields.total_payable') }}</flux:text>
            <flux:heading size="lg">{{ $summary['total_payable'] }}</flux:heading>
        </div>

        <div class="grid gap-2 sm:grid-cols-3">
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <flux:text size="sm">{{ __('pricing.fields.nights_count') }}</flux:text>
                <flux:heading size="sm">{{ $summary['nights_count'] }}</flux:heading>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <flux:text size="sm">{{ __('pricing.fields.total_without_deposit') }}</flux:text>
                <flux:heading size="sm">{{ $summary['total_without_deposit'] }}</flux:heading>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <flux:text size="sm">{{ __('pricing.fields.refundable_amount') }}</flux:text>
                <flux:heading size="sm">{{ $summary['refundable_amount'] }}</flux:heading>
            </div>
        </div>

        @if ($summary['requires_host_time_approval'])
            <flux:callout color="amber">
                <flux:callout.heading>{{ __('pricing.messages.host_time_approval_required') }}</flux:callout.heading>
            </flux:callout>
        @endif
    </flux:card>

    <livewire:bookings.pricing.price-breakdown :quote-id="$quoteId" />
</div>
