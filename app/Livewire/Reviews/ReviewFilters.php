<?php

namespace App\Livewire\Reviews;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ReviewFilters extends Component
{
    public string $filter = 'all';

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function render(): View
    {
        return view('livewire.reviews.review-filters');
    }
}
