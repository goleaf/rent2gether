<?php

namespace App\Livewire\Host\CheckIn;

use App\Livewire\Bookings\CheckIn\Concerns\LoadsBookingCheckIn;
use Illuminate\View\View;
use Livewire\Component;

class HostCheckInInstructionSender extends Component
{
    use LoadsBookingCheckIn;

    public function sendInstruction(): void
    {
        $checkIn = $this->checkIn();

        if ($checkIn) {
            $checkIn->forceFill(['instructions_available_at' => now()])->save();
            $this->refreshCheckInState();
        }
    }

    public function render(): View
    {
        return view('livewire.host.check-in.details-sheet', $this->checkInViewData('host_instruction_sender'));
    }
}
