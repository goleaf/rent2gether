<?php

namespace App\Services\Bookings;

use App\Models\Booking;

class BookingNumberService
{
    public function generate(): string
    {
        $year = now()->format('Y');
        $next = Booking::query()
            ->where('booking_number', 'like', "BK-{$year}-%")
            ->count() + 1;

        return $this->ensureUnique(sprintf('BK-%s-%06d', $year, $next));
    }

    public function ensureUnique(string $number): string
    {
        if (! Booking::query()->where('booking_number', $number)->exists()) {
            return $number;
        }

        $year = now()->format('Y');
        $next = Booking::query()
            ->where('booking_number', 'like', "BK-{$year}-%")
            ->count() + 1;

        do {
            $candidate = sprintf('BK-%s-%06d', $year, $next);
            $next++;
        } while (Booking::query()->where('booking_number', $candidate)->exists());

        return $candidate;
    }
}
