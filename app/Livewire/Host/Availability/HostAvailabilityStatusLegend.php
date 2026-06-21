<?php

namespace App\Livewire\Host\Availability;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostAvailabilityStatusLegend extends Component
{
    public function statuses(): array
    {
        return [
            'available',
            'request_only',
            'payment_pending',
            'host_confirmation_pending',
            'booked',
            'cleaning',
            'repair',
            'closed_by_host',
            'temporarily_hidden',
        ];
    }

    public function render(): View
    {
        return view('livewire.host.availability.host-availability-status-legend');
    }
}
