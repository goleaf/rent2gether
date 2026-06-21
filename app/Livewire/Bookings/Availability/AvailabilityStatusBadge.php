<?php

namespace App\Livewire\Bookings\Availability;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class AvailabilityStatusBadge extends Component
{
    public string $status = 'available';

    public function render(): View
    {
        return view('livewire.bookings.availability.availability-status-badge');
    }
}
