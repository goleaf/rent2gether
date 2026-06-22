<?php

namespace App\Livewire\Complaints;

use App\Models\Booking;
use App\Models\ComplaintCase;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GuestComplaintForm extends Component
{
    public ?int $bookingId = null;

    public ?int $caseId = null;

    public string $complaintType = 'other';

    public string $desiredResolutionType = 'fix_problem';

    public function mount(Booking|int|null $booking = null, ComplaintCase|int|null $case = null): void
    {
        $this->bookingId = $booking instanceof Booking ? $booking->id : $booking;
        $this->caseId = $case instanceof ComplaintCase ? $case->id : $case;
    }

    public function render(): View
    {
        return view('livewire.complaints.guest-complaint-form');
    }
}
