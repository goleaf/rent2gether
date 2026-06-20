<?php

namespace App\Services\Compatibility;

use App\Models\GuestCompatibilityProfile;
use App\Models\User;
use Illuminate\Support\Arr;

class GuestCompatibilityProfileService
{
    /** @var list<string> */
    private array $fields = [
        'smokes',
        'smoking_preference',
        'tobacco_smell_sensitivity',
        'wakes_up_early',
        'wakes_up_late',
        'sleeps_early',
        'sleeps_late',
        'works_at_night',
        'studies_at_night',
        'returns_late',
        'needs_late_entry',
        'needs_quiet_at_night',
        'sensitive_to_light_at_night',
        'sensitive_to_noise_at_night',
        'student',
        'working',
        'remote_worker',
        'needs_workspace',
        'needs_fast_wifi',
        'needs_power_socket',
        'needs_online_calls',
        'often_home',
        'rarely_home',
        'mostly_only_sleeps',
        'cooks_often',
        'needs_kitchen',
        'needs_fridge_shelf',
        'needs_washing_machine',
        'social_level',
        'prefers_private_space',
        'comfortable_with_strangers',
        'cleanliness_expectation',
        'ready_to_join_cleaning',
        'wants_private_room',
        'comfortable_with_shared_room',
        'max_people_in_room',
        'wants_female_room',
        'wants_male_room',
        'comfortable_with_mixed_room',
        'wants_lower_bunk',
        'avoids_upper_bunk',
        'avoids_sofa',
        'avoids_floor_mattress',
        'needs_locker',
        'needs_locker_lock',
        'needs_luggage_space',
        'needs_bedding',
        'needs_towel',
        'needs_curtain',
        'travelling_with_pet',
        'avoids_pets',
        'has_pet_allergy',
        'needs_self_check_in',
        'needs_24_7_access',
    ];

    public function createDefaultForUser(User $user): GuestCompatibilityProfile
    {
        return GuestCompatibilityProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'smokes' => false,
                'smoking_preference' => 'non_smoker',
                'needs_quiet_at_night' => null,
                'comfortable_with_shared_room' => true,
                'comfortable_with_strangers' => true,
                'max_people_in_room' => null,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data): GuestCompatibilityProfile
    {
        $profile = $this->createDefaultForUser($user);
        $profile->fill(Arr::only($data, $this->fields));
        $profile->save();

        app(CompatibilityCacheService::class)->forgetForUser($user);

        return $profile->refresh();
    }

    public function getProfile(User $user): GuestCompatibilityProfile
    {
        return $this->createDefaultForUser($user);
    }

    public function completeProfile(User $user): GuestCompatibilityProfile
    {
        $profile = $this->createDefaultForUser($user);
        $profile->forceFill(['profile_completed_at' => now()])->save();

        return $profile->refresh();
    }

    /**
     * @return list<string>
     */
    public function getMissingImportantFields(User $user): array
    {
        $profile = $this->createDefaultForUser($user);
        $important = [
            'smokes',
            'needs_quiet_at_night',
            'comfortable_with_shared_room',
            'max_people_in_room',
            'needs_locker',
            'travelling_with_pet',
        ];

        return collect($important)
            ->filter(fn (string $field): bool => $profile->getAttribute($field) === null)
            ->values()
            ->all();
    }
}
