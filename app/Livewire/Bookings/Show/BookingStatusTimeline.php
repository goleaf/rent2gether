<?php

namespace App\Livewire\Bookings\Show;

use App\Livewire\Bookings\Concerns\BuildsBookingViewData;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingStatusTimeline extends Component
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

        return view('livewire.bookings.show.booking-status-timeline', [
            'logs' => $booking->statusLogs->sortByDesc('created_at')->values(),
        ]);
    }
}
