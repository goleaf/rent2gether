<?php

namespace App\Livewire\Search;

use App\Models\Bed;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BedCard extends Component
{
    #[Locked]
    public Bed $bed;

    #[Locked]
    public int $nights = 0;

    #[Computed]
    public function priceSummary(): ?array
    {
        if ($this->nights <= 0) {
            return null;
        }

        return $this->bed->calculatePrice(request('in', now()->toDateString()), request('out', now()->addDays($this->nights)->toDateString()));
    }

    public function render(): View
    {
        return view('livewire.search.bed-card');
    }
}
