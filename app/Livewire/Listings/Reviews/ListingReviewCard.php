<?php

namespace App\Livewire\Listings\Reviews;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ListingReviewCard extends Component
{
    public ?int $reviewId = null;

    public function render(): View
    {
        return view('livewire.listings.reviews.listing-review-card');
    }
}
