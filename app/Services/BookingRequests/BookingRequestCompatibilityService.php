<?php

namespace App\Services\BookingRequests;

use App\Models\BookingRequest;
use App\Models\BookingRequestCompatibilityResult;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Support\Collection;

class BookingRequestCompatibilityService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<string, mixed>>
     */
    public function checkCompatibility(User $guest, SleepingPlace $place, array $data): array
    {
        $place->loadMissing(['room', 'property']);

        return array_values(array_filter([
            $this->checkGuestCount($place, (int) ($data['guests_count'] ?? 1)),
            ...$this->checkRoomRules($guest, $place->room, $data),
            $this->checkSmokingPolicy($guest, $place->property, $data),
            $this->checkPetPolicy($guest, $place->property, $data),
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array<string, mixed>>
     */
    public function checkRoomRules(User $guest, Room $room, array $data): array
    {
        unset($data);

        $genderPolicy = $room->gender_policy?->value ?? $room->gender_policy ?? $room->gender_type?->value ?? $room->gender_type;
        $guestGender = $guest->gender;

        if ($genderPolicy === 'female_only' && $guestGender === 'male') {
            return [$this->result('room_format', BookingRequestCompatibilityResult::STATUS_CONFLICT, 'warning')];
        }

        if ($genderPolicy === 'male_only' && $guestGender === 'female') {
            return [$this->result('room_format', BookingRequestCompatibilityResult::STATUS_CONFLICT, 'warning')];
        }

        return [$this->result('room_format', BookingRequestCompatibilityResult::STATUS_GOOD, 'info')];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public function checkSmokingPolicy(User $guest, ?Property $property, array $data): ?array
    {
        unset($data);

        if ((bool) $guest->is_smoker && $this->ruleForbids($property?->rules, 'smoking')) {
            return $this->result('smoking_policy', BookingRequestCompatibilityResult::STATUS_CONFLICT, 'warning');
        }

        return $this->result('smoking_policy', BookingRequestCompatibilityResult::STATUS_GOOD, 'info');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public function checkPetPolicy(User $guest, ?Property $property, array $data): ?array
    {
        unset($data);

        if ((bool) $guest->has_pets && $this->ruleForbids($property?->rules, 'pets')) {
            return $this->result('pet_policy', BookingRequestCompatibilityResult::STATUS_CONFLICT, 'warning');
        }

        return $this->result('pet_policy', BookingRequestCompatibilityResult::STATUS_GOOD, 'info');
    }

    /**
     * @return array<string, mixed>
     */
    public function checkGuestCount(SleepingPlace $place, int $guestsCount): array
    {
        $maxGuests = (int) ($place->max_guests_count ?: $place->max_guests ?: 1);

        return $guestsCount > $maxGuests
            ? $this->result('guest_count', BookingRequestCompatibilityResult::STATUS_BLOCKING, 'blocking', ['max' => $maxGuests])
            : $this->result('guest_count', BookingRequestCompatibilityResult::STATUS_GOOD, 'info', ['max' => $maxGuests]);
    }

    /**
     * @return Collection<int, BookingRequestCompatibilityResult>
     */
    public function createCompatibilityResults(BookingRequest $request): Collection
    {
        $request->loadMissing(['guest', 'sleepingPlace.room', 'sleepingPlace.property']);
        $request->compatibilityResults()->delete();

        $results = $this->checkCompatibility($request->guest, $request->sleepingPlace, [
            'guests_count' => $request->guests_count,
        ]);

        return collect($request->compatibilityResults()->createMany($results));
    }

    /**
     * @param  array<string, scalar|null>  $params
     * @return array<string, mixed>
     */
    private function result(string $key, string $status, string $severity, array $params = []): array
    {
        return [
            'compatibility_key' => $key,
            'status' => $status,
            'severity' => $severity,
            'message_key' => "booking_requests.compatibility.{$key}",
            'message_params_json' => $params,
        ];
    }

    private function ruleForbids(mixed $rules, string $key): bool
    {
        if (! is_array($rules)) {
            return false;
        }

        $value = $rules[$key] ?? $rules["{$key}_allowed"] ?? null;

        return $value === false || $value === 'forbidden' || $value === 'no';
    }
}
