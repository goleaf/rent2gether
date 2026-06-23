<flux:card class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('payments.cards.breakdown') }}</span>
        </span>
    </flux:heading>

    <div class="grid gap-2">
        @forelse ($allocations as $allocation)
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <flux:text size="sm">{{ $allocation['label'] }}</flux:text>
                    @if ($allocation['refundable'])
                        <flux:text size="xs" class="text-zinc-500">{{ __('payments.messages.deposit_refundable') }}</flux:text>
                    @endif
                </div>
                <flux:text size="sm">{{ $allocation['amount'] }}</flux:text>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('payments.empty_states.no_allocations') }}</flux:text>
        @endforelse
    </div>
</flux:card>
