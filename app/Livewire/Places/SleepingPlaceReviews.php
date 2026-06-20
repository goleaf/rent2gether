<?php

namespace App\Livewire\Places;

use App\Models\Review;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class SleepingPlaceReviews extends Component
{
    use WithPagination;

    #[Locked]
    public int $sleepingPlaceId;

    public function mount(int $sleepingPlaceId): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
    }

    public function placeholder(): View
    {
        return view('livewire.places.partials.lazy-placeholder', [
            'label' => __('listing.detail.reviews.loading'),
        ]);
    }

    public function render(): View
    {
        $reviews = Review::query()
            ->select([
                'id',
                'reviewer_id',
                'sleeping_place_id',
                'overall_rating',
                'cleanliness_rating',
                'safety_rating',
                'liked_text',
                'improvement_text',
                'positive_comment',
                'negative_comment',
                'would_recommend',
                'recommend',
                'status',
                'visible_at',
                'created_at',
            ])
            ->with(['reviewer:id,name,avatar'])
            ->where('sleeping_place_id', $this->sleepingPlaceId)
            ->visible()
            ->guestToPlace()
            ->latest('created_at')
            ->simplePaginate(5, pageName: 'placeReviewsPage');

        return view('livewire.places.sleeping-place-reviews', [
            'reviews' => $reviews,
        ]);
    }
}
