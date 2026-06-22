<?php

namespace App\Livewire\Listings\Reviews;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ListingReviewFilters extends Component
{
    public string $filter = 'all';

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function render(): View
    {
        return view('livewire.listings.reviews.listing-review-filters');
    }
}
