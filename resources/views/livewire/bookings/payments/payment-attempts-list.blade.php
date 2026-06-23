<flux:card class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('payments.cards.attempts') }}</span>
        </span>
    </flux:heading>

    <div class="grid gap-2">
        @forelse ($attempts as $attempt)
            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-3">
                    <flux:text size="sm">{{ __('payments.fields.attempt_number') }} {{ $attempt['number'] }}</flux:text>
                    <flux:text size="sm">{{ $attempt['status'] }}</flux:text>
                </div>
                <flux:text size="xs" class="text-zinc-500">{{ $attempt['amount'] }}</flux:text>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('payments.empty_states.no_attempts') }}</flux:text>
        @endforelse
    </div>
</flux:card>
