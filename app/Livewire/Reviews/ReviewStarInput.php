<?php

namespace App\Livewire\Reviews;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ReviewStarInput extends Component
{
    public string $scoreKey = 'overall';

    public int $value = 5;

    public function setValue(int $value): void
    {
        $this->value = max(1, min(5, $value));
    }

    public function render(): View
    {
        return view('livewire.reviews.review-star-input');
    }
}
