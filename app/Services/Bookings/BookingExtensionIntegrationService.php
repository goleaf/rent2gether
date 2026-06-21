<?php

namespace App\Services\Bookings;

use App\Enums\BookingType;
use App\Models\Booking;

class BookingExtensionIntegrationService
{
    public function linkExtensionBooking(Booking $booking, Booking $sourceBooking): Booking
    {
        $booking->forceFill([
            'booking_type' => BookingType::Extension->value,
            'extension_from_booking_id' => $sourceBooking->id,
            'sleeping_place_id' => $sourceBooking->sleeping_place_id,
            'room_id' => $sourceBooking->room_id,
            'property_id' => $sourceBooking->property_id,
        ])->save();

        return $booking->fresh();
    }
}
