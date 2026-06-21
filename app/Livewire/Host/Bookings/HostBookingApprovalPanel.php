<?php

namespace App\Livewire\Host\Bookings;

use App\Livewire\Bookings\Concerns\BuildsBookingViewData;
use App\Models\Booking;
use App\Services\Bookings\BookingHostApprovalService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostBookingApprovalPanel extends Component
{
    use BuildsBookingViewData;

    #[Locked]
    public int $bookingId;

    public string $message = '';

    public string $rejectionReason = '';

    public function mount(int|Booking $bookingId): void
    {
        $this->bookingId = $bookingId instanceof Booking ? $bookingId->id : $bookingId;
    }

    public function approve(BookingHostApprovalService $approval): void
    {
        $booking = Booking::query()->findOrFail($this->bookingId);
        $approval->approve(auth()->user() ?: $booking->host, $booking, $this->message ?: null);
    }

    public function reject(BookingHostApprovalService $approval): void
    {
        $this->validate([
            'rejectionReason' => ['required', 'string', 'max:500'],
        ]);

        $booking = Booking::query()->findOrFail($this->bookingId);
        $approval->reject(auth()->user() ?: $booking->host, $booking, $this->rejectionReason);
    }

    public function render(): View
    {
        return view('livewire.host.bookings.host-booking-approval-panel', [
            'summary' => $this->bookingSummary($this->loadBooking($this->bookingId)),
        ]);
    }
}
