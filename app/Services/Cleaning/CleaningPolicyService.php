<?php

namespace App\Services\Cleaning;

use App\Models\CleaningPolicy;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class CleaningPolicyService
{
    public function getForSleepingPlace(SleepingPlace $place): ?CleaningPolicy
    {
        return CleaningPolicy::query()
            ->where('sleeping_place_id', $place->id)
            ->where('active', true)
            ->latest('id')
            ->first();
    }

    public function getForRoom(Room $room): ?CleaningPolicy
    {
        return CleaningPolicy::query()
            ->where('room_id', $room->id)
            ->whereNull('sleeping_place_id')
            ->where('active', true)
            ->latest('id')
            ->first();
    }

    public function getForProperty(Property $property): ?CleaningPolicy
    {
        return CleaningPolicy::query()
            ->where('property_id', $property->id)
            ->whereNull('room_id')
            ->whereNull('sleeping_place_id')
            ->where('active', true)
            ->latest('id')
            ->first();
    }

    public function resolveForContext(?Property $property, ?Room $room, ?SleepingPlace $place): CleaningPolicy
    {
        if ($place) {
            $policy = $this->getForSleepingPlace($place);

            if ($policy) {
                return $policy;
            }
        }

        if ($room) {
            $policy = $this->getForRoom($room);

            if ($policy) {
                return $policy;
            }
        }

        if ($property) {
            $policy = $this->getForProperty($property);

            if ($policy) {
                return $policy;
            }
        }

        return new CleaningPolicy([
            'property_id' => $property?->id ?? $place?->property_id ?? $room?->property_id,
            'room_id' => $room?->id ?? $place?->room_id,
            'sleeping_place_id' => $place?->id,
        ]);
    }

    public function createDefaultForSleepingPlace(SleepingPlace $place): CleaningPolicy
    {
        return CleaningPolicy::query()->firstOrCreate(
            [
                'sleeping_place_id' => $place->id,
                'active' => true,
            ],
            [
                'property_id' => $place->property_id,
                'room_id' => $place->room_id,
            ],
        );
    }

    public function updatePolicy(User $host, array $context, array $data): CleaningPolicy
    {
        $property = isset($context['property_id']) ? Property::query()->findOrFail($context['property_id']) : null;
        $room = isset($context['room_id']) ? Room::query()->findOrFail($context['room_id']) : null;
        $place = isset($context['sleeping_place_id']) ? SleepingPlace::query()->findOrFail($context['sleeping_place_id']) : null;
        $property ??= $place?->property ?? $room?->property;

        if (! $property || (int) $property->host_user_id !== (int) $host->id) {
            throw new AuthorizationException;
        }

        $policy = $this->resolveForContext($property, $room, $place);
        $policy->fill([
            ...$context,
            ...$data,
            'property_id' => $property->id,
            'room_id' => $room?->id ?? $place?->room_id,
            'sleeping_place_id' => $place?->id,
        ])->save();

        return $policy->refresh();
    }
}
