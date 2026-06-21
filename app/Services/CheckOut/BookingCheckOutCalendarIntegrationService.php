<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Models\SleepingPlaceCalendarDay;

class BookingCheckOutCalendarIntegrationService
{
    public function releaseOrConvertBookingLocksAfterCheckout(BookingCheckOut $checkOut): void
    {
        $this->syncAvailabilityAfterCheckout($checkOut);
    }

    public function keepPlaceBlockedForCleaning(BookingCheckOut $checkOut): void
    {
        $this->block($checkOut, 'cleaning', 'after_checkout');
    }

    public function keepPlaceBlockedForInspection(BookingCheckOut $checkOut): void
    {
        $this->block($checkOut, 'blocked', 'inspection');
    }

    public function openSleepingPlaceIfReady(BookingCheckOut $checkOut): void
    {
        if (! $this->isReadyToOpen($checkOut)) {
            if ($checkOut->cleaning_required) {
                $this->keepPlaceBlockedForCleaning($checkOut);
            } elseif ($checkOut->inspection_required) {
                $this->keepPlaceBlockedForInspection($checkOut);
            }

            return;
        }

        SleepingPlaceCalendarDay::query()->updateOrCreate(
            [
                'sleeping_place_id' => $checkOut->sleeping_place_id,
                'date' => $checkOut->check_out_date,
            ],
            [
                'status' => 'available',
                'reason' => 'checkout_ready',
                'source' => 'checkout',
                'booking_id' => null,
                'blocked_by_host' => false,
            ],
        );
    }

    public function syncAvailabilityAfterCheckout(BookingCheckOut $checkOut): void
    {
        $this->openSleepingPlaceIfReady($checkOut);
    }

    private function block(BookingCheckOut $checkOut, string $status, string $reason): void
    {
        SleepingPlaceCalendarDay::query()->updateOrCreate(
            [
                'sleeping_place_id' => $checkOut->sleeping_place_id,
                'date' => $checkOut->check_out_date,
            ],
            [
                'status' => $status,
                'reason' => $reason,
                'source' => 'checkout',
                'booking_id' => $checkOut->booking_id,
            ],
        );
    }

    private function isReadyToOpen(BookingCheckOut $checkOut): bool
    {
        if ($checkOut->status !== 'completed') {
            return false;
        }

        return ! $checkOut->cleaning_required
            && ! $checkOut->inspection_required
            && ! $checkOut->repair_required
            && ! $checkOut->has_complaint
            && ! $checkOut->has_dispute;
    }
}
