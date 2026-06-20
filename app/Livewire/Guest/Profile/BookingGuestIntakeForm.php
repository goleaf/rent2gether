<?php

namespace App\Livewire\Guest\Profile;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class BookingGuestIntakeForm extends Component
{
    public ?int $sleepingPlaceId = null;

    public string $tripPurpose = '';

    public function render(): View
    {
        return view('livewire.guest.profile.booking-guest-intake-form');
    }
}
