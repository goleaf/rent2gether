<?php

namespace App\Services\Cleaning;

use App\Models\CleaningTask;

class CleaningNumberService
{
    public function generate(): string
    {
        $prefix = 'CLN-'.now()->format('Y').'-';
        $next = CleaningTask::query()
            ->where('cleaning_number', 'like', $prefix.'%')
            ->count() + 1;

        return $this->ensureUnique($prefix.sprintf('%06d', $next));
    }

    public function ensureUnique(string $number): string
    {
        $candidate = $number;
        $suffix = 1;

        while (CleaningTask::query()->where('cleaning_number', $candidate)->exists()) {
            $candidate = preg_replace('/\d{6}$/', sprintf('%06d', $suffix + 1), $number) ?: $number.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
