<?php

namespace App\Livewire\Host\Bookings;

use App\Livewire\Bookings\Concerns\BuildsBookingViewData;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostBookingDetailsSheet extends Component
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
        $booking = $this->loadBooking($this->bookingId);

        return view('livewire.host.bookings.host-booking-details-sheet', [
            'summary' => $this->bookingSummary($booking),
            'requirements' => $booking->requirements,
            'events' => $booking->lifecycleEvents->sortByDesc('occurred_at')->values(),
        ]);
    }
}
