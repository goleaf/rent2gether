<?php

namespace App\Services\HostOccupants;

use App\Models\User;
use App\Services\HostOccupants\Data\HostOccupantFilters;
use App\Services\HostOccupants\Data\HostOccupantSummaryData;
use Carbon\CarbonImmutable;

class HostOccupantSummaryService
{
    public function __construct(
        private readonly HostCurrentOccupantsService $occupants,
    ) {}

    public function getSummary(User $host): HostOccupantSummaryData
    {
        $today = CarbonImmutable::today()->toDateString();
        $occupants = $this->occupants->getCurrentOccupants($host, new HostOccupantFilters);

        return new HostOccupantSummaryData(
            currentCount: $occupants->count(),
            checkInsTodayCount: $occupants
                ->filter(fn ($snapshot): bool => $snapshot->check_in_date?->toDateString() === $today)
                ->count(),
            checkOutsTodayCount: $occupants
                ->filter(fn ($snapshot): bool => $snapshot->check_out_date?->toDateString() === $today)
                ->count(),
            needsAttentionCount: $occupants
                ->filter(fn ($snapshot): bool => $snapshot->has_complaints
                    || $snapshot->needs_extension
                    || $snapshot->needs_checkout
                    || $snapshot->checkout_due_today
                    || $snapshot->checkout_overdue
                    || $snapshot->needs_cleaning_after_checkout
                    || in_array($snapshot->payment_status, ['pending', 'partial', 'overdue'], true))
                ->count(),
            paymentPendingCount: $occupants->whereIn('payment_status', ['pending', 'partial', 'overdue'])->count(),
            complaintsCount: $occupants->where('has_complaints', true)->count(),
        );
    }
}
