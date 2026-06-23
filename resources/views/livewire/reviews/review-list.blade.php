<section class="space-y-3">
    @forelse($reviews as $review)
        <flux:card class="space-y-2">
            <div class="flex items-center justify-between gap-3">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('ratings.messages.rating_short', ['rating' => $review->overall_rating]) }}</span>
                    </span>
                </flux:heading>
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
