<flux:card class="space-y-2">
    <flux:heading size="lg">{{ __('ratings.target_types.guest') }}</flux:heading>

    <flux:text class="text-zinc-600 dark:text-zinc-400">
        @if($snapshot)
            {{ __('ratings.messages.rating_summary', ['rating' => $snapshot->overall_rating, 'count' => $snapshot->reviews_count]) }}
        @else
            {{ __('ratings.messages.not_enough_reviews') }}
        @endif
    </flux:text>
</flux:card>
