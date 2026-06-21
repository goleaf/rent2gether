<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Models\BookingStay;

class BookingCheckOutStayIntegrationService
{
    public function markStayCheckedOut(BookingCheckOut $checkOut): BookingStay
    {
        $stay = $this->stay($checkOut);
        $stay->forceFill([
            'status' => 'guest_checked_out',
            'actual_check_out_at' => $checkOut->actual_check_out_at ?: now(),
            'ended_at' => $checkOut->actual_check_out_at ?: now(),
        ])->save();

        return $stay->refresh();
    }

    public function markStayCompleted(BookingCheckOut $checkOut): BookingStay
    {
        $stay = $this->stay($checkOut);
        $stay->forceFill([
            'status' => 'completed',
            'actual_check_out_at' => $checkOut->actual_check_out_at ?: now(),
            'ended_at' => $checkOut->completed_at ?: now(),
        ])->save();

        return $stay->refresh();
    }

    public function closeStayIfCheckoutClosed(BookingCheckOut $checkOut): BookingStay
    {
        $stay = $this->stay($checkOut);
        $stay->forceFill([
            'status' => 'closed',
            'closed_at' => $checkOut->closed_at ?: now(),
        ])->save();

        return $stay->refresh();
    }

    private function stay(BookingCheckOut $checkOut): BookingStay
    {
        return $checkOut->stay ?: $checkOut->booking()->firstOrFail()->stay()->firstOrFail();
    }
}
