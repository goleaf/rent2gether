<?php

namespace App\Services\CheckIn;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckInProblem;
use App\Services\Bookings\BookingStatusService;

class BookingCheckInHostUnresponsiveIntegrationService
{
    public function createCaseFromCheckInProblem(BookingCheckInProblem $problem): mixed
    {
        $problem->forceFill([
            'source_created_host_unresponsive_case_id' => $problem->id,
            'status' => 'host_notified',
        ])->save();

        $this->markBookingHostUnresponsive($problem->checkIn()->firstOrFail());

        return $problem->id;
    }

    public function markBookingHostUnresponsive(BookingCheckIn $checkIn): Booking
    {
        $checkIn = app(BookingCheckInStatusService::class)->transition($checkIn, 'host_unresponsive', null, [
            'reason_key' => 'check_in.events.host_unresponsive',
        ]);
        $booking = $checkIn->booking()->firstOrFail();
        $statuses = app(BookingStatusService::class);

        if ($statuses->canTransition($booking, BookingStatus::HostUnresponsive->value)) {
            return $statuses->transition($booking, BookingStatus::HostUnresponsive->value, null, [
                'reason_key' => 'check_in.events.host_unresponsive',
                'event_key' => 'host_unresponsive',
                'event_type' => 'guest_action',
            ]);
        }

        if ($booking->status instanceof \BackedEnum && $booking->status->value === BookingStatus::Confirmed->value
            && $statuses->canTransition($booking, BookingStatus::ReadyForCheckInCore->value)) {
            $booking = $statuses->transition($booking, BookingStatus::ReadyForCheckInCore->value, null, [
                'reason_key' => 'check_in.events.ready_for_check_in',
                'event_key' => 'ready_for_check_in',
                'event_type' => 'system',
            ]);

            if ($statuses->canTransition($booking, BookingStatus::HostUnresponsive->value)) {
                return $statuses->transition($booking, BookingStatus::HostUnresponsive->value, null, [
                    'reason_key' => 'check_in.events.host_unresponsive',
                    'event_key' => 'host_unresponsive',
                    'event_type' => 'guest_action',
                ]);
            }
        }

        return $booking->refresh();
    }
}
