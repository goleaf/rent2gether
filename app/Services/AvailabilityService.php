<?php

namespace App\Services;

use App\Models\Bed;
use Carbon\CarbonInterface;

class AvailabilityService
{
    public function isAvailable(Bed $bed, CarbonInterface|string $checkIn, CarbonInterface|string $checkOut): bool
    {
        $checkInDate = $checkIn instanceof CarbonInterface ? $checkIn->toDateString() : $checkIn;
        $checkOutDate = $checkOut instanceof CarbonInterface ? $checkOut->toDateString() : $checkOut;

        return ! $bed->bookings()
            ->whereNotIn('status', [
                'cancelled_guest',
                'cancelled_host',
                'cancelled_system',
                'cancelled_service',
                'no_show',
            ])
            ->whereDate('check_in', '<', $checkOutDate)
            ->whereDate('check_out', '>', $checkInDate)
            ->exists()
            && ! $bed->availabilities()
                ->whereIn('status', ['blocked', 'maintenance', 'cleaning'])
                ->whereDate('date', '>=', $checkInDate)
                ->whereDate('date', '<', $checkOutDate)
                ->exists();
    }
}
