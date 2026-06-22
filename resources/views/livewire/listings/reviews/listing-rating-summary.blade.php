<flux:card class="space-y-3">
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <flux:heading size="lg">{{ __('ratings.title') }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                @if($snapshot)
                    {{ __('ratings.messages.rating_summary', ['rating' => $snapshot->overall_rating, 'count' => $snapshot->reviews_count]) }}
                @else
                    {{ __('ratings.messages.not_enough_reviews') }}
                @endif
            </flux:text>
        </div>

        @if($snapshot)
            <flux:badge color="amber">{{ $snapshot->overall_rating }}</flux:badge>
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
