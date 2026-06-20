<?php

namespace App\Livewire\Account\Concerns;

use App\Enums\RoomType;
use App\Enums\SleepingPlaceType;
use App\Models\City;
use App\Support\Geo\GeoNameNormalizer;
use Illuminate\Validation\Rule;

trait ManagesGuestPreferenceForm
{
    public ?string $preferredBudgetMin = null;

    public ?string $preferredBudgetMax = null;

    public string $preferredCurrency = 'EUR';

    public string $preferredCity = '';

    public string $preferredRoomType = '';

    public string $preferredSleepingPlaceType = '';

    public bool $wantsWifi = true;

    public bool $wantsKitchen = true;

    public bool $wantsWashingMachine = false;

    public bool $wantsLocker = false;

    public bool $wantsLowerBunk = false;

    public bool $wantsWorkspace = false;

    public bool $wantsQuietHours = false;

    public bool $avoidsSmoking = true;

    public bool $avoidsPets = false;

    public bool $avoidsMixedRoom = false;

    public bool $needsLateCheckIn = false;

    public bool $needsEarlyCheckOut = false;

    public bool $needsAccessibility = false;

    public ?string $maxPeopleInRoom = null;

    public ?string $maxWalkingDistanceToTransportMeters = null;

    public string $sleepSchedule = '';

    public string $socialLevel = '';

    public string $allergies = '';

    public string $baggageSize = '';

    protected function mountPreferenceForm(): void
    {
        $preferences = auth()->user()->guestPreference()->with('preferredCity')->firstOrCreate([], [
            'preferred_currency' => 'EUR',
        ]);

        $this->preferredBudgetMin = $preferences->preferred_budget_min !== null ? (string) $preferences->preferred_budget_min : null;
        $this->preferredBudgetMax = $preferences->preferred_budget_max !== null ? (string) $preferences->preferred_budget_max : null;
        $this->preferredCurrency = $preferences->preferred_currency ?: 'EUR';
        $this->preferredCity = $preferences->preferredCity?->name ?: '';
        $this->preferredRoomType = $preferences->preferred_room_type ?: '';
        $this->preferredSleepingPlaceType = $preferences->preferred_sleeping_place_type ?: '';
        $this->wantsWifi = (bool) $preferences->wants_wifi;
        $this->wantsKitchen = (bool) $preferences->wants_kitchen;
        $this->wantsWashingMachine = (bool) $preferences->wants_washing_machine;
        $this->wantsLocker = (bool) $preferences->wants_locker;
        $this->wantsLowerBunk = (bool) $preferences->wants_lower_bunk;
        $this->wantsWorkspace = (bool) $preferences->needs_workspace;
        $this->wantsQuietHours = (bool) $preferences->needs_quiet_hours;
        $this->avoidsSmoking = (bool) $preferences->avoids_smoking;
        $this->avoidsPets = (bool) $preferences->avoids_pets;
        $this->avoidsMixedRoom = (bool) $preferences->avoids_mixed_room;
        $this->needsLateCheckIn = (bool) $preferences->needs_late_check_in;
        $this->needsEarlyCheckOut = (bool) $preferences->needs_early_check_out;
        $this->needsAccessibility = (bool) $preferences->needs_accessibility;
        $this->maxPeopleInRoom = $preferences->max_people_in_room !== null ? (string) $preferences->max_people_in_room : null;
        $this->maxWalkingDistanceToTransportMeters = $preferences->max_walking_distance_to_transport_meters !== null ? (string) $preferences->max_walking_distance_to_transport_meters : null;
        $this->sleepSchedule = $preferences->sleep_schedule ?: '';
        $this->socialLevel = $preferences->social_level ?: '';
        $this->allergies = $preferences->allergies ?: '';
        $this->baggageSize = $preferences->baggage_size ?: '';
    }

    /** @return array<string, mixed> */
    protected function preferenceRules(): array
    {
        return [
            'preferredBudgetMin' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'preferredBudgetMax' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'preferredCurrency' => ['required', Rule::in(['EUR', 'USD'])],
            'preferredCity' => ['nullable', 'string', 'max:120'],
            'preferredRoomType' => ['nullable', Rule::in(array_map(fn (RoomType $type): string => $type->value, RoomType::cases()))],
            'preferredSleepingPlaceType' => ['nullable', Rule::in(array_map(fn (SleepingPlaceType $type): string => $type->value, SleepingPlaceType::cases()))],
            'wantsWifi' => ['boolean'],
            'wantsKitchen' => ['boolean'],
            'wantsWashingMachine' => ['boolean'],
            'wantsLocker' => ['boolean'],
            'wantsLowerBunk' => ['boolean'],
            'wantsWorkspace' => ['boolean'],
            'wantsQuietHours' => ['boolean'],
            'avoidsSmoking' => ['boolean'],
            'avoidsPets' => ['boolean'],
            'avoidsMixedRoom' => ['boolean'],
            'needsLateCheckIn' => ['boolean'],
            'needsEarlyCheckOut' => ['boolean'],
            'needsAccessibility' => ['boolean'],
            'maxPeopleInRoom' => ['nullable', 'integer', 'min:1', 'max:50'],
            'maxWalkingDistanceToTransportMeters' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'sleepSchedule' => ['nullable', Rule::in(['early_bird', 'night_owl', 'flexible', 'regular'])],
            'socialLevel' => ['nullable', Rule::in(['quiet', 'balanced', 'social'])],
            'allergies' => ['nullable', 'string', 'max:500'],
            'baggageSize' => ['nullable', Rule::in(['small', 'medium', 'large'])],
        ];
    }

    /** @param array<string, mixed> $validated */
    protected function persistPreferenceForm(array $validated): void
    {
        auth()->user()->guestPreference()->updateOrCreate([], [
            'preferred_budget_min' => $this->optionalNumber($validated['preferredBudgetMin']),
            'preferred_budget_max' => $this->optionalNumber($validated['preferredBudgetMax']),
            'preferred_currency' => $validated['preferredCurrency'],
            'preferred_city_id' => $this->cityId($validated['preferredCity'] ?: null),
            'preferred_room_type' => $validated['preferredRoomType'] ?: null,
            'preferred_sleeping_place_type' => $validated['preferredSleepingPlaceType'] ?: null,
            'wants_wifi' => $validated['wantsWifi'],
            'wants_kitchen' => $validated['wantsKitchen'],
            'wants_washing_machine' => $validated['wantsWashingMachine'],
            'wants_locker' => $validated['wantsLocker'],
            'wants_lower_bunk' => $validated['wantsLowerBunk'],
            'needs_workspace' => $validated['wantsWorkspace'],
            'needs_quiet_hours' => $validated['wantsQuietHours'],
            'avoids_smoking' => $validated['avoidsSmoking'],
            'avoids_pets' => $validated['avoidsPets'],
            'avoids_mixed_room' => $validated['avoidsMixedRoom'],
            'needs_late_check_in' => $validated['needsLateCheckIn'],
            'needs_early_check_out' => $validated['needsEarlyCheckOut'],
            'needs_accessibility' => $validated['needsAccessibility'],
            'max_people_in_room' => $this->optionalNumber($validated['maxPeopleInRoom']),
            'max_walking_distance_to_transport_meters' => $this->optionalNumber($validated['maxWalkingDistanceToTransportMeters']),
            'sleep_schedule' => $validated['sleepSchedule'] ?: null,
            'social_level' => $validated['socialLevel'] ?: null,
            'allergies' => $validated['allergies'] ?: null,
            'baggage_size' => $validated['baggageSize'] ?: null,
            'accessibility_needs_json' => $validated['needsAccessibility'] ? ['step_free_access'] : [],
        ]);
    }

    private function cityId(?string $city): ?int
    {
        if (! $city) {
            return null;
        }

        return City::query()
            ->where('name_normalized', GeoNameNormalizer::normalize($city))
            ->orWhere('ascii_name', $city)
            ->orWhere('name', $city)
            ->value('id');
    }

    private function optionalNumber(mixed $value): mixed
    {
        return $value === null || $value === '' ? null : $value;
    }
}
