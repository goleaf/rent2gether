<?php

namespace App\Services\Compatibility;

use App\Models\GuestCompatibilityProfile;
use App\Models\SleepingPlace;
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
        'i_smoke',
        'i_do_not_smoke',
        'i_wake_up_early',
        'i_sleep_late',
        'i_work_at_night',
        'i_study',
        'i_work_remotely',
        'i_often_stay_home',
        'i_rarely_stay_home',
        'i_like_quiet',
        'i_am_ok_with_noise',
        'i_am_social',
        'i_prefer_not_to_socialize',
        'i_like_cleanliness',
        'i_am_ready_to_help_cleaning',
        'i_accept_living_with_strangers',
        'i_do_not_want_many_people',
        'i_want_private_room',
        'i_accept_shared_room',
        'i_need_desk',
        'i_need_fast_internet',
        'i_need_locker',
        'i_need_quiet_at_night',
        'i_need_late_entry',
        'i_travel_with_pet',
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
                'i_accept_living_with_strangers' => true,
                'i_accept_shared_room' => true,
                'max_people_in_room' => null,
            ],
        );
    }

    public function getOrCreate(User $guest): GuestCompatibilityProfile
    {
        return $this->createDefaultForUser($guest);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data): GuestCompatibilityProfile
    {
        $profile = $this->createDefaultForUser($user);
        $payload = Arr::only($data, $this->fields);
        $profile->fill($payload + $this->legacyPayload($payload));
        $profile->save();

        app(CompatibilityCacheService::class)->forgetForUser($user);

        return $profile->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $guest, array $data): GuestCompatibilityProfile
    {
        return $this->updateProfile($guest, $data);
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

    /**
     * @return array<string, mixed>
     */
    public function compareWithSleepingPlace(User $guest, SleepingPlace $place): array
    {
        $warnings = $this->buildWarnings($guest, $place);

        return [
            'score' => max(0, 100 - (count($warnings) * 15)),
            'warnings' => $warnings,
            'status' => $warnings === [] ? 'good' : 'needs_attention',
        ];
    }

    /**
     * @return list<array{key:string,message_key:string,severity:string}>
     */
    public function buildWarnings(User $guest, SleepingPlace $place): array
    {
        $profile = $this->getOrCreate($guest);
        $place->loadMissing(['room:id,property_id,noise_level,has_desk,has_chair', 'property:id,rules,amenities']);
        $warnings = [];
        $rules = $this->normalizedValues($place->property?->rules);
        $amenities = $this->normalizedValues($place->property?->amenities);

        if (($profile->i_smoke || $profile->smokes) && in_array('no_smoking', $rules, true)) {
            $warnings[] = $this->warning('smoking_conflict');
        }

        if (($profile->i_travel_with_pet || $profile->travelling_with_pet) && in_array('no_pets', $rules, true)) {
            $warnings[] = $this->warning('pet_conflict');
        }

        if (($profile->i_like_quiet || $profile->needs_quiet_at_night) && in_array((string) $place->room?->noise_level, ['high', 'noisy'], true)) {
            $warnings[] = $this->warning('quiet_conflict');
        }

        if (($profile->i_work_remotely || $profile->remote_worker || $profile->i_need_fast_internet) && ! in_array('fast_wifi', $amenities, true)) {
            $warnings[] = $this->warning('remote_work_conflict');
        }

        if (($profile->i_need_desk || $profile->needs_workspace) && ! (bool) ($place->room?->has_desk ?? false)) {
            $warnings[] = $this->warning('desk_conflict');
        }

        if ($profile->i_need_late_entry && in_array('no_late_entry', $rules, true)) {
            $warnings[] = $this->warning('late_entry_conflict');
        }

        return $warnings;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function legacyPayload(array $payload): array
    {
        return [
            'smokes' => $payload['i_smoke'] ?? null,
            'wakes_up_early' => $payload['i_wake_up_early'] ?? null,
            'works_at_night' => $payload['i_work_at_night'] ?? null,
            'student' => $payload['i_study'] ?? null,
            'remote_worker' => $payload['i_work_remotely'] ?? null,
            'often_home' => $payload['i_often_stay_home'] ?? null,
            'rarely_home' => $payload['i_rarely_stay_home'] ?? null,
            'needs_quiet_at_night' => $payload['i_need_quiet_at_night'] ?? ($payload['i_like_quiet'] ?? null),
            'needs_workspace' => $payload['i_need_desk'] ?? null,
            'needs_fast_wifi' => $payload['i_need_fast_internet'] ?? null,
            'needs_locker' => $payload['i_need_locker'] ?? null,
            'needs_late_entry' => $payload['i_need_late_entry'] ?? null,
            'travelling_with_pet' => $payload['i_travel_with_pet'] ?? null,
            'comfortable_with_strangers' => $payload['i_accept_living_with_strangers'] ?? null,
            'comfortable_with_shared_room' => $payload['i_accept_shared_room'] ?? null,
            'wants_private_room' => $payload['i_want_private_room'] ?? null,
            'ready_to_join_cleaning' => $payload['i_am_ready_to_help_cleaning'] ?? null,
            'social_level' => ($payload['i_am_social'] ?? false) ? 'social' : null,
        ];
    }

    /**
     * @return list<string>
     */
    private function normalizedValues(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn (mixed $item): string => str((string) $item)->lower()->replace(' ', '_')->toString())
            ->values()
            ->all();
    }

    /**
     * @return array{key:string,message_key:string,severity:string}
     */
    private function warning(string $key): array
    {
        return [
            'key' => $key,
            'message_key' => "guest_profile.warnings.{$key}",
            'severity' => 'warning',
        ];
    }
}
