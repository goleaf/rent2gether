<?php

namespace App\Livewire\Host\Reviews;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostReviewRequestCard extends Component
{
    public ?int $reviewRequestId = null;

    public function render(): View
    {
        return view('livewire.host.reviews.host-review-request-card');
    }
}
