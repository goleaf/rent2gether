<?php

namespace App\Livewire\Host\Complaints;

use App\Models\ComplaintCase;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostComplaintDetailsSheet extends Component
{
    public ?int $caseId = null;

    public function mount(ComplaintCase|int|null $case = null): void
    {
        $this->caseId = $case instanceof ComplaintCase ? $case->id : $case;
    }

    public function render(): View
    {
        return view('livewire.host.complaints.host-complaint-details-sheet');
    }
}
