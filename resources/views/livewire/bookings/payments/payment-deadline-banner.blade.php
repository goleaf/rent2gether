<flux:card class="space-y-3 border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/40">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-1">
            <flux:heading size="sm">{{ __('payments.fields.payment_deadline') }}</flux:heading>
            <flux:text size="sm">{{ $summary['deadline'] }}</flux:text>
        </div>
        <flux:badge color="amber">{{ $summary['status'] }}</flux:badge>
    </div>

    <div class="grid gap-2">
        @forelse ($deadlines as $deadline)
            <div class="flex items-center justify-between gap-3">
                <flux:text size="xs">{{ $deadline['type'] }}</flux:text>
                <flux:text size="xs">{{ $deadline['status'] }}</flux:text>
            </div>
        @empty
            <flux:text size="sm">{{ __('payments.empty_states.no_deadline') }}</flux:text>
        @endforelse
    </div>
</flux:card>
