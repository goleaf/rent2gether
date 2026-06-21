<?php

namespace App\Services\Bookings;

use App\Models\BookingCancellation;
use App\Models\BookingCancellationPreview;

class BookingCancellationNumberService
{
    public function generateCancellationNumber(): string
    {
        return $this->generateFor('CAN', BookingCancellation::query()->where('cancellation_number', 'like', $this->prefix('CAN').'%')->count() + 1);
    }

    public function generatePreviewNumber(): string
    {
        return $this->generateFor('CANPRE', BookingCancellationPreview::query()->where('preview_number', 'like', $this->prefix('CANPRE').'%')->count() + 1);
    }

    public function ensureUnique(string $number): string
    {
        [$prefix] = explode('-', $number);
        $next = (int) substr($number, -6);
        $candidate = $number;

        while ($this->exists($candidate)) {
            $next++;
            $candidate = $this->generateFor($prefix, $next);
        }

        return $candidate;
    }

    private function generateFor(string $prefix, int $sequence): string
    {
        return $this->ensureUnique(sprintf('%s%06d', $this->prefix($prefix), $sequence));
    }

    private function prefix(string $prefix): string
    {
        return sprintf('%s-%s-', $prefix, now()->format('Y'));
    }

    private function exists(string $number): bool
    {
        return BookingCancellation::query()->where('cancellation_number', $number)->exists()
            || BookingCancellationPreview::query()->where('preview_number', $number)->exists();
    }
}
