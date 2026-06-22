<?php

namespace App\Livewire\Listings\Reviews;

use App\Models\Review;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ListingReviewList extends Component
{
    public ?int $sleepingPlaceId = null;

    #[Computed]
    public function reviews(): CursorPaginator
    {
        return Review::query()
            ->select(['id', 'review_number', 'sleeping_place_id', 'overall_rating', 'public_comment', 'published_at'])
            ->where('status', 'published')
            ->where('is_public', true)
            ->when($this->sleepingPlaceId, fn ($query): mixed => $query->where('sleeping_place_id', $this->sleepingPlaceId))
            ->latest('published_at')
            ->cursorPaginate(20);
    }

    public function render(): View
    {
        return view('livewire.listings.reviews.listing-review-list', [
            'reviews' => $this->reviews,
        ]);
    }
}
