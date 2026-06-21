<?php

namespace App\Services\BookingRequests;

use App\Models\Booking;
use App\Models\BookingRequest;
use App\Models\SleepingPlaceBookingDateLock;
use App\Services\Availability\SleepingPlaceDateLockService;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class BookingRequestAvailabilityHoldService
{
    public function __construct(
        private readonly SleepingPlaceDateLockService $dateLocks,
    ) {}

    public function shouldHoldDates(BookingRequest $request): bool
    {
        if ($request->request_type === BookingRequest::TYPE_PRELIMINARY_INQUIRY) {
            return false;
        }

        return (bool) $request->hold_dates || $request->request_type === BookingRequest::TYPE_SAME_DAY_URGENT;
    }

    /**
     * @return Collection<int, SleepingPlaceBookingDateLock>
     */
    public function createTemporaryHold(BookingRequest $request): Collection
    {
        if (! $this->shouldHoldDates($request)) {
            return collect();
        }

        $until = $request->hold_expires_at ?: $request->expires_at ?: now()->addDay();
        $request->forceFill([
            'hold_dates' => true,
            'hold_expires_at' => $until,
        ])->save();

        $existing = $request->dateLocks()
            ->where('status', 'active')
            ->orderBy('date')
            ->get();

        if ($existing->isNotEmpty()) {
            $request->dateLocks()
                ->where('status', 'active')
                ->update([
                    'expires_at' => $until,
                    'updated_at' => now(),
                ]);

            return $existing;
        }

        $locks = $this->dateLocks->createLocksForRequest($request);

        $request->dateLocks()
            ->where('status', 'active')
            ->update([
                'expires_at' => $until,
                'updated_at' => now(),
            ]);

        return $locks;
    }

    /**
     * @return Collection<int, SleepingPlaceBookingDateLock>
     */
    public function extendHold(BookingRequest $request, CarbonInterface $until): Collection
    {
        $request->forceFill([
            'hold_dates' => true,
            'hold_expires_at' => $until,
        ])->save();

        $updated = $request->dateLocks()
            ->where('status', 'active')
            ->update([
                'expires_at' => $until,
                'updated_at' => now(),
            ]);

        return $updated > 0
            ? $request->dateLocks()->where('status', 'active')->orderBy('date')->get()
            : $this->createTemporaryHold($request);
    }

    public function releaseHold(BookingRequest $request, ?string $reason = null): int
    {
        unset($reason);

        $released = $this->dateLocks->releaseLocksForRequest($request);

        $request->forceFill([
            'hold_dates' => false,
            'hold_expires_at' => null,
        ])->save();

        return $released;
    }

    /**
     * @return Collection<int, SleepingPlaceBookingDateLock>
     */
    public function convertHoldToBooking(BookingRequest $request, Booking $booking): Collection
    {
        $this->releaseHold($request, 'converted_to_booking');

        return $this->dateLocks->createLocksForBooking($booking, 'booked');
    }
}
