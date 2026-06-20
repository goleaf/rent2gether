<?php

namespace App\Services\Hints;

use App\Data\Hints\GuestHintData;
use App\Models\SleepingPlace;
use App\Services\Hints\Concerns\BuildsGuestHints;

class CancellationHintService
{
    use BuildsGuestHints;

    public function strictCancellation(SleepingPlace $place): ?GuestHintData
    {
        if ($this->value($place->cancellation_policy) !== 'strict') {
            return null;
        }

        return $this->hint('strict_cancellation', 'cancellation', 'warning', 'medium', 52, beforeBooking: true, source: 'cancellation');
    }
}
