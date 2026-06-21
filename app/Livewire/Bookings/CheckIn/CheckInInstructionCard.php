<?php

namespace App\Livewire\Bookings\CheckIn;

use App\Livewire\Bookings\CheckIn\Concerns\LoadsBookingCheckIn;
use App\Services\CheckIn\BookingCheckInInstructionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class CheckInInstructionCard extends Component
{
    use LoadsBookingCheckIn;

    public function render(): View
    {
        $checkIn = $this->checkIn();
        $instructions = $checkIn && Auth::user()
            ? app(BookingCheckInInstructionService::class)->getVisibleInstructions(Auth::user(), $checkIn)
            : null;

        return view('livewire.bookings.check-in.card', [
            ...$this->checkInViewData('instruction_card'),
            'instructions' => $instructions,
        ]);
    }
}
