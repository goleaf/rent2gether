<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;

class BookingCheckOutNumberService
{
    public function generate(): string
    {
        $year = now()->format('Y');
        $sequence = BookingCheckOut::query()
            ->where('checkout_number', 'like', "OUT-{$year}-%")
            ->count() + 1;

        return $this->ensureUnique(sprintf('OUT-%s-%06d', $year, $sequence));
    }

    public function ensureUnique(string $number): string
    {
        if (! BookingCheckOut::query()->where('checkout_number', $number)->exists()) {
            return $number;
        }

        $year = now()->format('Y');
        $sequence = BookingCheckOut::query()
            ->where('checkout_number', 'like', "OUT-{$year}-%")
            ->count() + 1;

        return sprintf('OUT-%s-%06d', $year, $sequence);
    }
}
