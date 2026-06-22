<?php

namespace App\Livewire\Reviews;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class GuestPlaceReviewForm extends Component
{
    public ?int $reviewRequestId = null;

    public function render(): View
    {
        return view('livewire.reviews.guest-place-review-form');
    }
}
