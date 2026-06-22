<?php

namespace App\Services\Disputes;

use App\Models\DisputeCase;

class DisputeNumberService
{
    public function generate(): string
    {
        $prefix = 'DSP-'.now()->format('Y').'-';
        $last = DisputeCase::query()
            ->where('dispute_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('dispute_number');
        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $this->ensureUnique($prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT));
    }

    public function ensureUnique(string $number): string
    {
        if (! DisputeCase::query()->where('dispute_number', $number)->exists()) {
            return $number;
        }

        $prefix = substr($number, 0, -6);
        $sequence = ((int) substr($number, -6)) + 1;

        return $this->ensureUnique($prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT));
    }
}
