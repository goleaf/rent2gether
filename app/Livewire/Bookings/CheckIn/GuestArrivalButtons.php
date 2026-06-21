<?php

namespace App\Livewire\Bookings\CheckIn;

use App\Livewire\Bookings\CheckIn\Concerns\LoadsBookingCheckIn;
use App\Services\CheckIn\BookingCheckInService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class GuestArrivalButtons extends Component
{
    use LoadsBookingCheckIn;

    public function markOnTheWay(): void
    {
        $checkIn = $this->checkIn();

        if ($checkIn && Auth::user()) {
            app(BookingCheckInService::class)->markGuestOnTheWay(Auth::user(), $checkIn);
            $this->refreshCheckInState();
        }
    }

    public function markArrived(): void
    {
        $checkIn = $this->checkIn();

        if ($checkIn && Auth::user()) {
            app(BookingCheckInService::class)->markGuestArrived(Auth::user(), $checkIn);
            $this->refreshCheckInState();
        }
    }

    public function render(): View
    {
        return view('livewire.bookings.check-in.card', $this->checkInViewData('guest_arrival_buttons'));
    }
}
