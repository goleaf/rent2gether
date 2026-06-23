<flux:card class="space-y-3">
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="star" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('ratings.title') }}</span>
                </span>
            </flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                @if($snapshot)
                    {{ __('ratings.messages.rating_summary', ['rating' => $snapshot->overall_rating, 'count' => $snapshot->reviews_count]) }}
                @else
                    {{ __('ratings.messages.not_enough_reviews') }}
                @endif
            </flux:text>
        </div>

        @if($snapshot)
            <flux:badge color="amber" icon="exclamation-triangle">{{ $snapshot->overall_rating }}</flux:badge>
        @endif
    </div>

    @if($snapshot)
        <div class="grid grid-cols-2 gap-2 text-sm">
            <span>{{ __('ratings.metrics.cleanliness') }}: {{ $snapshot->cleanliness_rating }}</span>
            <span>{{ __('ratings.metrics.safety') }}: {{ $snapshot->safety_rating }}</span>
            <span>{{ __('ratings.metrics.internet') }}: {{ $snapshot->internet_rating }}</span>
        </div>
    @endif
</flux:card>
