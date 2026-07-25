<?php

namespace App\Services\Bookings;

use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Availability\AvailabilityService;
use BackedEnum;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class BookingDateValidationService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly StayLengthCalculatorService $stayLength,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return Collection<int, array{validation_key:string,severity:string,message_key:string,blocking:bool,visible_to_guest:bool,visible_to_host:bool,message_params_json:array<string, mixed>}>
     */
    public function validateDates(User $guest, SleepingPlace $place, array $data): Collection
    {
        $results = collect($this->stayLength->validateBasicDateOrder($data));

        if ($results->where('blocking', true)->isNotEmpty()) {
            return $results->values();
        }

        $checkIn = $this->date($data['check_in_date']);
        $checkOut = $this->date($data['check_out_date']);
        $nights = $this->stayLength->calculateNights($checkIn, $checkOut);

        $place->loadMissing([
            'room:id,property_id,status,gender_policy,gender_type,min_guest_age,max_guest_age',
            'property:id,status',
            'calendarSettings',
        ]);

        if ($this->statusValue($place->status) !== SleepingPlaceStatus::Active->value
            || $this->statusValue($place->room?->status) !== RoomStatus::Active->value
            || $this->statusValue($place->property?->status) !== PropertyStatus::Active->value) {
            $results->push($this->result('sleeping_place_unavailable'));
        }

        $results = $results
            ->merge($this->validateMinMaxNights($place, $nights))
            ->merge($this->validateCheckInWeekday($place, $checkIn))
            ->merge($this->validateCheckOutWeekday($place, $checkOut))
            ->merge($this->availabilityResults($place, $checkIn, $checkOut))
            ->merge($this->validateCleaningGap($place, $checkIn, $checkOut))
            ->merge($this->validateInspectionGap($place, $checkIn, $checkOut))
            ->merge($this->validateGuestEligibility($guest, $place, $data))
            ->merge($this->validateGuestCount($place, (int) ($data['guests_count'] ?? 1)));

        if ($place->room instanceof Room) {
            $results = $results->merge($this->validateRoomPolicy($guest, $place->room, $data));
        }

        if (($place->calendarSettings?->requires_host_confirmation ?? false) || ($place->requires_host_approval ?? false)) {
            $results->push($this->result('host_confirmation_required', 'info', false));
        }

        return $results->unique('validation_key')->values();
    }

    /**
     * @return Collection<int, array{validation_key:string,severity:string,message_key:string,blocking:bool,visible_to_guest:bool,visible_to_host:bool,message_params_json:array<string, mixed>}>
     */
    public function validateMinMaxNights(SleepingPlace $place, int $nights): Collection
    {
        $place->loadMissing('calendarSettings');
        $min = max(1, (int) ($place->calendarSettings?->min_nights ?: $place->min_nights ?: 1));
        $max = $place->calendarSettings?->max_nights ?: $place->max_nights;
        $results = collect();

        if ($nights < $min) {
            $results->push($this->result('below_min_nights', 'blocking', true, ['count' => $min]));
        }

        if ($max !== null && $nights > (int) $max) {
            $results->push($this->result('above_max_nights', 'blocking', true, ['count' => (int) $max]));
        }

        return $results;
    }

    /**
     * @return Collection<int, array{validation_key:string,severity:string,message_key:string,blocking:bool,visible_to_guest:bool,visible_to_host:bool,message_params_json:array<string, mixed>}>
     */
    public function validateCheckInWeekday(SleepingPlace $place, CarbonInterface $date): Collection
    {
        $place->loadMissing('calendarSettings');
        $allowed = $place->calendarSettings?->check_in_weekdays_json;

        if (is_array($allowed) && $allowed !== [] && ! in_array($date->dayOfWeek, array_map('intval', $allowed), true)) {
            return collect([$this->result('check_in_weekday_not_allowed')]);
        }

        return collect();
    }

    /**
     * @return Collection<int, array{validation_key:string,severity:string,message_key:string,blocking:bool,visible_to_guest:bool,visible_to_host:bool,message_params_json:array<string, mixed>}>
     */
    public function validateCheckOutWeekday(SleepingPlace $place, CarbonInterface $date): Collection
    {
        $place->loadMissing('calendarSettings');
        $allowed = $place->calendarSettings?->check_out_weekdays_json;

        if (is_array($allowed) && $allowed !== [] && ! in_array($date->dayOfWeek, array_map('intval', $allowed), true)) {
            return collect([$this->result('check_out_weekday_not_allowed')]);
        }

        return collect();
    }

    /**
     * @return Collection<int, array{validation_key:string,severity:string,message_key:string,blocking:bool,visible_to_guest:bool,visible_to_host:bool,message_params_json:array<string, mixed>}>
     */
    public function validateCleaningGap(SleepingPlace $place, CarbonInterface $checkIn, CarbonInterface $checkOut): Collection
    {
        $reasons = $this->availability->getBlockingReasons($place, $checkIn, $checkOut);

        return $reasons->contains('cleaning_gap_required')
            ? collect([$this->result('cleaning_gap_required')])
            : collect();
    }

    /**
     * @return Collection<int, array{validation_key:string,severity:string,message_key:string,blocking:bool,visible_to_guest:bool,visible_to_host:bool,message_params_json:array<string, mixed>}>
     */
    public function validateInspectionGap(SleepingPlace $place, CarbonInterface $checkIn, CarbonInterface $checkOut): Collection
    {
        $reasons = $this->availability->getBlockingReasons($place, $checkIn, $checkOut);

        return $reasons->contains('inspection_required')
            ? collect([$this->result('inspection_gap_required')])
            : collect();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return Collection<int, array{validation_key:string,severity:string,message_key:string,blocking:bool,visible_to_guest:bool,visible_to_host:bool,message_params_json:array<string, mixed>}>
     */
    public function validateGuestEligibility(User $guest, SleepingPlace $place, array $data): Collection
    {
        $results = collect();
        $requiresVerification = (bool) ($data['requires_identity_verification'] ?? false);

        if ($requiresVerification && ! (bool) ($guest->identity_verified ?? false)) {
            $results->push($this->result('guest_verification_required'));
        }

        $birthDate = $guest->date_of_birth ?? null;

        if ($birthDate) {
            $age = CarbonImmutable::parse($birthDate)->age;
            $minAge = $place->min_guest_age ?: $place->room?->min_guest_age;
            $maxAge = $place->max_guest_age ?: $place->room?->max_guest_age;

            if (($minAge && $age < (int) $minAge) || ($maxAge && $age > (int) $maxAge)) {
                $results->push($this->result('guest_age_not_allowed'));
            }
        }

        return $results;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return Collection<int, array{validation_key:string,severity:string,message_key:string,blocking:bool,visible_to_guest:bool,visible_to_host:bool,message_params_json:array<string, mixed>}>
     */
    public function validateRoomPolicy(User $guest, Room $room, array $data): Collection
    {
        $policy = $this->statusValue($room->gender_policy ?: $room->gender_type ?: 'mixed');
        $gender = $this->statusValue($data['guest_gender'] ?? $guest->gender ?? '');

        if (($policy === 'female_only' || $policy === 'female') && $gender !== '' && $gender !== 'female') {
            return collect([$this->result('room_gender_policy_mismatch')]);
        }

        if (($policy === 'male_only' || $policy === 'male') && $gender !== '' && $gender !== 'male') {
            return collect([$this->result('room_gender_policy_mismatch')]);
        }

        return collect();
    }

    /**
     * @return Collection<int, array{validation_key:string,severity:string,message_key:string,blocking:bool,visible_to_guest:bool,visible_to_host:bool,message_params_json:array<string, mixed>}>
     */
    public function validateGuestCount(SleepingPlace $place, int $guestsCount): Collection
    {
        $max = max(1, (int) ($place->max_guests_count ?: $place->max_guests ?: 1));

        return $guestsCount > $max
            ? collect([$this->result('guests_count_too_high', 'blocking', true, ['count' => $max])])
            : collect();
    }

    /**
     * @return Collection<int, array{validation_key:string,severity:string,message_key:string,blocking:bool,visible_to_guest:bool,visible_to_host:bool,message_params_json:array<string, mixed>}>
     */
    private function availabilityResults(SleepingPlace $place, CarbonInterface $checkIn, CarbonInterface $checkOut): Collection
    {
        return $this->availability
            ->getBlockingReasons($place, $checkIn, $checkOut)
            ->map(fn (string $reason): array => match ($reason) {
                'range_overlaps_existing_booking' => $this->result('date_locked_by_another_booking'),
                'repair' => $this->result('sleeping_place_repair'),
                'broken' => $this->result('sleeping_place_repair'),
                'unavailable_complaint' => $this->result('complaint_block'),
                'complaint_blocked' => $this->result('complaint_block'),
                'closed_by_host' => $this->result('sleeping_place_unavailable'),
                'closed_by_service' => $this->result('sleeping_place_unavailable'),
                'hierarchy_unavailable' => $this->result('sleeping_place_unavailable'),
                'hidden' => $this->result('sleeping_place_unavailable'),
                'request_only' => $this->result('request_only', 'warning', false),
                default => $this->result('sleeping_place_unavailable'),
            });
    }

    /**
     * @return array{validation_key:string,severity:string,message_key:string,blocking:bool,visible_to_guest:bool,visible_to_host:bool,message_params_json:array<string, mixed>}
     */
    private function result(string $key, string $severity = 'blocking', bool $blocking = true, array $params = []): array
    {
        return [
            'validation_key' => $key,
            'severity' => $severity,
            'message_key' => 'booking_dates.validation.'.$key,
            'message_params_json' => $params,
            'blocking' => $blocking,
            'visible_to_guest' => true,
            'visible_to_host' => false,
        ];
    }

    private function date(mixed $date): CarbonImmutable
    {
        return $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)->startOfDay()
            : CarbonImmutable::parse($date)->startOfDay();
    }

    private function statusValue(mixed $status): string
    {
        return $status instanceof BackedEnum ? (string) $status->value : (string) $status;
    }
}
