<?php

namespace App\Livewire\Search;

use App\Models\Bed;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BedCard extends Component
{
    #[Locked]
    public Bed $bed;

    #[Locked]
    public int $nights = 0;

    public function render(): View
    {
        return view('livewire.search.bed-card');
    }
}
