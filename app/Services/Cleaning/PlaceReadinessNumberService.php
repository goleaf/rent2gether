<?php

namespace App\Services\Cleaning;

use App\Models\PlaceReadinessCheck;

class PlaceReadinessNumberService
{
    public function generate(): string
    {
        $prefix = 'RDY-'.now()->format('Y').'-';
        $next = PlaceReadinessCheck::query()
            ->where('readiness_number', 'like', $prefix.'%')
            ->count() + 1;

        return $this->ensureUnique($prefix.sprintf('%06d', $next));
    }

    public function ensureUnique(string $number): string
    {
        $candidate = $number;
        $suffix = 1;

        while (PlaceReadinessCheck::query()->where('readiness_number', $candidate)->exists()) {
            $candidate = preg_replace('/\d{6}$/', sprintf('%06d', $suffix + 1), $number) ?: $number.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
