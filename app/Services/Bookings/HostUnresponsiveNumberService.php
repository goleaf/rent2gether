<?php

namespace App\Services\Bookings;

use App\Models\BookingHostUnresponsiveCase;

class HostUnresponsiveNumberService
{
    public function generate(): string
    {
        $prefix = 'HU-'.now()->format('Y').'-';
        $next = BookingHostUnresponsiveCase::query()
            ->where('case_number', 'like', $prefix.'%')
            ->count() + 1;

        return $this->ensureUnique($prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT));
    }

    public function ensureUnique(string $number): string
    {
        if (! BookingHostUnresponsiveCase::query()->where('case_number', $number)->exists()) {
            return $number;
        }

        $prefix = substr($number, 0, -6);
        $candidate = (int) substr($number, -6);

        do {
            $candidate++;
            $next = $prefix.str_pad((string) $candidate, 6, '0', STR_PAD_LEFT);
        } while (BookingHostUnresponsiveCase::query()->where('case_number', $next)->exists());

        return $next;
    }
}
