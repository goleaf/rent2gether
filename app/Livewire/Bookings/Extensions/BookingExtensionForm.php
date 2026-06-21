<?php

namespace App\Livewire\Bookings\Extensions;

use App\Models\Booking;
use App\Models\BookingExtension;
use Illuminate\Contracts\View\View;

class BookingExtensionForm extends GuestExtensionPage
{
    public function mount(Booking|int|null $booking = null, BookingExtension|int|null $extension = null): void
    {
        $this->mountBookingExtension($booking, $extension);
    }

    public function render(): View
    {
        return view('livewire.bookings.extensions.card', $this->extensionViewData('form'));
    }
}
