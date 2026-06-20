<?php

namespace App\Services\Compatibility;

use App\Data\Compatibility\CompatibilityContext;
use App\Data\Compatibility\CompatibilityReasonData;
use Illuminate\Support\Facades\App;

class CompatibilityReasonService
{
    /**
     * @return list<CompatibilityReasonData>
     */
    public function buildPositiveReasons(CompatibilityContext $context): array
    {
        $profile = $context->guestProfile;
        $room = $context->roomProfile;
        $place = $context->sleepingPlaceProfile;
        $reasons = [];

        if ($profile->needs_quiet_at_night && $this->quietRoom($room, $place)) {
            $reasons[] = $this->positive('quiet_match', 10);
        }

        if ($profile->remote_worker && $profile->needs_workspace && $room->has_workspace) {
            $reasons[] = $this->positive('workspace_match', 10);
        }

        if ($profile->needs_fast_wifi && $this->hasAny($context->propertyAmenities, ['fast_wifi', 'wifi', 'wi-fi'])) {
            $reasons[] = $this->positive('wifi_match', 8);
        }

        if ($profile->needs_locker && $place->has_locker) {
            $reasons[] = $this->positive('locker_match', 10);
        }

        if ($profile->wants_lower_bunk && $place->is_bottom_bunk) {
            $reasons[] = $this->positive('lower_bunk_match', 12);
        }

        if ($profile->smokes === false && $room->smoking_allowed === false) {
            $reasons[] = $this->positive('non_smoking_match', 8);
        }

        if ($context->range->nightsCount >= 30 && $room->long_stay_allowed) {
            $reasons[] = $this->positive('long_stay_match', 8);
        }

        if ($profile->comfortable_with_shared_room && $room->is_shared) {
            $reasons[] = $this->positive('shared_room_match', 5);
        }

        if ($profile->needs_power_socket && $place->has_power_socket) {
            $reasons[] = $this->positive('power_socket_match', 5);
        }

        if ($profile->needs_bedding && $place->has_bedding) {
            $reasons[] = $this->positive('bedding_match', 4);
        }

        if ($profile->needs_curtain && $place->has_curtain) {
            $reasons[] = $this->positive('curtain_match', 5);
        }

        if ($profile->needs_late_entry && $room->late_entry_allowed) {
            $reasons[] = $this->positive('late_entry_match', 8);
        }

        return $this->unique($reasons);
    }

    /**
     * @return list<CompatibilityReasonData>
     */
    public function buildWarningReasons(CompatibilityContext $context): array
    {
        $profile = $context->guestProfile;
        $room = $context->roomProfile;
        $place = $context->sleepingPlaceProfile;
        $reasons = [];

        if ($profile->needs_quiet_at_night && $this->noisyRoom($room, $place)) {
            $reasons[] = $this->warning('noise_conflict', 25);
        }

        if ($profile->needs_quiet_at_night && ! $room->quiet_hours_enabled) {
            $reasons[] = $this->warning('quiet_hours_missing', 10);
        }

        if (($profile->works_at_night || $profile->studies_at_night) && ! $room->can_work_at_night) {
            $reasons[] = $this->warning('night_work_conflict', 20);
        }

        if (($profile->works_at_night || $profile->studies_at_night) && ! $room->can_turn_light_at_night && ! $place->has_personal_lamp) {
            $reasons[] = $this->warning('light_at_night_conflict', 15);
        }

        if ($profile->smokes && $room->smoking_allowed === false) {
            $reasons[] = $this->warning('smoking_conflict', 20);
        }

        if ($profile->tobacco_smell_sensitivity === 'high' && $room->smoking_allowed) {
            $reasons[] = $this->warning('tobacco_smell_conflict', 30);
        }

        if ($profile->cooks_often && $profile->needs_kitchen && ! $room->kitchen_night_use_allowed) {
            $reasons[] = $this->warning('kitchen_time_conflict', 10);
        }

        if ($context->range->nightsCount >= 30 && $room->long_stay_allowed === false) {
            $reasons[] = $this->warning('long_stay_conflict', 20);
        }

        if ($profile->avoids_upper_bunk && $place->is_top_bunk) {
            $reasons[] = $this->warning('upper_bunk_conflict', 35);
        }

        if ($profile->needs_locker && ! $place->has_locker) {
            $reasons[] = $this->warning('locker_missing', 20);
        }

        if ($profile->needs_locker_lock && $place->has_locker && ! $place->locker_has_lock) {
            $reasons[] = $this->warning('locker_lock_missing', 10);
        }

        if ($profile->needs_workspace && ! $room->has_workspace) {
            $reasons[] = $this->warning('workspace_missing', 15);
        }

        if ($profile->needs_fast_wifi && ! $this->hasAny($context->propertyAmenities, ['fast_wifi', 'wifi', 'wi-fi'])) {
            $reasons[] = $this->warning('fast_wifi_missing', 10);
        }

        if ($profile->max_people_in_room && $room->max_people_in_room && $room->max_people_in_room > $profile->max_people_in_room) {
            $reasons[] = $this->warning('many_people_conflict', 30);
        }

        if ($profile->needs_late_entry && $room->late_entry_allowed === false) {
            $reasons[] = $this->warning('late_entry_conflict', 20);
        }

        if ($profile->needs_power_socket && ! $place->has_power_socket) {
            $reasons[] = $this->warning('power_socket_missing', 10);
        }

        if ($profile->avoids_sofa && $place->is_sofa) {
            $reasons[] = $this->warning('sofa_conflict', 20);
        }

        if ($profile->avoids_floor_mattress && $place->is_floor_mattress) {
            $reasons[] = $this->warning('floor_mattress_conflict', 20);
        }

        return $this->unique($reasons);
    }

    /**
     * @return list<CompatibilityReasonData>
     */
    public function buildBlockingReasons(CompatibilityContext $context): array
    {
        $profile = $context->guestProfile;
        $room = $context->roomProfile;
        $place = $context->sleepingPlaceProfile;
        $reasons = [];

        if ($profile->travelling_with_pet && $room->pets_allowed === false) {
            $reasons[] = $this->blocking('pet_forbidden');
        }

        if ($profile->has_pet_allergy && $room->pets_present) {
            $reasons[] = $this->blocking('pet_allergy_with_pets');
        }

        if ($place->min_nights && $context->range->nightsCount < $place->min_nights) {
            $reasons[] = $this->blocking('stay_too_short');
        }

        if ($place->max_nights && $context->range->nightsCount > $place->max_nights) {
            $reasons[] = $this->blocking('stay_too_long');
        }

        if ($profile->smoking_preference === 'indoor' && $room->smoking_allowed === false) {
            $reasons[] = $this->blocking('indoor_smoking_forbidden');
        }

        if ($profile->needs_self_check_in && $room->late_entry_allowed === false) {
            $reasons[] = $this->blocking('self_check_in_required');
        }

        if ($profile->needs_24_7_access && $room->late_entry_allowed === false) {
            $reasons[] = $this->blocking('late_entry_required');
        }

        if ($profile->getAttribute('needs_limited_mobility_access') && $place->suitable_for_limited_mobility === false) {
            $reasons[] = $this->blocking('limited_mobility_not_supported');
        }

        return $this->unique($reasons);
    }

    /**
     * @param  list<CompatibilityReasonData|string>  $reasons
     * @return list<string>
     */
    public function translateReasons(array $reasons, string $locale): array
    {
        $previous = App::getLocale();
        App::setLocale($locale);

        try {
            return collect($reasons)
                ->map(fn (CompatibilityReasonData|string $reason): string => $reason instanceof CompatibilityReasonData
                    ? $reason->message
                    : __("compatibility.reasons.$reason"))
                ->values()
                ->all();
        } finally {
            App::setLocale($previous);
        }
    }

    private function positive(string $key, int $weight): CompatibilityReasonData
    {
        return new CompatibilityReasonData($key, __("compatibility.positive.$key"), $weight, 'positive');
    }

    private function warning(string $key, int $penalty): CompatibilityReasonData
    {
        return new CompatibilityReasonData($key, __("compatibility.warnings.$key"), $penalty, 'warning');
    }

    private function blocking(string $key): CompatibilityReasonData
    {
        return new CompatibilityReasonData($key, __("compatibility.blocking.$key"), 100, 'blocking');
    }

    private function quietRoom(mixed $room, mixed $place): bool
    {
        return $room->quiet_hours_enabled || in_array($room->noise_level, ['quiet', 'low'], true) || in_array($place->noise_level_near_place, ['quiet', 'low'], true);
    }

    private function noisyRoom(mixed $room, mixed $place): bool
    {
        return in_array($room->noise_level, ['high', 'noisy', 'loud'], true) || in_array($place->noise_level_near_place, ['high', 'noisy', 'loud'], true);
    }

    /**
     * @param  list<string>  $values
     * @param  list<string>  $needles
     */
    private function hasAny(array $values, array $needles): bool
    {
        return collect($needles)->contains(fn (string $needle): bool => in_array($needle, $values, true));
    }

    /**
     * @param  list<CompatibilityReasonData>  $reasons
     * @return list<CompatibilityReasonData>
     */
    private function unique(array $reasons): array
    {
        return collect($reasons)
            ->unique(fn (CompatibilityReasonData $reason): string => $reason->key)
            ->values()
            ->all();
    }
}
