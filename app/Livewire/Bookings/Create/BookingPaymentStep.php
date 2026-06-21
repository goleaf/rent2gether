<?php

namespace App\Livewire\Bookings\Create;

use App\Livewire\Bookings\Concerns\BuildsBookingViewData;
use App\Models\Booking;
use App\Services\Bookings\BookingPaymentStateService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingPaymentStep extends Component
{
    use BuildsBookingViewData;

    #[Locked]
    public int $bookingId;

    public function mount(int|Booking $bookingId): void
    {
        $this->bookingId = $bookingId instanceof Booking ? $bookingId->id : $bookingId;
    }

    public function markPaid(BookingPaymentStateService $payments): void
    {
        $booking = Booking::query()
            ->select(['id', 'status', 'payment_status', 'approval_type', 'payment_method', 'paid_at', 'payment_paid_at'])
            ->findOrFail($this->bookingId);

        $payments->markPaid($booking, ['payment_method' => 'manual_mvp']);
    }

    public function render(): View
    {
        return view('livewire.bookings.create.booking-payment-step', [
            'summary' => $this->bookingSummary($this->loadBooking($this->bookingId)),
        ]);
    }
}
