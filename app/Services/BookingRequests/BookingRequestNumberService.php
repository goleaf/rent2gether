<?php

namespace App\Services\BookingRequests;

use App\Models\BookingRequest;
use Carbon\CarbonImmutable;

class BookingRequestNumberService
{
    public function generate(): string
    {
        $year = CarbonImmutable::now()->format('Y');
        $next = BookingRequest::query()
            ->where('request_number', 'like', "BR-{$year}-%")
            ->count() + 1;

        return $this->ensureUnique(sprintf('BR-%s-%06d', $year, $next));
    }

    public function ensureUnique(string $number): string
    {
        if (! BookingRequest::query()->where('request_number', $number)->exists()) {
            return $number;
        }

        $year = CarbonImmutable::now()->format('Y');

        for ($attempt = 1; $attempt < 1000; $attempt++) {
            $candidate = sprintf('BR-%s-%06d', $year, BookingRequest::query()->count() + $attempt + 1);

            if (! BookingRequest::query()->where('request_number', $candidate)->exists()) {
                return $candidate;
            }
        }

        return 'BR-'.$year.'-'.str_replace('.', '', (string) microtime(true));
    }
}
