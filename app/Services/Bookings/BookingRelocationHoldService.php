<?php

namespace App\Services\Bookings;

use App\Models\BookingRelocation;
use App\Models\SleepingPlaceBookingDateLock;
use App\Services\Availability\SleepingPlaceDateLockService;
use Illuminate\Support\Collection;

class BookingRelocationHoldService
{
    public function __construct(
        private readonly SleepingPlaceDateLockService $dateLocks,
    ) {}

    /**
     * @return Collection<int, SleepingPlaceBookingDateLock>
     */
    public function createNewPlaceHold(BookingRelocation $relocation): Collection
    {
        return $this->dateLocks->createLocksForRelocation($relocation);
    }

    public function releaseNewPlaceHold(BookingRelocation $relocation, string $reason): int
    {
        return SleepingPlaceBookingDateLock::query()
            ->where('booking_relocation_id', $relocation->id)
            ->where('status', 'active')
            ->update([
                'status' => $reason === 'expired' ? 'expired' : 'released',
                'released_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * @return Collection<int, SleepingPlaceBookingDateLock>
     */
    public function convertHoldToBookingLocks(BookingRelocation $relocation): Collection
    {
        SleepingPlaceBookingDateLock::query()
            ->where('booking_relocation_id', $relocation->id)
            ->where('status', 'active')
            ->update([
                'booking_id' => $relocation->new_booking_id,
                'lock_type' => 'booked',
                'expires_at' => null,
                'updated_at' => now(),
            ]);

        return SleepingPlaceBookingDateLock::query()
            ->where('booking_relocation_id', $relocation->id)
            ->where('booking_id', $relocation->new_booking_id)
            ->orderBy('date')
            ->get();
    }

    public function expireOldRelocationHolds(): int
    {
        return SleepingPlaceBookingDateLock::query()
            ->where('lock_type', 'relocation_pending')
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update([
                'status' => 'expired',
                'released_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
