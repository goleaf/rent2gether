<?php

namespace App\Livewire\Host\CheckIn;

use App\Livewire\Bookings\CheckIn\Concerns\LoadsBookingCheckIn;
use App\Services\CheckIn\BookingCheckInService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class HostCheckInDetailsSheet extends Component
{
    use LoadsBookingCheckIn;

    public function confirm(): void
    {
        $checkIn = $this->checkIn();

        if ($checkIn && Auth::user()) {
            app(BookingCheckInService::class)->confirmByHost(Auth::user(), $checkIn);
            $this->refreshCheckInState();
        }
    }

    public function render(): View
    {
        return view('livewire.host.check-in.details-sheet', $this->checkInViewData('host_details_sheet'));
    }
}
