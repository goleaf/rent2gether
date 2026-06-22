<?php

namespace App\Services\Cleaning;

use App\Models\Booking;
use App\Models\PlaceReadinessCheck;
use App\Models\SleepingPlace;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class TurnoverReadinessService
{
    /**
     * @return array{gap_minutes: int, required_minutes: int, enough: bool}
     */
    public function calculateTurnoverWindow(Booking $previousBooking, Booking $nextBooking): array
    {
        $checkoutAt = $this->dateTime($previousBooking->check_out_date, $previousBooking->check_out_time ?: '11:00');
        $checkInAt = $this->dateTime($nextBooking->check_in_date, $nextBooking->check_in_time ?: '17:00');
        $gap = max(0, $checkoutAt->diffInMinutes($checkInAt, false));
        $required = $this->getRequiredPreparationMinutes($previousBooking->sleepingPlace);

        return [
            'gap_minutes' => $gap,
            'required_minutes' => $required,
            'enough' => $gap >= $required,
        ];
    }

    public function canPrepareInTime(Booking $previousBooking, Booking $nextBooking): bool
    {
        return $this->calculateTurnoverWindow($previousBooking, $nextBooking)['enough'];
    }

    public function getRequiredPreparationMinutes(SleepingPlace $place): int
    {
        $policy = app(CleaningPolicyService::class)->resolveForContext($place->property, $place->room, $place);

        return (int) $policy->default_cleaning_duration_minutes
            + (int) $policy->default_inspection_duration_minutes
            + (int) $policy->same_day_turnover_min_gap_minutes;
    }

    public function createSameDayTurnoverTasks(Booking $previousBooking, Booking $nextBooking): Collection
    {
        $window = $this->calculateTurnoverWindow($previousBooking, $nextBooking);
        $task = app(CleaningTaskService::class)->createManual($previousBooking->host, [
            'booking_id' => $previousBooking->id,
            'property_id' => $previousBooking->property_id,
            'room_id' => $previousBooking->room_id,
            'sleeping_place_id' => $previousBooking->sleeping_place_id,
            'cleaning_type' => 'turnover_cleaning',
            'cleaning_scope' => 'sleeping_place',
            'priority' => 'same_day_turnover',
            'scheduled_date' => $previousBooking->check_out_date->toDateString(),
            'scheduled_start_at' => $this->dateTime($previousBooking->check_out_date, $previousBooking->check_out_time ?: '11:00'),
            'inspection_required' => true,
        ]);

        PlaceReadinessCheck::query()->create([
            'readiness_number' => app(PlaceReadinessNumberService::class)->generate(),
            'booking_id' => $previousBooking->id,
            'next_booking_id' => $nextBooking->id,
            'property_id' => $previousBooking->property_id,
            'room_id' => $previousBooking->room_id,
            'sleeping_place_id' => $previousBooking->sleeping_place_id,
            'host_user_id' => $previousBooking->host_user_id,
            'status' => $window['enough'] ? 'checking' : 'not_ready',
            'check_reason' => 'same_day_turnover',
            'target_check_in_at' => $this->dateTime($nextBooking->check_in_date, $nextBooking->check_in_time ?: '17:00'),
            'same_day_turnover' => true,
            'turnover_gap_minutes' => $window['gap_minutes'],
            'required_gap_minutes' => $window['required_minutes'],
            'gap_is_enough' => $window['enough'],
            'blocking_reason_key' => $window['enough'] ? null : 'same_day_turnover_risky',
        ]);

        return collect([$task->refresh()]);
    }

    public function buildTurnoverWarnings(Booking $previousBooking, Booking $nextBooking): Collection
    {
        return $this->canPrepareInTime($previousBooking, $nextBooking)
            ? collect(['same_day_turnover_ok'])
            : collect(['same_day_turnover_risky']);
    }

    private function dateTime(mixed $date, mixed $time): CarbonImmutable
    {
        $dateString = $date instanceof CarbonInterface ? $date->toDateString() : (string) $date;
        $timeString = $time instanceof CarbonInterface ? $time->format('H:i') : (string) $time;

        return CarbonImmutable::parse($dateString.' '.$timeString);
    }
}
