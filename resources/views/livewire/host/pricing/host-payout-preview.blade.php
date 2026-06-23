<flux:card class="space-y-3">
    <flux:heading size="sm">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('pricing.sections.host_payout') }}</span>
        </span>
    </flux:heading>

    <div class="space-y-2">
        <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('pricing.fields.amount_after_discount') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ $preview['accommodation_after_discount'] }}</flux:text>
        </div>
        <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('pricing.fields.cleaning_fee') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ $preview['cleaning_fee'] }}</flux:text>
        </div>
        <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('pricing.fields.host_payout') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ $preview['host_payout'] }}</flux:text>
        </div>
    </div>

    @if ($preview['payout_date'])
        <flux:badge size="sm" icon="calendar-days">{{ __('pricing.fields.host_payout_date') }} · {{ $preview['payout_date'] }}</flux:badge>
    @endif
</flux:card>
