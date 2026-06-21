<?php

namespace App\Services\Bookings;

use App\Models\BookingRelocation;
use App\Models\SleepingPlaceBookingDateLock;
use Illuminate\Support\Collection;

class BookingRelocationCalendarService
{
    public function __construct(
        private readonly BookingRelocationHoldService $holds,
    ) {}

    /**
     * @return Collection<int, SleepingPlaceBookingDateLock>
     */
    public function createNewPlaceLocks(BookingRelocation $relocation): Collection
    {
        return $this->holds->createNewPlaceHold($relocation);
    }

    public function releaseNewPlaceLocks(BookingRelocation $relocation, string $reason): int
    {
        return $this->holds->releaseNewPlaceHold($relocation, $reason);
    }

    public function releaseOldPlaceLocksAfterMove(BookingRelocation $relocation): int
    {
        return SleepingPlaceBookingDateLock::query()
            ->where('booking_id', $relocation->original_booking_id)
            ->where('sleeping_place_id', $relocation->current_sleeping_place_id)
            ->where('status', 'active')
            ->whereDate('date', '>=', $relocation->relocation_date)
            ->update([
                'status' => 'released_for_inspection',
                'released_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function keepOldPlaceBlockedForCleaningOrInspection(BookingRelocation $relocation): void
    {
        SleepingPlaceBookingDateLock::query()
            ->where('booking_id', $relocation->original_booking_id)
            ->where('sleeping_place_id', $relocation->current_sleeping_place_id)
            ->where('status', 'released_for_inspection')
            ->update([
                'lock_type' => 'old_place_inspection_pending',
                'updated_at' => now(),
            ]);
    }

    public function keepOldPlaceBlockedForRepairOrComplaint(BookingRelocation $relocation): void
    {
        if (! in_array($relocation->reason, ['breakdown', 'maintenance_issue', 'complaint_resolution'], true)) {
            return;
        }

        SleepingPlaceBookingDateLock::query()
            ->where('booking_id', $relocation->original_booking_id)
            ->where('sleeping_place_id', $relocation->current_sleeping_place_id)
            ->where('status', 'released_for_inspection')
            ->update([
                'lock_type' => 'old_place_repair_or_complaint_pending',
                'updated_at' => now(),
            ]);
    }

    public function syncHostCalendar(BookingRelocation $relocation): void
    {
        $this->holds->convertHoldToBookingLocks($relocation);
    }
}
