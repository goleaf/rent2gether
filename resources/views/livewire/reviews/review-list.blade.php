<section class="space-y-3">
    @forelse($reviews as $review)
        <flux:card class="space-y-2">
            <div class="flex items-center justify-between gap-3">
                <flux:heading size="lg">{{ __('ratings.messages.rating_short', ['rating' => $review->overall_rating]) }}</flux:heading>
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $review->published_at?->diffForHumans() }}</flux:text>
            </div>

            @if($review->public_comment)
                <flux:text class="text-zinc-700 dark:text-zinc-300">{{ $review->public_comment }}</flux:text>
            @endif
        </flux:card>
    @empty
        <flux:card>
            <flux:text>{{ __('reviews.messages.no_reviews_yet') }}</flux:text>
        </flux:card>
    @endforelse
</section>
