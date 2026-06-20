<?php

namespace App\Services\HostOccupants\Data;

final readonly class HostOccupantSummaryData
{
    public function __construct(
        public int $currentCount = 0,
        public int $checkInsTodayCount = 0,
        public int $checkOutsTodayCount = 0,
        public int $needsAttentionCount = 0,
        public int $paymentPendingCount = 0,
        public int $complaintsCount = 0,
    ) {}
}
