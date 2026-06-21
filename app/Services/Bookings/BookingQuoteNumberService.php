<?php

namespace App\Services\Bookings;

use App\Models\BookingQuote;

class BookingQuoteNumberService
{
    public function generate(): string
    {
        $prefix = 'QT-'.now()->format('Y').'-';
        $latest = BookingQuote::query()
            ->where('quote_number', 'like', $prefix.'%')
            ->orderByDesc('quote_number')
            ->value('quote_number');

        $next = $latest ? ((int) substr((string) $latest, -6)) + 1 : 1;

        return $this->ensureUnique($prefix.sprintf('%06d', $next));
    }

    public function ensureUnique(string $number): string
    {
        if (! BookingQuote::query()->where('quote_number', $number)->exists()) {
            return $number;
        }

        $prefix = substr($number, 0, -6);
        $next = (int) substr($number, -6);

        do {
            $next++;
            $candidate = $prefix.sprintf('%06d', $next);
        } while (BookingQuote::query()->where('quote_number', $candidate)->exists());

        return $candidate;
    }
}
