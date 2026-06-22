<?php

namespace App\Livewire\Reviews;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ReviewResponseForm extends Component
{
    public ?int $reviewId = null;

    public string $responseText = '';

    public function render(): View
    {
        return view('livewire.reviews.review-response-form');
    }
}
