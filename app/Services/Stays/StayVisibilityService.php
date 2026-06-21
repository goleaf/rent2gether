<?php

namespace App\Services\Stays;

use App\Models\BookingStay;
use App\Models\BookingStayOccupant;
use App\Models\StayVisibilityPreference;
use App\Models\User;

class StayVisibilityService
{
    public function getVisibilityPreferences(BookingStay $stay): StayVisibilityPreference
    {
        return StayVisibilityPreference::query()->firstOrCreate(
            [
                'booking_stay_id' => $stay->id,
                'user_id' => $stay->guest_user_id,
            ],
            [
                'show_public_name' => true,
                'show_age_range' => true,
                'show_gender_if_room_policy_relevant' => true,
                'show_city' => true,
                'show_languages' => true,
                'show_stay_purpose' => true,
                'show_sleep_schedule' => false,
                'show_smoking_status' => false,
                'show_sociability_level' => false,
                'show_checkout_date' => true,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateVisibility(User $guest, BookingStay $stay, array $data): StayVisibilityPreference
    {
        if ((int) $stay->guest_user_id !== (int) $guest->id) {
            abort(403);
        }

        $preference = $this->getVisibilityPreferences($stay);
        $preference->forceFill(array_intersect_key($data, array_flip([
            'show_public_name',
            'show_age_range',
            'show_gender_if_room_policy_relevant',
            'show_city',
            'show_languages',
            'show_stay_purpose',
            'show_sleep_schedule',
            'show_smoking_status',
            'show_sociability_level',
            'show_checkout_date',
        ])))->save();

        return $preference->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function filterOccupantForRoommates(BookingStayOccupant $occupant): array
    {
        $occupant->loadMissing('stay.visibilityPreference');
        $preference = $occupant->stay?->visibilityPreference;

        if ($occupant->public_visibility === 'hidden') {
            return [
                'label' => __('occupants.messages.private_roommate'),
                'occupant_type' => $occupant->occupant_type,
            ];
        }

        return array_filter([
            'label' => $preference?->show_public_name ? $occupant->occupant_name : __('occupants.messages.roommate'),
            'occupant_type' => $occupant->occupant_type,
            'age_range' => $preference?->show_age_range ? $occupant->age_range : null,
            'gender' => ($preference?->show_gender_if_room_policy_relevant && $occupant->public_gender_visible) ? $occupant->gender : null,
            'city_name' => $preference?->show_city ? $occupant->city_name : null,
            'languages' => $preference?->show_languages ? ($occupant->languages_json ?: []) : [],
            'stay_purpose' => $preference?->show_stay_purpose ? $occupant->stay_purpose : null,
            'sleep_schedule' => $preference?->show_sleep_schedule ? $occupant->sleep_schedule : null,
            'smoking_status' => $preference?->show_smoking_status ? $occupant->smoking_status : null,
            'sociability_level' => $preference?->show_sociability_level ? $occupant->sociability_level : null,
            'neighbor_rating' => $occupant->neighbor_rating_snapshot,
            'checkout_date' => $preference?->show_checkout_date ? $occupant->stay?->planned_check_out_date?->toDateString() : null,
        ], fn (mixed $value): bool => $value !== null && $value !== []);
    }

    /**
     * @return array<string, mixed>
     */
    public function filterOccupantForPublicListing(BookingStayOccupant $occupant): array
    {
        $data = $this->filterOccupantForRoommates($occupant);
        unset($data['label'], $data['city_name'], $data['neighbor_rating']);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function filterOccupantForHost(BookingStayOccupant $occupant): array
    {
        return [
            'id' => $occupant->id,
            'name' => $occupant->occupant_name,
            'user_id' => $occupant->user_id,
            'occupant_type' => $occupant->occupant_type,
            'is_main_guest' => $occupant->is_main_guest,
            'age_range' => $occupant->age_range,
            'gender' => $occupant->gender,
            'city_name' => $occupant->city_name,
            'country_name' => $occupant->country_name,
            'languages' => $occupant->languages_json ?: [],
            'stay_purpose' => $occupant->stay_purpose,
            'sleep_schedule' => $occupant->sleep_schedule,
            'smoking_status' => $occupant->smoking_status,
            'sociability_level' => $occupant->sociability_level,
            'neighbor_rating' => $occupant->neighbor_rating_snapshot,
            'public_visibility' => $occupant->public_visibility,
        ];
    }
}
