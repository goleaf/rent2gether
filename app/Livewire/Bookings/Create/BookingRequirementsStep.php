<?php

namespace App\Livewire\Bookings\Create;

use App\Livewire\Bookings\Concerns\BuildsBookingViewData;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingRequirementsStep extends Component
{
    use BuildsBookingViewData;

    #[Locked]
    public int $bookingId;

    public function mount(int|Booking $bookingId): void
    {
        $this->bookingId = $bookingId instanceof Booking ? $bookingId->id : $bookingId;
    }

    public function render(): View
    {
        return view('livewire.bookings.create.booking-requirements-step', [
            'requirements' => $this->loadBooking($this->bookingId)->requirements,
        ]);
    }
}
