<?php

namespace App\Livewire\Disputes;

use App\Models\DisputeCase;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GuestDisputeDetailsPage extends Component
{
    public ?int $disputeId = null;

    public function mount(DisputeCase|int|null $dispute = null): void
    {
        $this->disputeId = $dispute instanceof DisputeCase ? $dispute->id : $dispute;
    }

    public function render(): View
    {
        return view('livewire.disputes.guest-dispute-details-page');
    }
}
