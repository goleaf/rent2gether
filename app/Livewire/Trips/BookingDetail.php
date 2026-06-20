<?php

namespace App\Livewire\Trips;

use App\Livewire\Trips\Concerns\LoadsTripBookings;
use App\Models\Booking;
use App\Models\User;
use App\Support\Trips\TripBookingPresenter;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingDetail extends Component
{
    use LoadsTripBookings;

    #[Locked]
    public int $bookingId;

    public function mount(Booking $booking): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User && (int) $booking->guest_user_id === (int) $user->id, 403);

        $this->bookingId = $booking->id;
    }

    public function render(TripBookingPresenter $presenter): View
    {
        $booking = $this->booking();

        return view('livewire.trips.booking-detail', [
            'booking' => $booking,
            'trip' => $presenter->detail($booking),
        ])->layout('layouts.app', [
            'title' => __('booking.trips.detail.title'),
        ]);
    }

    private function booking(): Booking
    {
        return $this->tripBookingQuery()
            ->forGuest((int) auth()->id())
            ->findOrFail($this->bookingId);
    }
}
