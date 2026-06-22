<?php

namespace App\Livewire\Bookings\Readiness;

use App\Models\PlaceReadinessCheck;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GuestPlaceReadyNotice extends Component
{
    public ?int $bookingId = null;

    public function render(): View
    {
        $check = $this->bookingId
            ? PlaceReadinessCheck::query()
                ->where('booking_id', $this->bookingId)
                ->latest('id')
                ->first()
            : null;

        return view('livewire.bookings.readiness.guest-place-ready-notice', [
            'status' => $check?->status,
        ]);
    }
}
