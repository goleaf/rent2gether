<?php

namespace App\Livewire\Bookings\GuestIntake;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class DocumentsAndSpecialRequestsStep extends Component
{
    public function render(): View
    {
        return view('livewire.bookings.guest-intake.documents-and-special-requests-step');
    }
}
