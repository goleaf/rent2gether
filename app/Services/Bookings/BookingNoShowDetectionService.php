<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingNoShow;
use Carbon\CarbonImmutable;

class BookingNoShowDetectionService
{
    public function shouldStartNoShowWatch(Booking $booking): bool
    {
        $status = $this->statusValue($booking);

        return in_array($status, $this->allowedBookingStatuses(), true)
            && ! $this->hasGuestConfirmedArrival($booking)
            && $this->isGuestLate($booking)
            && ! $this->bookingIsClosed($status);
    }

    public function isGuestLate(Booking $booking): bool
    {
        return CarbonImmutable::now()->greaterThanOrEqualTo($this->checkInAt($booking));
    }

    public function hasGuestConfirmedArrival(Booking $booking): bool
    {
        return (bool) ($booking->guest_checked_in_at || $booking->checked_in_at || $booking->guest_check_in_confirmed_at);
    }

    public function hasActiveCheckInProblem(Booking $booking): bool
    {
        return $booking->checkIn()
            ->where(function ($query): void {
                $query->where('has_problem', true)
                    ->orWhereIn('status', ['check_in_problem', 'problem_reported', 'host_unresponsive']);
            })
            ->where(function ($query): void {
                $query->whereNull('problem_status')
                    ->orWhereNotIn('problem_status', ['resolved', 'closed']);
            })
            ->exists();
    }

    public function canConfirmNoShow(BookingNoShow $noShow): bool
    {
        $noShow->loadMissing('booking');

        if ($noShow->guest_claimed_arrived || in_array($noShow->guest_response_type, ['i_arrived', 'i_have_check_in_problem', 'host_not_answering'], true)) {
            return false;
        }

        if ($noShow->host_unresponsive_case_id || $this->hasActiveCheckInProblem($noShow->booking)) {
            return false;
        }

        if ($this->hasActiveHostUnresponsiveCase($noShow->booking)) {
            return false;
        }

        if ($noShow->guest_response_type === 'accept_no_show' || $noShow->guest_warned_cancellation) {
            return true;
        }

        return $noShow->waiting_until !== null && CarbonImmutable::now()->greaterThanOrEqualTo(CarbonImmutable::instance($noShow->waiting_until));
    }

    /**
     * @return list<string>
     */
    private function allowedBookingStatuses(): array
    {
        return [
            BookingStatus::Confirmed->value,
            BookingStatus::Paid->value,
            BookingStatus::ReadyForCheckIn->value,
            BookingStatus::ReadyForCheckInCore->value,
        ];
    }

    private function bookingIsClosed(string $status): bool
    {
        return in_array($status, [
            BookingStatus::CancelledByGuestFlow->value,
            BookingStatus::CancelledByHostFlow->value,
            BookingStatus::CancelledByServiceFuture->value,
            BookingStatus::PaymentFailed->value,
            BookingStatus::NoShow->value,
            BookingStatus::HostUnresponsive->value,
            BookingStatus::GuestCheckedIn->value,
            BookingStatus::CheckedIn->value,
            BookingStatus::StayInProgress->value,
        ], true);
    }

    private function hasActiveHostUnresponsiveCase(Booking $booking): bool
    {
        return $booking->hostUnresponsiveCases()
            ->whereNotIn('status', ['resolved', 'closed', 'cancelled', 'converted_to_no_show'])
            ->exists();
    }

    private function statusValue(Booking $booking): string
    {
        return $booking->status instanceof \BackedEnum ? $booking->status->value : (string) $booking->status;
    }

    private function checkInAt(Booking $booking): CarbonImmutable
    {
        $checkIn = CarbonImmutable::parse((string) ($booking->check_in_date ?: $booking->check_in ?: now()->toDateString()))->startOfDay();
        $time = $booking->arrival_time ?: $booking->check_in_time;

        if ($time) {
            $parsed = $time instanceof \DateTimeInterface ? CarbonImmutable::instance($time) : CarbonImmutable::parse((string) $time);

            return $checkIn->setTime((int) $parsed->format('H'), (int) $parsed->format('i'));
        }

        return $checkIn;
    }
}
