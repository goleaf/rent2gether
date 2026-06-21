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
        $property->load(
            'rooms:id,property_id',
            'photos:id,property_id',
            'ruleRecords:id,property_id,rule_key',
            'address:id,property_id,city_id',
        );

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
            'calendarSettings:id,sleeping_place_id,check_in_time_from,check_out_time_until',
            'property:id,user_id,host_user_id,title,description,city_id,access_instructions,emergency_contact_name,emergency_contact_phone',
            'property.host.hostProfile:id,user_id,default_check_in_time,default_check_out_time,default_cancellation_policy',
            'property.photos:id,property_id',
            'property.ruleRecords:id,property_id,rule_key',
            'property.translations:id,property_id,house_rules_text',
            'property.accessDetails:id,property_id,check_in_instruction,key_pickup_instruction,key_return_instruction,key_pickup_method,emergency_contact_available',
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
            $this->record($place->property, $place->room, $place, 'check_in_time', $this->hasCheckInTime($place)),
            $this->record($place->property, $place->room, $place, 'check_out_time', $this->hasCheckOutTime($place)),
            $this->record($place->property, $place->room, $place, 'cancellation_policy', $this->hasCancellationPolicy($place)),
            $this->record($place->property, $place->room, $place, 'deposit_policy', $place->deposit_amount !== null),
            $this->record($place->property, $place->room, $place, 'kitchen_rules', $this->hasKitchenRules($place->property)),
            $this->record($place->property, $place->room, $place, 'bathroom_rules', $this->hasBathroomRules($place->property)),
            $this->record($place->property, $place->room, $place, 'emergency_contact', $this->hasEmergencyContact($place->property)),
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

    private function hasCheckInTime(SleepingPlace $place): bool
    {
        return filled($place->calendarSettings?->check_in_time_from)
            || filled($place->property?->host?->hostProfile?->default_check_in_time);
    }

    private function hasCheckOutTime(SleepingPlace $place): bool
    {
        return filled($place->calendarSettings?->check_out_time_until)
            || filled($place->property?->host?->hostProfile?->default_check_out_time);
    }

    private function hasCancellationPolicy(SleepingPlace $place): bool
    {
        return filled($place->cancellation_policy)
            || filled($place->property?->host?->hostProfile?->default_cancellation_policy);
    }

    private function hasKitchenRules(Property $property): bool
    {
        return $this->hasPropertyRule($property, ['cooking', 'kitchen_at_night'])
            || $this->hasRuleText($property, 'kitchen');
    }

    private function hasBathroomRules(Property $property): bool
    {
        return $this->hasPropertyRule($property, ['bathroom_at_night'])
            || $this->hasRuleText($property, 'bathroom');
    }

    private function hasEmergencyContact(Property $property): bool
    {
        return filled($property->emergency_contact_name)
            || filled($property->emergency_contact_phone)
            || $property->accessDetails?->emergency_contact_available === true;
    }

    /**
     * @param  list<string>  $keys
     */
    private function hasPropertyRule(Property $property, array $keys): bool
    {
        return $property->ruleRecords->contains(fn ($rule): bool => in_array($rule->rule_key, $keys, true));
    }

    private function hasRuleText(Property $property, string $needle): bool
    {
        return $property->translations->contains(
            fn ($translation): bool => str_contains((string) $translation->house_rules_text, $needle),
        );
    }
}
