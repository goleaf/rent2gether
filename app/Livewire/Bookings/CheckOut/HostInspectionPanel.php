<?php

namespace App\Livewire\Bookings\CheckOut;

use App\Livewire\Bookings\CheckOut\Concerns\LoadsBookingCheckOut;
use App\Services\CheckOut\BookingCheckOutInspectionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class HostInspectionPanel extends Component
{
    use LoadsBookingCheckOut;

    public bool $roomChecked = true;

    public bool $sleepingPlaceChecked = true;

    public bool $sleepingPlaceFree = true;

    public bool $hasDamage = false;

    public bool $hasExtraDirty = false;

    public function completeInspection(): void
    {
        $checkOut = $this->checkOut();

        if ($checkOut && Auth::user()) {
            app(BookingCheckOutInspectionService::class)->completeInspection(Auth::user(), $checkOut, [
                'room_checked' => $this->roomChecked,
                'sleeping_place_checked' => $this->sleepingPlaceChecked,
                'sleeping_place_free' => $this->sleepingPlaceFree,
                'has_damage' => $this->hasDamage,
                'has_extra_dirty' => $this->hasExtraDirty,
            ]);
            $this->refreshCheckOutState();
        }
    }

    public function render(): View
    {
        return view('livewire.bookings.check-out.card', $this->checkOutViewData('inspection_panel'));
    }
}
