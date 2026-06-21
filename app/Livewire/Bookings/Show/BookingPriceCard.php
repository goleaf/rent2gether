<?php

namespace App\Livewire\Bookings\Show;

use App\Livewire\Bookings\Concerns\BuildsBookingViewData;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingPriceCard extends Component
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
        return view('livewire.bookings.show.booking-price-card', [
            'summary' => $this->bookingSummary($this->loadBooking($this->bookingId)),
        ]);
    }
}
