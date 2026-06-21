<?php

namespace App\Services\HostListings\Creation;

use App\Models\HostListingSuggestion;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;

class ListingSuggestionService
{
    /**
     * @return Collection<int, HostListingSuggestion>
     */
    public function generateForProperty(Property $property): Collection
    {
        $property->loadMissing('accessDetails', 'photos', 'ruleRecords', 'translations');
        $suggestions = new Collection;

        if ($property->photos->isEmpty()) {
            $suggestions->push($this->record($property, null, null, 'add_property_photo', 'warning'));
        }

        if ($property->ruleRecords->isEmpty()) {
            $suggestions->push($this->record($property, null, null, 'specify_living_rules', 'warning', 'edit_rules'));
        }

        if (! $this->hasKitchenRules($property)) {
            $suggestions->push($this->record($property, null, null, 'missing_kitchen_rules', 'warning', 'edit_rules'));
        }

        if (! $this->hasBathroomRules($property)) {
            $suggestions->push($this->record($property, null, null, 'missing_bathroom_rules', 'warning', 'edit_rules'));
        }

        if (! $this->hasKeyPickupMethod($property)) {
            $suggestions->push($this->record($property, null, null, 'missing_key_pickup_method', 'warning', 'edit_access'));
        }

        if (! $this->hasEmergencyContact($property)) {
            $suggestions->push($this->record($property, null, null, 'missing_emergency_contact', 'warning', 'edit_safety'));
        }

        return $suggestions;
    }

    /**
     * @return Collection<int, HostListingSuggestion>
     */
    public function generateForRoom(Room $room): Collection
    {
        $room->loadMissing('property', 'photos');
        $suggestions = new Collection;

        if ($room->photos->isEmpty()) {
            $suggestions->push($this->record($room->property, $room, null, 'add_room_photo', 'warning'));
        }

        if (blank($room->rules_text) && blank($room->room_rules_text)) {
            $suggestions->push($this->record($room->property, $room, null, 'missing_room_rules', 'warning', 'edit_room_rules'));
        }

        if ($room->current_guests_count === null && $room->current_residents_count === null && $room->occupied_sleeping_places_count === null) {
            $suggestions->push($this->record($room->property, $room, null, 'specify_current_people_in_room', 'info', 'edit_room'));
        }

        return $suggestions;
    }

    /**
     * @return Collection<int, HostListingSuggestion>
     */
    public function generateForSleepingPlace(SleepingPlace $place): Collection
    {
        $place->loadMissing(
            'calendarSettings',
            'property.accessDetails',
            'property.host.hostProfile',
            'property.ruleRecords',
            'property.translations',
            'room',
            'photos',
            'storageDetails',
        );
        $suggestions = new Collection;

        if ($place->photos->isEmpty()) {
            $suggestions->push($this->record($place->property, $place->room, $place, 'add_sleeping_place_photo', 'warning', 'add_photo'));
        }

        if (! $place->storageDetails?->has_locker && ! $place->has_locker) {
            $suggestions->push($this->record($place->property, $place->room, $place, 'specify_personal_locker', 'info', 'edit_storage'));
        }

        if ($place->cleaning_fee === null) {
            $suggestions->push($this->record($place->property, $place->room, $place, 'missing_cleaning_fee', 'info', 'edit_pricing'));
        }

        if ($place->deposit_amount === null) {
            $suggestions->push($this->record($place->property, $place->room, $place, 'missing_deposit_policy', 'warning', 'edit_pricing'));
        }

        if (! $this->hasCancellationPolicy($place)) {
            $suggestions->push($this->record($place->property, $place->room, $place, 'missing_cancellation_policy', 'warning', 'edit_pricing'));
        }

        if (! $this->hasCheckInTime($place)) {
            $suggestions->push($this->record($place->property, $place->room, $place, 'missing_check_in_time', 'warning', 'edit_access'));
        }

        if (! $this->hasCheckOutTime($place)) {
            $suggestions->push($this->record($place->property, $place->room, $place, 'missing_check_out_time', 'warning', 'edit_access'));
        }

        if (! $this->hasKeyPickupMethod($place->property)) {
            $suggestions->push($this->record($place->property, $place->room, $place, 'missing_key_pickup_method', 'warning', 'edit_access'));
        }

        if (! $this->hasKitchenRules($place->property)) {
            $suggestions->push($this->record($place->property, $place->room, $place, 'missing_kitchen_rules', 'warning', 'edit_rules'));
        }

        if (! $this->hasBathroomRules($place->property)) {
            $suggestions->push($this->record($place->property, $place->room, $place, 'missing_bathroom_rules', 'warning', 'edit_rules'));
        }

        if (! $this->hasEmergencyContact($place->property)) {
            $suggestions->push($this->record($place->property, $place->room, $place, 'missing_emergency_contact', 'warning', 'edit_safety'));
        }

        return $suggestions;
    }

    /**
     * @throws AuthorizationException
     */
    public function dismiss(User $host, HostListingSuggestion $suggestion): HostListingSuggestion
    {
        if ((int) $suggestion->user_id !== (int) $host->id) {
            throw new AuthorizationException(__('domain.errors.not_property_owner'));
        }

        $suggestion->forceFill(['status' => 'dismissed'])->save();

        return $suggestion->refresh();
    }

    public function markCompleted(HostListingSuggestion $suggestion): HostListingSuggestion
    {
        $suggestion->forceFill(['status' => 'completed'])->save();

        return $suggestion->refresh();
    }

    private function record(Property $property, ?Room $room, ?SleepingPlace $place, string $key, string $severity, ?string $action = null): HostListingSuggestion
    {
        return HostListingSuggestion::query()->firstOrCreate(
            [
                'user_id' => $property->host_user_id ?? $property->user_id,
                'property_id' => $property->id,
                'room_id' => $room?->id,
                'sleeping_place_id' => $place?->id,
                'suggestion_key' => $key,
            ],
            [
                'severity' => $severity,
                'message_key' => 'listing_readiness.suggestions.'.$key,
                'action_key' => $action,
                'status' => 'active',
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

    private function hasKeyPickupMethod(Property $property): bool
    {
        return filled($property->accessDetails?->key_pickup_method)
            || filled($property->accessDetails?->key_pickup_instruction)
            || filled($property->access_instructions);
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
