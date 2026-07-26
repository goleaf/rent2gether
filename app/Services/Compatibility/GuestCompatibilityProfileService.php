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
                'i_do_not_smoke' => true,
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
        $payload = $this->normalizePromptPayload(Arr::only($data, $this->fields));
        $profile->fill(array_replace($payload, $this->legacyPayload($payload)));
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
        $legacy = [];
        $this->copyBoolean($legacy, $payload, 'i_smoke', 'smokes');
        $this->copyBoolean($legacy, $payload, 'i_wake_up_early', 'wakes_up_early');
        $this->copyBoolean($legacy, $payload, 'i_sleep_late', 'sleeps_late');
        $this->copyBoolean($legacy, $payload, 'i_work_at_night', 'works_at_night');
        $this->copyBoolean($legacy, $payload, 'i_study', 'student');
        $this->copyBoolean($legacy, $payload, 'i_work_remotely', 'remote_worker');
        $this->copyBoolean($legacy, $payload, 'i_often_stay_home', 'often_home');
        $this->copyBoolean($legacy, $payload, 'i_rarely_stay_home', 'rarely_home');
        $this->copyBoolean($legacy, $payload, 'i_need_desk', 'needs_workspace');
        $this->copyBoolean($legacy, $payload, 'i_need_fast_internet', 'needs_fast_wifi');
        $this->copyBoolean($legacy, $payload, 'i_need_locker', 'needs_locker');
        $this->copyBoolean($legacy, $payload, 'i_need_late_entry', 'needs_late_entry');
        $this->copyBoolean($legacy, $payload, 'i_travel_with_pet', 'travelling_with_pet');
        $this->copyBoolean($legacy, $payload, 'i_accept_living_with_strangers', 'comfortable_with_strangers');
        $this->copyBoolean($legacy, $payload, 'i_accept_shared_room', 'comfortable_with_shared_room');
        $this->copyBoolean($legacy, $payload, 'i_want_private_room', 'wants_private_room');
        $this->copyBoolean($legacy, $payload, 'i_am_ready_to_help_cleaning', 'ready_to_join_cleaning');

        if ((bool) ($payload['i_do_not_smoke'] ?? false)) {
            $legacy['smokes'] = false;
            $legacy['smoking_preference'] = 'non_smoker';
        }

        if ((bool) ($payload['i_smoke'] ?? false)) {
            $smokingPreference = $payload['smoking_preference'] ?? 'smoker';

            $legacy['smokes'] = true;
            $legacy['smoking_preference'] = $smokingPreference === 'non_smoker' ? 'smoker' : $smokingPreference;
        }

        if ((bool) ($payload['i_like_quiet'] ?? false) || (bool) ($payload['i_need_quiet_at_night'] ?? false)) {
            $legacy['needs_quiet_at_night'] = true;
            $legacy['sensitive_to_noise_at_night'] = true;
        } elseif (array_key_exists('i_am_ok_with_noise', $payload) && (bool) $payload['i_am_ok_with_noise']) {
            $legacy['needs_quiet_at_night'] = false;
            $legacy['sensitive_to_noise_at_night'] = false;
        }

        if ((bool) ($payload['i_am_social'] ?? false)) {
            $legacy['social_level'] = 'social';
        }

        if ((bool) ($payload['i_prefer_not_to_socialize'] ?? false)) {
            $legacy['social_level'] = 'quiet';
            $legacy['prefers_private_space'] = true;
        }

        if ((bool) ($payload['i_like_cleanliness'] ?? false)) {
            $legacy['cleanliness_expectation'] = 'strict';
        }

        if ((bool) ($payload['i_do_not_want_many_people'] ?? false) && ! isset($payload['max_people_in_room'])) {
            $legacy['max_people_in_room'] = 4;
        }

        return $legacy;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizePromptPayload(array $payload): array
    {
        if ((bool) ($payload['i_smoke'] ?? false)) {
            $payload['i_do_not_smoke'] = false;
        }

        if ((bool) ($payload['i_do_not_smoke'] ?? false)) {
            $payload['i_smoke'] = false;
        }

        if ((bool) ($payload['i_often_stay_home'] ?? false)) {
            $payload['i_rarely_stay_home'] = false;
        }

        if ((bool) ($payload['i_rarely_stay_home'] ?? false)) {
            $payload['i_often_stay_home'] = false;
        }

        if ((bool) ($payload['i_am_social'] ?? false)) {
            $payload['i_prefer_not_to_socialize'] = false;
        }

        if ((bool) ($payload['i_prefer_not_to_socialize'] ?? false)) {
            $payload['i_am_social'] = false;
        }

        if ((bool) ($payload['i_like_quiet'] ?? false) || (bool) ($payload['i_need_quiet_at_night'] ?? false)) {
            $payload['i_am_ok_with_noise'] = false;
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $legacy
     * @param  array<string, mixed>  $payload
     */
    private function copyBoolean(array &$legacy, array $payload, string $source, string $target): void
    {
        if (array_key_exists($source, $payload)) {
            $legacy[$target] = (bool) $payload[$source];
        }
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
