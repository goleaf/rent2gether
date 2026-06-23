<flux:card class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-1">
            <flux:heading size="md">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('payments.host.summary_title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $summary['payment_number'] }}</flux:text>
        </div>
        <flux:badge color="{{ $summary['status_color'] }}" icon="user">{{ $summary['status'] }}</flux:badge>
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
