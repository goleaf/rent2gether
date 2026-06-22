<?php

namespace App\Services\Complaints;

use App\Models\ComplaintCase;

class ComplaintNumberService
{
    public function generate(): string
    {
        $prefix = 'CMP-'.now()->format('Y').'-';
        $last = ComplaintCase::query()
            ->where('complaint_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('complaint_number');

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $this->ensureUnique($prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT));
    }

    public function ensureUnique(string $number): string
    {
        if (! ComplaintCase::query()->where('complaint_number', $number)->exists()) {
            return $number;
        }

        $prefix = substr($number, 0, -6);
        $sequence = ((int) substr($number, -6)) + 1;

        return $this->ensureUnique($prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT));
    }
}
