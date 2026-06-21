<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchReport;

class ListingMismatchNumberService
{
    public function generate(): string
    {
        $prefix = 'MM-'.now()->format('Y').'-';
        $lastNumber = BookingListingMismatchReport::query()
            ->where('mismatch_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('mismatch_number');

        $sequence = $lastNumber ? ((int) substr((string) $lastNumber, -6)) + 1 : 1;

        return $this->ensureUnique($prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT));
    }

    public function ensureUnique(string $number): string
    {
        if (! BookingListingMismatchReport::query()->where('mismatch_number', $number)->exists()) {
            return $number;
        }

        return $this->ensureUnique('MM-'.now()->format('Y').'-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT));
    }
}
