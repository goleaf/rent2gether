<?php

namespace App\Livewire\Reviews;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ReviewStatusBadge extends Component
{
    public string $status = 'draft';

    public function render(): View
    {
        return view('livewire.reviews.review-status-badge');
    }
}
