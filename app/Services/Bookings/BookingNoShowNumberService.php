<?php

namespace App\Services\Bookings;

use App\Models\BookingNoShow;

class BookingNoShowNumberService
{
    public function generate(): string
    {
        $prefix = 'NS-'.now()->format('Y').'-';
        $next = BookingNoShow::query()
            ->where('no_show_number', 'like', $prefix.'%')
            ->count() + 1;

        return $this->ensureUnique($prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT));
    }

    public function ensureUnique(string $number): string
    {
        if (! BookingNoShow::query()->where('no_show_number', $number)->exists()) {
            return $number;
        }

        $prefix = 'NS-'.now()->format('Y').'-';
        $next = BookingNoShow::query()
            ->where('no_show_number', 'like', $prefix.'%')
            ->count() + 1;

        do {
            $candidate = $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $next++;
        } while (BookingNoShow::query()->where('no_show_number', $candidate)->exists());

        return $candidate;
    }
}
