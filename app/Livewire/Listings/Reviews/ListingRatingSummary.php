<?php

namespace App\Livewire\Listings\Reviews;

use App\Models\SleepingPlaceRatingSnapshot;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ListingRatingSummary extends Component
{
    public ?int $sleepingPlaceId = null;

    #[Computed]
    public function snapshot(): ?SleepingPlaceRatingSnapshot
    {
        if ($this->sleepingPlaceId === null) {
            return null;
        }

        return SleepingPlaceRatingSnapshot::query()
            ->select(['id', 'sleeping_place_id', 'overall_rating', 'cleanliness_rating', 'safety_rating', 'internet_rating', 'reviews_count'])
            ->where('sleeping_place_id', $this->sleepingPlaceId)
            ->first();
    }

    public function render(): View
    {
        return view('livewire.listings.reviews.listing-rating-summary', [
            'snapshot' => $this->snapshot,
        ]);
    }
}
