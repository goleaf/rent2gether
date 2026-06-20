<div class="mx-auto max-w-5xl space-y-5 pb-28 sm:pb-8">
    <section class="space-y-3">
        <flux:badge color="emerald">{{ __('host.income.eyebrow') }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">{{ __('host.income.title') }}</flux:heading>
            <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">
                {{ __('host.income.helper') }}
            </flux:text>
        </div>
    </section>

    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="sm">{{ __('host.income.filters.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host.income.filters.helper') }}</flux:text>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <flux:field>
                <flux:label>{{ __('host.income.filters.period') }}</flux:label>
                <flux:select wire:model.change="datePreset">
                    <flux:select.option value="this_month">{{ __('host.income.filters.this_month') }}</flux:select.option>
                    <flux:select.option value="last_month">{{ __('host.income.filters.last_month') }}</flux:select.option>
                    <flux:select.option value="custom">{{ __('host.income.filters.custom') }}</flux:select.option>
                </flux:select>
                <flux:error name="datePreset" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('host.income.filters.start') }}</flux:label>
                <flux:input type="date" wire:model.change="customStart" />
                <flux:error name="customStart" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('host.income.filters.end') }}</flux:label>
                <flux:input type="date" wire:model.change="customEnd" />
                <flux:error name="customEnd" />
            </flux:field>
        </div>

        <flux:button type="button" variant="primary" class="w-full data-loading:opacity-70 sm:w-auto" wire:click="applyFilters">
            <span wire:loading.remove wire:target="applyFilters">{{ __('host.income.filters.apply') }}</span>
            <span wire:loading wire:target="applyFilters">{{ __('host.income.filters.applying') }}</span>
        </flux:button>
    </flux:card>

    <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <flux:card class="space-y-1">
            <flux:text size="sm" class="text-zinc-500">{{ __('host.income.summary.today') }}</flux:text>
            <div class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->money($summary['today_income'], $summary['currency']) }}</div>
        </flux:card>
        <flux:card class="space-y-1">
            <flux:text size="sm" class="text-zinc-500">{{ __('host.income.summary.week') }}</flux:text>
            <div class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->money($summary['week_income'], $summary['currency']) }}</div>
        </flux:card>
        <flux:card class="space-y-1">
            <flux:text size="sm" class="text-zinc-500">{{ __('host.income.summary.month') }}</flux:text>
            <div class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->money($summary['month_income'], $summary['currency']) }}</div>
        </flux:card>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <flux:card class="space-y-1">
            <flux:text size="sm" class="text-zinc-500">{{ __('host.income.summary.confirmed') }}</flux:text>
            <div class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->money($summary['confirmed_income'], $summary['currency']) }}</div>
            <flux:text size="xs" class="text-zinc-500">{{ __('host.income.summary.bookings_count', ['count' => $summary['confirmed_count']]) }}</flux:text>
        </flux:card>
        <flux:card class="space-y-1">
            <flux:text size="sm" class="text-zinc-500">{{ __('host.income.summary.pending') }}</flux:text>
            <div class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->money($summary['pending_payments_amount'], $summary['currency']) }}</div>
            <flux:text size="xs" class="text-zinc-500">{{ __('host.income.summary.bookings_count', ['count' => $summary['pending_payments_count']]) }}</flux:text>
        </flux:card>
        <flux:card class="space-y-1">
            <flux:text size="sm" class="text-zinc-500">{{ __('host.income.summary.refunds') }}</flux:text>
            <div class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->money($summary['refunds_amount'], $summary['currency']) }}</div>
            <flux:text size="xs" class="text-zinc-500">{{ __('host.income.summary.refunds_count', ['count' => $summary['refunds_count']]) }}</flux:text>
        </flux:card>
        <flux:card class="space-y-1">
            <flux:text size="sm" class="text-zinc-500">{{ __('host.income.summary.deposits_held') }}</flux:text>
            <div class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->money($summary['deposits_held_amount'], $summary['currency']) }}</div>
        </flux:card>
        <flux:card class="space-y-1">
            <flux:text size="sm" class="text-zinc-500">{{ __('host.income.summary.deposits_returned') }}</flux:text>
            <div class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->money($summary['deposits_returned_amount'], $summary['currency']) }}</div>
        </flux:card>
    </section>

    <flux:card class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('host.income.payouts.title') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host.income.payouts.helper') }}</flux:text>
            </div>
            <flux:badge color="zinc">{{ __($summary['payout_placeholder']['label_key']) }}</flux:badge>
        </div>
        <div class="rounded-lg border border-dashed border-zinc-200 px-3 py-3 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
            {{ __('host.income.payouts.text', ['amount' => $this->money($summary['payout_placeholder']['amount'], $summary['payout_placeholder']['currency'])]) }}
        </div>
    </flux:card>

    <div class="grid gap-4 lg:grid-cols-3">
        @foreach([
            ['title' => __('host.income.breakdown.properties'), 'items' => $summary['by_property']],
            ['title' => __('host.income.breakdown.rooms'), 'items' => $summary['by_room']],
            ['title' => __('host.income.breakdown.sleeping_places'), 'items' => $summary['by_sleeping_place']],
        ] as $section)
            <flux:card class="space-y-3">
                <flux:heading size="lg">{{ $section['title'] }}</flux:heading>
                @forelse($section['items'] as $item)
                    <div class="flex items-start justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                        <div class="min-w-0">
                            <div class="truncate font-medium text-zinc-900 dark:text-zinc-100">{{ $item['label'] }}</div>
                            <div class="text-xs text-zinc-500">{{ __('host.income.summary.bookings_count', ['count' => $item['count']]) }}</div>
                        </div>
                        <div class="shrink-0 font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->money($item['amount'], $item['currency']) }}</div>
                    </div>
                @empty
                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('host.income.empty_breakdown') }}</flux:text>
                @endforelse
            </flux:card>
        @endforeach
    </div>

    <flux:card class="space-y-3">
        <flux:heading size="lg">{{ __('host.income.receipts.title') }}</flux:heading>
        @forelse($summary['receipts'] as $receipt)
            <div class="rounded-lg border border-zinc-200 px-3 py-3 text-sm dark:border-zinc-700">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="truncate font-medium text-zinc-900 dark:text-zinc-100">{{ $receipt['reference'] }}</div>
                        <div class="text-xs text-zinc-500">{{ __('host.income.receipts.line', ['guest' => $receipt['guest'], 'place' => $receipt['place'], 'date' => $receipt['date']]) }}</div>
                    </div>
                    <flux:badge size="sm" color="blue">{{ $receipt['payment_status'] }}</flux:badge>
                </div>
                <div class="mt-3 grid grid-cols-3 gap-2 text-xs text-zinc-600 dark:text-zinc-400">
                    <div>
                        <div>{{ __('host.income.receipts.total') }}</div>
                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->money($receipt['total'], $receipt['currency']) }}</div>
                    </div>
                    <div>
                        <div>{{ __('host.income.receipts.refund') }}</div>
                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->money($receipt['refund'], $receipt['currency']) }}</div>
                    </div>
                    <div>
                        <div>{{ __('host.income.receipts.net') }}</div>
                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->money($receipt['net'], $receipt['currency']) }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-zinc-200 px-3 py-4 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                {{ __('host.income.receipts.empty') }}
            </div>
        @endforelse
    </flux:card>
</div>
