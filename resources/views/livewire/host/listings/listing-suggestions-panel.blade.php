<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('listing_readiness.suggestions.title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_readiness.suggestions.helper') }}</flux:text>
    </div>

    <div class="space-y-2">
        @forelse($suggestions as $suggestion)
            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <flux:text size="sm">{{ __($suggestion->message_key) }}</flux:text>
            </div>
        @empty
            <flux:text size="sm">{{ __('listing_readiness.suggestions.empty') }}</flux:text>
        @endforelse
    </div>
</flux:card>
