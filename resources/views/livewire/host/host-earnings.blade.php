<div class="max-w-4xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('host.earnings.title') }}</flux:heading>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <flux:card class="text-center">
            <div class="text-2xl font-bold">&euro;{{ number_format($this->todayIncome, 2) }}</div>
            <flux:text size="sm" class="text-zinc-500">{{ __('host.earnings.today') }}</flux:text>
        </flux:card>
        <flux:card class="text-center">
            <div class="text-2xl font-bold">&euro;{{ number_format($this->weekIncome, 2) }}</div>
            <flux:text size="sm" class="text-zinc-500">{{ __('host.earnings.this_week') }}</flux:text>
        </flux:card>
        <flux:card class="text-center">
            <div class="text-2xl font-bold">&euro;{{ number_format($this->monthIncome, 2) }}</div>
            <flux:text size="sm" class="text-zinc-500">{{ __('host.this_month') }}</flux:text>
        </flux:card>
        <flux:card class="text-center">
            <div class="text-2xl font-bold">&euro;{{ number_format($this->yearIncome, 2) }}</div>
            <flux:text size="sm" class="text-zinc-500">{{ __('host.earnings.this_year') }}</flux:text>
        </flux:card>
    </div>

    <div class="space-y-3">
        <flux:heading size="lg">{{ __('host.earnings.recent_payouts') }}</flux:heading>
        @forelse($this->recentPayouts as $payout)
            <flux:card class="flex items-center justify-between">
                <div>
                    <flux:text class="font-medium">{{ $payout->reference }}</flux:text>
                    <flux:text size="sm" class="text-zinc-500">{{ $payout->created_at->translatedFormat('d M Y') }}</flux:text>
                </div>
                <div class="flex items-center gap-3">
                    <flux:badge>{{ $payout->status->label() }}</flux:badge>
                    <flux:text class="font-semibold">&euro;{{ number_format($payout->net_amount, 2) }}</flux:text>
                </div>
            </flux:card>
        @empty
            <flux:card>
                <flux:text class="text-center text-zinc-500 py-4">{{ __('host.earnings.empty') }}</flux:text>
            </flux:card>
        @endforelse
    </div>
</div>
