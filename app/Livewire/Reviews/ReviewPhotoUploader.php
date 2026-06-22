<?php

namespace App\Livewire\Reviews;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ReviewPhotoUploader extends Component
{
    public ?int $reviewId = null;

    public function render(): View
    {
        return view('livewire.reviews.review-photo-uploader');
    }
}
