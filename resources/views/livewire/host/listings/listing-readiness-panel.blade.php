<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('listing_readiness.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_readiness.helper') }}</flux:text>
    </div>

    <div class="space-y-2">
        @forelse($checks as $check)
            <div class="flex items-start justify-between gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <flux:text size="sm">{{ __($check->message_key) }}</flux:text>
                <flux:badge size="sm" color="{{ $check->status === 'completed' ? 'emerald' : 'amber' }}" icon="exclamation-triangle">
                    {{ __('listing_readiness.statuses.'.$check->status) }}
                </flux:badge>
            </div>
        @empty
            <flux:text size="sm">{{ __('listing_readiness.empty') }}</flux:text>
        @endforelse
    </div>
</flux:card>
