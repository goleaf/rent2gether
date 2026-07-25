<?php

namespace App\Services\Availability;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\SleepingPlace;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class SleepingPlaceTurnoverService
{
    /**
     * @param  array<string, mixed>  $context
     * @return array{allowed:bool,request_only:bool,reason_key:?string,message_key:?string}
     */
    public function validateTurnover(SleepingPlace $place, CarbonInterface $checkIn, CarbonInterface $checkOut, array $context = []): array
    {
        $previousBooking = $this->previousBookingEndingAt($place, $checkIn);

        if (! $previousBooking instanceof Booking) {
            return [
                'allowed' => true,
                'request_only' => false,
                'reason_key' => null,
                'message_key' => null,
            ];
        }

        if (! $this->canSameDayTurnover($place, $previousBooking, Carbon::instance($checkIn), $context['check_in_time'] ?? null)) {
            return [
                'allowed' => false,
                'request_only' => false,
                'reason_key' => 'same_day_turnover_not_allowed',
                'message_key' => 'availability.messages.same_day_turnover_not_allowed',
            ];
        }

        if ($this->requiresCleaningBeforeNextGuest($place) && $this->requiresCleaningDone($place) && ! ($context['cleaning_done'] ?? false)) {
            return [
                'allowed' => false,
                'request_only' => true,
                'reason_key' => 'cleaning_gap_required',
                'message_key' => 'availability.messages.cleaning_gap_required',
            ];
        }

        if ($this->requiresInspectionBeforeNextGuest($place) && $this->requiresInspectionDone($place) && ! ($context['inspection_done'] ?? false)) {
            return [
                'allowed' => false,
                'request_only' => true,
                'reason_key' => 'inspection_required',
                'message_key' => 'availability.messages.inspection_required',
            ];
        }

        return [
            'allowed' => true,
            'request_only' => false,
            'reason_key' => null,
            'message_key' => null,
        ];
    }

    public function calculateRequiredGap(SleepingPlace $place): int
    {
        $place->loadMissing('turnoverRules');
        $rule = $place->turnoverRules;

        return max(
            (int) ($rule?->min_gap_minutes ?? 0),
            $rule?->cleaning_required_between_guests ? (int) $rule->cleaning_gap_minutes : 0,
            $rule?->inspection_required_after_checkout ? (int) $rule->inspection_gap_minutes : 0,
        );
    }

    public function canSameDayTurnover(SleepingPlace $place, Booking $previousBooking, CarbonInterface $newCheckInDate, ?string $newCheckInTime = null): bool
    {
        $previousCheckoutDate = $this->date($previousBooking->check_out_date ?? $previousBooking->check_out);
        $newCheckInDate = $this->date($newCheckInDate);

        if ($newCheckInDate->lessThan($previousCheckoutDate)) {
            return false;
        }

        if ($newCheckInDate->greaterThan($previousCheckoutDate)) {
            return true;
        }

        $place->loadMissing(['turnoverRules', 'calendarSettings']);
        $rule = $place->turnoverRules;
        $settings = $place->calendarSettings;

        $sameDayAllowed = (bool) ($rule?->same_day_turnover_allowed ?? $settings?->same_day_turnover_allowed ?? true);

        if (! $sameDayAllowed) {
            return false;
        }

        if (! (bool) ($rule?->morning_checkout_evening_checkin_allowed ?? true)) {
            return false;
        }

        $previousCheckoutTime = $this->timeString(
            $previousBooking->check_out_time
                ?? $rule?->latest_previous_check_out_time
                ?? $settings?->default_check_out_time
                ?? $settings?->check_out_time_until
                ?? '11:00'
        );
        $newCheckInTime = $this->timeString(
            $newCheckInTime
                ?? $rule?->earliest_new_check_in_time
                ?? $settings?->earliest_check_in_time
                ?? $settings?->default_check_in_time
                ?? $settings?->check_in_time_from
                ?? '15:00'
        );

        $readyAt = CarbonImmutable::parse($previousCheckoutDate->toDateString().' '.$previousCheckoutTime)
            ->addMinutes($this->calculateRequiredGap($place));
        $arrivalAt = CarbonImmutable::parse($newCheckInDate->toDateString().' '.$newCheckInTime);

        return $readyAt->lessThanOrEqualTo($arrivalAt);
    }

    public function requiresCleaningBeforeNextGuest(SleepingPlace $place): bool
    {
        $place->loadMissing('turnoverRules');

        return (bool) ($place->turnoverRules?->cleaning_required_between_guests ?? false);
    }

    public function requiresInspectionBeforeNextGuest(SleepingPlace $place): bool
    {
        $place->loadMissing('turnoverRules');

        return (bool) ($place->turnoverRules?->inspection_required_after_checkout ?? false);
    }

    public function getEarliestAllowedNextCheckIn(SleepingPlace $place, Booking $previousBooking): ?Carbon
    {
        if (! $previousBooking->check_out_date && ! $previousBooking->check_out) {
            return null;
        }

        $place->loadMissing(['turnoverRules', 'calendarSettings']);
        $checkoutDate = $this->date($previousBooking->check_out_date ?? $previousBooking->check_out);
        $checkoutTime = $this->timeString(
            $previousBooking->check_out_time
                ?? $place->turnoverRules?->latest_previous_check_out_time
                ?? $place->calendarSettings?->default_check_out_time
                ?? $place->calendarSettings?->check_out_time_until
                ?? '11:00'
        );

        return Carbon::parse($checkoutDate->toDateString().' '.$checkoutTime)
            ->addMinutes($this->calculateRequiredGap($place));
    }

    private function previousBookingEndingAt(SleepingPlace $place, CarbonInterface $checkIn): ?Booking
    {
        return $place->bookings()
            ->whereDate('check_out_date', $this->date($checkIn)->toDateString())
            ->whereNotIn('status', $this->nonBlockingBookingStatuses())
            ->latest('check_out_date')
            ->first();
    }

    private function requiresCleaningDone(SleepingPlace $place): bool
    {
        $place->loadMissing('turnoverRules');

        return (bool) ($place->turnoverRules?->same_day_turnover_requires_cleaning_done ?? true);
    }

    private function requiresInspectionDone(SleepingPlace $place): bool
    {
        $place->loadMissing('turnoverRules');

        return (bool) ($place->turnoverRules?->same_day_turnover_requires_inspection_done ?? false);
    }

    /**
     * @return list<string>
     */
    private function nonBlockingBookingStatuses(): array
    {
        return [
            BookingStatus::Draft->value,
            BookingStatus::DeclinedByHost->value,
            BookingStatus::CancelledByGuestFlow->value,
            BookingStatus::CancelledByHostFlow->value,
            BookingStatus::Expired->value,
            BookingStatus::CancelledByGuest->value,
            BookingStatus::CancelledByHost->value,
            BookingStatus::CancelledBySystem->value,
            BookingStatus::CancelledByService->value,
            BookingStatus::NoShow->value,
            BookingStatus::HostNoShow->value,
            BookingStatus::CheckedOut->value,
            BookingStatus::Completed->value,
            BookingStatus::AwaitingReview->value,
            BookingStatus::Closed->value,
        ];
    }

    private function timeString(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('H:i');
        }

        return substr((string) ($value ?: '00:00'), 0, 5);
    }

    private function date(CarbonInterface|string $date): CarbonImmutable
    {
        return $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)->startOfDay()
            : CarbonImmutable::parse($date)->startOfDay();
    }
}
