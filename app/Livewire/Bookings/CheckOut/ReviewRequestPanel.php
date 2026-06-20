<?php

namespace App\Livewire\Bookings\CheckOut;

use App\Livewire\Bookings\CheckOut\Concerns\LoadsBookingCheckOut;
use App\Services\CheckOut\BookingReviewRequestService;
use Illuminate\View\View;
use Livewire\Component;

class ReviewRequestPanel extends Component
{
    use LoadsBookingCheckOut;

    public function sendRequests(): void
    {
        $checkOut = $this->checkOut();

        if ($checkOut) {
            app(BookingReviewRequestService::class)->sendReviewRequests($checkOut);
            $this->refreshCheckOutState();
        }
    }

    public function render(): View
    {
        return view('livewire.bookings.check-out.card', $this->checkOutViewData('review_request'));
    }
}
