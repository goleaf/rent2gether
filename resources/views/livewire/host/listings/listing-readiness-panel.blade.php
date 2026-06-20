<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('listing_readiness.title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_readiness.helper') }}</flux:text>
    </div>

    <div class="space-y-2">
        @forelse($checks as $check)
            <div class="flex items-start justify-between gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <flux:text size="sm">{{ __($check->message_key) }}</flux:text>
                <flux:badge size="sm" color="{{ $check->status === 'completed' ? 'emerald' : 'amber' }}">
                    {{ __('listing_readiness.statuses.'.$check->status) }}
                </flux:badge>
            </div>
        @empty
            <flux:text size="sm">{{ __('listing_readiness.empty') }}</flux:text>
        @endforelse
    </div>
</flux:card>
