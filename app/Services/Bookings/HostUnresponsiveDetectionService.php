<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingHostUnresponsiveCase;
use Carbon\CarbonImmutable;

class HostUnresponsiveDetectionService
{
    public function canReport(\App\Models\User $guest, Booking $booking): bool
    {
        return (int) $booking->guest_user_id === (int) $guest->id
            && in_array($this->statusValue($booking), $this->allowedStatuses(), true)
            && ! $this->bookingIsClosed($booking);
    }

    public function isUrgentCheckInContext(Booking $booking): bool
    {
        $checkIn = CarbonImmutable::parse((string) ($booking->check_in_date ?: $booking->check_in ?: now()->toDateString()))->startOfDay();

        return CarbonImmutable::now()->greaterThanOrEqualTo($checkIn->subHours(6));
    }

    public function hasHostRespondedRecently(Booking $booking): bool
    {
        return $booking->hostUnresponsiveCases()
            ->whereNotNull('host_last_response_at')
            ->where('host_last_response_at', '>=', now()->subMinutes(30))
            ->exists();
    }

    public function hasSufficientInstructionsAvailable(Booking $booking): bool
    {
        return (bool) $booking->check_in_instruction_available
            || $booking->checkIn()
                ->where(function ($query): void {
                    $query->whereNotNull('instructions_available_at')
                        ->orWhereNotNull('instructions_shown_at');
                })
                ->exists();
    }

    public function shouldBlockNoShow(BookingHostUnresponsiveCase $case): bool
    {
        if (in_array($case->status, ['resolved', 'closed', 'cancelled', 'converted_to_no_show'], true)) {
            return false;
        }

        $case->loadMissing('booking.hostUnresponsivePolicySnapshot');
        $snapshot = $case->booking?->hostUnresponsivePolicySnapshot;

        return $snapshot === null || (bool) $snapshot->auto_block_no_show_while_active;
    }

    public function canConfirmHostUnresponsive(BookingHostUnresponsiveCase $case): bool
    {
        if (in_array($case->status, ['resolved', 'closed', 'cancelled'], true)) {
            return false;
        }

        if ($case->host_last_response_at || $case->representative_last_response_at) {
            return false;
        }

        if ($case->guest_feels_unsafe) {
            return true;
        }

        return $case->response_deadline_at !== null
            && CarbonImmutable::now()->greaterThanOrEqualTo(CarbonImmutable::instance($case->response_deadline_at));
    }

    /**
     * @return list<string>
     */
    private function allowedStatuses(): array
    {
        return [
            BookingStatus::Confirmed->value,
            BookingStatus::Paid->value,
            BookingStatus::ReadyForCheckIn->value,
            BookingStatus::ReadyForCheckInCore->value,
            BookingStatus::GuestCheckedIn->value,
            BookingStatus::InProgress->value,
            BookingStatus::StayInProgress->value,
            BookingStatus::ActiveStay->value,
        ];
    }

    private function bookingIsClosed(Booking $booking): bool
    {
        return in_array($this->statusValue($booking), [
            BookingStatus::CancelledByGuestFlow->value,
            BookingStatus::CancelledByHostFlow->value,
            BookingStatus::CancelledByServiceFuture->value,
            BookingStatus::PaymentFailed->value,
            BookingStatus::NoShow->value,
            BookingStatus::HostUnresponsive->value,
        ], true);
    }

    private function statusValue(Booking $booking): string
    {
        return $booking->status instanceof \BackedEnum ? $booking->status->value : (string) $booking->status;
    }
}
