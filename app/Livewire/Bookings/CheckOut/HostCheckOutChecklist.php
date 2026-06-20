<?php

namespace App\Livewire\Bookings\CheckOut;

use App\Livewire\Bookings\CheckOut\Concerns\LoadsBookingCheckOut;
use App\Services\CheckOut\BookingCheckOutChecklistService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class HostCheckOutChecklist extends Component
{
    use LoadsBookingCheckOut;

    public function markCompleted(string $itemKey): void
    {
        $checkOut = $this->checkOut();

        if ($checkOut && Auth::user()) {
            app(BookingCheckOutChecklistService::class)->markItemCompleted(Auth::user(), $checkOut, $itemKey);
            $this->refreshCheckOutState();
        }
    }

    public function render(): View
    {
        return view('livewire.bookings.check-out.card', $this->checkOutViewData('host_checklist'));
    }
}
