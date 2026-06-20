<div class="space-y-3">
    @forelse($reviews as $review)
        <flux:card class="space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="sm">{{ $review->reviewer?->name ?: __('listing.detail.reviews.guest') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-500">{{ $review->created_at?->translatedFormat('d M Y') }}</flux:text>
                </div>
                <flux:badge>{{ __('listing.detail.reviews.rating', ['rating' => number_format((float) $review->overall_rating, 1)]) }}</flux:badge>
            </div>

            @if($review->liked_text ?: $review->positive_comment)
                <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $review->liked_text ?: $review->positive_comment }}</p>
            @endif

            @if($review->improvement_text ?: $review->negative_comment)
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $review->improvement_text ?: $review->negative_comment }}</p>
            @endif
        </flux:card>
    @empty
        <flux:card class="space-y-2 text-center">
            <flux:icon name="chat-bubble-left-right" class="mx-auto size-8 text-zinc-300 dark:text-zinc-700" />
            <flux:heading size="sm">{{ __('listing.detail.reviews.empty_title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-500">{{ __('listing.detail.reviews.empty_helper') }}</flux:text>
        </flux:card>
    @endforelse

    @if($reviews->hasPages())
        <div>{{ $reviews->links() }}</div>
    @endif
</div>
