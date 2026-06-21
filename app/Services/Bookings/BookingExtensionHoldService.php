<?php

namespace App\Services\Bookings;

use App\Models\BookingExtension;
use App\Models\SleepingPlaceBookingDateLock;
use App\Services\Availability\SleepingPlaceDateLockService;
use Illuminate\Support\Collection;

class BookingExtensionHoldService
{
    public function __construct(
        private readonly SleepingPlaceDateLockService $dateLocks,
    ) {}

    /**
     * @return Collection<int, SleepingPlaceBookingDateLock>
     */
    public function createTemporaryHold(BookingExtension $extension): Collection
    {
        return $this->dateLocks->createLocksForExtension($extension);
    }

    public function releaseHold(BookingExtension $extension, string $reason): int
    {
        return SleepingPlaceBookingDateLock::query()
            ->where('booking_extension_id', $extension->id)
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
    public function convertHoldToBookingLocks(BookingExtension $extension): Collection
    {
        SleepingPlaceBookingDateLock::query()
            ->where('booking_extension_id', $extension->id)
            ->where('status', 'active')
            ->update([
                'booking_id' => $extension->booking_id,
                'lock_type' => 'booked',
                'expires_at' => null,
                'updated_at' => now(),
            ]);

        return SleepingPlaceBookingDateLock::query()
            ->where('booking_extension_id', $extension->id)
            ->where('booking_id', $extension->booking_id)
            ->orderBy('date')
            ->get();
    }

    public function expireOldExtensionHolds(): int
    {
        return SleepingPlaceBookingDateLock::query()
            ->where('lock_type', 'extension_pending')
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
