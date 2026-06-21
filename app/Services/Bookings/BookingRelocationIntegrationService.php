<?php

namespace App\Services\Bookings;

use App\Enums\BookingType;
use App\Models\Booking;

class BookingRelocationIntegrationService
{
    public function linkRelocationSegment(Booking $newBookingSegment, Booking $sourceBooking): Booking
    {
        $newBookingSegment->forceFill([
            'booking_type' => BookingType::Relocation->value,
            'relocation_from_booking_id' => $sourceBooking->id,
        ])->save();

        return $newBookingSegment->fresh();
    }
}
