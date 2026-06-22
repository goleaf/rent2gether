<?php

namespace App\Livewire\Host\Reviews;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostReviewResponseSheet extends Component
{
    public ?int $reviewId = null;

    public string $responseText = '';

    public function render(): View
    {
        return view('livewire.host.reviews.host-review-response-sheet');
    }
}
