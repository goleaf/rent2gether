<flux:card class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-1">
            <flux:heading size="md">{{ __('payments.host.summary_title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $summary['payment_number'] }}</flux:text>
        </div>
        <flux:badge color="{{ $summary['status_color'] }}">{{ $summary['status'] }}</flux:badge>
    </div>

    <div class="grid gap-2">
        @forelse ($allocations as $allocation)
            <div class="flex items-center justify-between gap-3">
                <flux:text size="sm">{{ $allocation['label'] }}</flux:text>
                <flux:text size="sm">{{ $allocation['amount'] }}</flux:text>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('payments.empty_states.no_allocations') }}</flux:text>
        @endforelse
    </div>
</flux:card>
