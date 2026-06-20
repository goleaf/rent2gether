<?php

namespace App\Livewire\Bookings\CheckIn;

use App\Livewire\Bookings\CheckIn\Concerns\LoadsBookingCheckIn;
use App\Services\CheckIn\BookingCheckInInstructionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class GuestCheckInInstructions extends Component
{
    use LoadsBookingCheckIn;

    public function render(): View
    {
        $booking = $this->booking();

        return view('livewire.bookings.check-in.card', [
            ...$this->checkInViewData('instructions'),
            'instructions' => $booking && Auth::user()
                ? app(BookingCheckInInstructionService::class)->getGuestInstructions(Auth::user(), $booking)
                : [],
        ]);
    }
}
