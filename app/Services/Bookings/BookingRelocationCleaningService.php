<?php

namespace App\Services\Bookings;

use App\Models\BookingRelocation;

class BookingRelocationCleaningService
{
    public function __construct(
        private readonly BookingRelocationEventService $events,
    ) {}

    public function createOldPlaceCleaningIfNeeded(BookingRelocation $relocation): mixed
    {
        $this->events->record($relocation, 'old_place_cleaning_created');

        return null;
    }

    public function createOldPlaceInspectionIfNeeded(BookingRelocation $relocation): mixed
    {
        $this->events->record($relocation, 'old_place_released_for_inspection');

        return null;
    }

    public function createRepairIfNeeded(BookingRelocation $relocation): mixed
    {
        if (! in_array($relocation->reason, ['breakdown', 'maintenance_issue'], true)) {
            return null;
        }

        $this->events->record($relocation, 'relocation_scheduled', ['repair_required' => true]);

        return null;
    }

    public function cancelUnneededCleaning(BookingRelocation $relocation): void
    {
        $this->events->record($relocation, 'relocation_cancelled', ['cleaning_cancelled' => true]);
    }
}
