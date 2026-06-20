<?php

namespace App\Services\CheckOut;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\HostCleaningTask;
use App\Models\SleepingPlaceCalendarDay;
use Carbon\CarbonImmutable;

class BookingCheckOutCalendarService
{
    public function releaseAfterCheckoutIfAllowed(BookingCheckOut $checkOut): void
    {
        if ($checkOut->status !== 'completed') {
            return;
        }

        $date = CarbonImmutable::parse($checkOut->check_out_date)->addDay()->toDateString();

        if ($this->futureBookingExists($checkOut, $date)) {
            return;
        }

        $existing = SleepingPlaceCalendarDay::query()
            ->where('sleeping_place_id', $checkOut->sleeping_place_id)
            ->whereDate('date', $date)
            ->first();

        if ($existing && in_array($existing->status, ['booked', 'cleaning', 'repair', 'blocked', 'unavailable'], true)) {
            return;
        }

        SleepingPlaceCalendarDay::query()->updateOrCreate(
            [
                'sleeping_place_id' => $checkOut->sleeping_place_id,
                'date' => $date,
            ],
            [
                'status' => 'available',
                'source' => 'checkout_release',
                'booking_id' => null,
                'blocked_by_host' => false,
            ],
        );
    }

    public function blockForCleaning(BookingCheckOut $checkOut): void
    {
        HostCleaningTask::query()->firstOrCreate(
            [
                'booking_id' => $checkOut->booking_id,
                'reason' => 'after_checkout',
            ],
            [
                'user_id' => $checkOut->host_user_id,
                'property_id' => $checkOut->property_id,
                'room_id' => $checkOut->room_id,
                'sleeping_place_id' => $checkOut->sleeping_place_id,
                'status' => 'planned',
                'scheduled_date' => $checkOut->check_out_date,
                'scheduled_time' => $checkOut->planned_check_out_time,
            ],
        );
    }

    public function blockForRepair(BookingCheckOut $checkOut): void
    {
        SleepingPlaceCalendarDay::query()->updateOrCreate(
            [
                'sleeping_place_id' => $checkOut->sleeping_place_id,
                'date' => $checkOut->check_out_date,
            ],
            [
                'status' => 'repair',
                'reason' => 'repair',
                'source' => 'checkout_issue',
                'booking_id' => $checkOut->booking_id,
            ],
        );
    }

    public function keepBlockedForInspection(BookingCheckOut $checkOut): void
    {
        $existing = SleepingPlaceCalendarDay::query()
            ->where('sleeping_place_id', $checkOut->sleeping_place_id)
            ->whereDate('date', $checkOut->check_out_date)
            ->first();

        if ($existing && in_array($existing->status, ['booked', 'cleaning', 'repair', 'blocked', 'unavailable'], true)) {
            return;
        }

        SleepingPlaceCalendarDay::query()->updateOrCreate(
            [
                'sleeping_place_id' => $checkOut->sleeping_place_id,
                'date' => $checkOut->check_out_date,
            ],
            [
                'status' => 'blocked',
                'reason' => 'inspection',
                'source' => 'checkout_inspection',
                'booking_id' => $checkOut->booking_id,
            ],
        );
    }

    public function syncCalendarAfterCheckout(BookingCheckOut $checkOut): void
    {
        if ($checkOut->issueReports()->where('repair_needed', true)->whereNotIn('status', ['resolved', 'closed'])->exists()) {
            $this->blockForRepair($checkOut);

            return;
        }

        if ($checkOut->status !== 'completed') {
            $this->keepBlockedForInspection($checkOut);

            return;
        }

        $this->releaseAfterCheckoutIfAllowed($checkOut);
    }

    private function futureBookingExists(BookingCheckOut $checkOut, string $date): bool
    {
        return Booking::query()
            ->where('sleeping_place_id', $checkOut->sleeping_place_id)
            ->where('id', '!=', $checkOut->booking_id)
            ->whereDate('check_in_date', '<=', $date)
            ->whereDate('check_out_date', '>', $date)
            ->whereNotIn('status', [
                BookingStatus::CancelledByGuest->value,
                BookingStatus::CancelledByGuestFlow->value,
                BookingStatus::CancelledByHost->value,
                BookingStatus::CancelledByHostFlow->value,
                BookingStatus::CancelledBySystem->value,
                BookingStatus::DeclinedByHost->value,
                BookingStatus::Expired->value,
            ])
            ->exists();
    }
}
