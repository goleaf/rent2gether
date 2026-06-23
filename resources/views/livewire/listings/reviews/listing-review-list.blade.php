<section class="space-y-3">
    @forelse($reviews as $review)
        <flux:card class="space-y-2">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="chat-bubble-left-right" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('ratings.messages.rating_short', ['rating' => $review->overall_rating]) }}</span>
                </span>
            </flux:heading>

            @if($review->public_comment)
                <flux:text>{{ $review->public_comment }}</flux:text>
            @endif
        </flux:card>
    @empty
        <flux:card>
            <flux:text>{{ __('reviews.messages.no_reviews_yet') }}</flux:text>
        </flux:card>
    @endforelse
</section>
