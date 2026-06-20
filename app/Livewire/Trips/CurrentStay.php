<?php

namespace App\Livewire\Trips;

use App\Livewire\Trips\Concerns\LoadsTripBookings;
use App\Support\Trips\TripBookingPresenter;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CurrentStay extends Component
{
    use LoadsTripBookings;

    public function render(TripBookingPresenter $presenter): View
    {
        $booking = $this->tripBookingQuery()
            ->forGuest((int) auth()->id())
            ->whereIn('status', TripBookingPresenter::activeStayStatuses())
            ->orderBy('check_out_date')
            ->orderBy('id')
            ->first();

        return view('livewire.trips.current-stay', [
            'booking' => $booking,
            'stay' => $booking ? $presenter->currentStay($booking) : null,
        ])->layout('layouts.app', [
            'title' => __('booking.trips.current.title'),
        ]);
    }
}
