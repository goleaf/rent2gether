<?php

namespace App\Livewire\Bookings\CheckOut;

use App\Livewire\Bookings\CheckOut\Concerns\LoadsBookingCheckOut;
use App\Services\CheckOut\BookingCheckOutService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class GuestCheckOutPage extends Component
{
    use LoadsBookingCheckOut;

    public function markCheckedOut(): void
    {
        $checkOut = $this->checkOut();

        if ($checkOut && Auth::user()) {
            app(BookingCheckOutService::class)->markGuestCheckedOut(Auth::user(), $checkOut);
            $this->refreshCheckOutState();
        }
    }

    public function confirm(): void
    {
        $this->markCheckedOut();
    }

    public function render(): View
    {
        return view('livewire.bookings.check-out.card', $this->checkOutViewData('guest_page'));
    }
}
