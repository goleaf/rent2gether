<?php

namespace App\Services\HostListings\Creation;

use App\Models\ListingReadinessCheck;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use Illuminate\Database\Eloquent\Collection;

class ListingReadinessService
{
    /**
     * @return Collection<int, ListingReadinessCheck>
     */
    public function checkProperty(Property $property): Collection
    {
        $property->load('rooms:id,property_id', 'photos:id,property_id', 'ruleRecords:id,property_id', 'address:id,property_id,city_id');

        return new Collection([
            $this->record($property, null, null, 'property_title', filled($property->title)),
            $this->record($property, null, null, 'property_location', filled($property->city_id) || filled($property->address?->city_id)),
            $this->record($property, null, null, 'property_description', filled($property->description), required: false),
            $this->record($property, null, null, 'property_photo', $property->photos->isNotEmpty()),
            $this->record($property, null, null, 'room_exists', $property->rooms->isNotEmpty()),
            $this->record($property, null, null, 'house_rules', $property->ruleRecords->isNotEmpty()),
        ]);
    }

    /**
     * @return Collection<int, ListingReadinessCheck>
     */
    public function checkRoom(Room $room): Collection
    {
        $room->load('property:id,user_id,host_user_id', 'sleepingPlaces:id,room_id', 'photos:id,room_id');

        return new Collection([
            $this->record($room->property, $room, null, 'room_title', filled($room->title)),
            $this->record($room->property, $room, null, 'room_photo', $room->photos->isNotEmpty()),
            $this->record($room->property, $room, null, 'sleeping_place_exists', $room->sleepingPlaces->isNotEmpty()),
            $this->record($room->property, $room, null, 'room_rules', filled($room->rules_text) || filled($room->room_rules_text)),
        ]);
    }

    /**
     * @return Collection<int, ListingReadinessCheck>
     */
    public function checkSleepingPlace(SleepingPlace $place): Collection
    {
        $place->load(
            'property:id,user_id,host_user_id,title,description,city_id',
            'property.photos:id,property_id',
            'property.ruleRecords:id,property_id',
            'property.accessDetails:id,property_id,check_in_instruction,key_pickup_instruction',
            'room:id,property_id,title,rules_text,room_rules_text',
            'room.photos:id,room_id',
            'photos:id,sleeping_place_id',
        );

        return new Collection([
            ...$this->checkProperty($place->property)->all(),
            ...$this->checkRoom($place->room)->all(),
            $this->record($place->property, $place->room, $place, 'sleeping_place_type', filled($place->place_type) || filled($place->sleeping_place_type)),
            $this->record($place->property, $place->room, $place, 'sleeping_place_price', (float) ($place->base_price ?? $place->base_price_per_night ?? 0) > 0),
            $this->record($place->property, $place->room, $place, 'sleeping_place_photo', $place->photos->isNotEmpty()),
            $this->record($place->property, $place->room, $place, 'access_instruction', filled($place->property->accessDetails?->check_in_instruction)),
            $this->record($place->property, $place->room, $place, 'check_in_time', filled($place->property->accessDetails?->key_pickup_instruction), required: false),
            $this->record($place->property, $place->room, $place, 'cancellation_policy', filled($place->cancellation_policy), required: false),
            $this->record($place->property, $place->room, $place, 'deposit_policy', $place->deposit_amount !== null, required: false),
        ]);
    }

    /**
     * @return Collection<int, ListingReadinessCheck>
     */
    public function getMissingRequiredChecks(SleepingPlace $place): Collection
    {
        return $this->checkSleepingPlace($place)
            ->filter(fn (ListingReadinessCheck $check): bool => $check->required && $check->status === 'missing')
            ->values();
    }

    /**
     * @return Collection<int, ListingReadinessCheck>
     */
    public function getWarnings(SleepingPlace $place): Collection
    {
        return $this->checkSleepingPlace($place)
            ->filter(fn (ListingReadinessCheck $check): bool => $check->status === 'warning')
            ->values();
    }

    public function getCompletionPercent(SleepingPlace $place): int
    {
        $checks = $this->checkSleepingPlace($place);

        if ($checks->isEmpty()) {
            return 0;
        }

        return (int) round(($checks->where('status', 'completed')->count() / $checks->count()) * 100);
    }

    private function record(Property $property, ?Room $room, ?SleepingPlace $place, string $key, bool $complete, bool $required = true): ListingReadinessCheck
    {
        return ListingReadinessCheck::query()->updateOrCreate(
            [
                'property_id' => $property->id,
                'room_id' => $room?->id,
                'sleeping_place_id' => $place?->id,
                'check_key' => $key,
            ],
            [
                'user_id' => $property->host_user_id ?? $property->user_id,
                'status' => $complete ? 'completed' : 'missing',
                'required' => $required,
                'message_key' => 'listing_readiness.messages.'.$key,
            ],
        );
    }
}
