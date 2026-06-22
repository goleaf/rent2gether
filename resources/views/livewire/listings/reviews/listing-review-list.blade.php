<section class="space-y-3">
    @forelse($reviews as $review)
        <flux:card class="space-y-2">
            <flux:heading size="lg">{{ __('ratings.messages.rating_short', ['rating' => $review->overall_rating]) }}</flux:heading>

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
