<?php

namespace App\Livewire\Reviews;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ReviewScoreGroup extends Component
{
    public string $group = 'place';

    public function render(): View
    {
        return view('livewire.reviews.review-score-group', [
            'scores' => $this->scoreKeys(),
        ]);
    }

    /**
     * @return list<string>
     */
    private function scoreKeys(): array
    {
        return $this->group === 'guest'
            ? ['overall', 'rules_respect', 'cleanliness_after_stay', 'communication', 'punctuality']
            : ['overall', 'cleanliness', 'safety', 'sleeping_place_quality', 'noise_level', 'internet'];
    }
}
