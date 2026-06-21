<flux:card class="space-y-3">
    <flux:heading size="sm">{{ __('pricing.sections.host_payout') }}</flux:heading>

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
        <flux:badge size="sm">{{ __('pricing.fields.host_payout_date') }} · {{ $preview['payout_date'] }}</flux:badge>
    @endif
</flux:card>
