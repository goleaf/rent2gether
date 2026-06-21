<?php

namespace App\Livewire\Bookings\Extensions;

use App\Livewire\Bookings\Extensions\Concerns\LoadsBookingExtension;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class RequestExtensionButton extends Component
{
    use LoadsBookingExtension;

    public function mount(Booking|int|null $booking = null): void
    {
        $this->mountBookingExtension($booking);
    }

    public function render(): View
    {
        return view('livewire.bookings.extensions.card', $this->extensionViewData('request_button'));
    }
}
