<?php

namespace App\Livewire\Reviews;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostGuestReviewForm extends Component
{
    public ?int $reviewRequestId = null;

    public function render(): View
    {
        return view('livewire.reviews.host-guest-review-form');
    }
}
