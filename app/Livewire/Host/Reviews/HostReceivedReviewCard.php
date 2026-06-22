<?php

namespace App\Livewire\Host\Reviews;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostReceivedReviewCard extends Component
{
    public ?int $reviewId = null;

    public function render(): View
    {
        return view('livewire.host.reviews.host-received-review-card');
    }
}
