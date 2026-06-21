<?php

namespace App\Services\Stays;

use App\Models\BookingStay;

class BookingStayNumberService
{
    public function generate(): string
    {
        $year = now()->format('Y');
        $next = BookingStay::query()
            ->where('stay_number', 'like', "STAY-{$year}-%")
            ->count() + 1;

        return $this->ensureUnique(sprintf('STAY-%s-%06d', $year, $next));
    }

    public function ensureUnique(string $number): string
    {
        if (! BookingStay::query()->where('stay_number', $number)->exists()) {
            return $number;
        }

        $year = now()->format('Y');
        $next = BookingStay::query()
            ->where('stay_number', 'like', "STAY-{$year}-%")
            ->count() + 1;

        do {
            $candidate = sprintf('STAY-%s-%06d', $year, $next);
            $next++;
        } while (BookingStay::query()->where('stay_number', $candidate)->exists());

        return $candidate;
    }
}
