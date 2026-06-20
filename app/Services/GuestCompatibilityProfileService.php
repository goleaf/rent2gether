<?php

namespace App\Services;

use App\Models\GuestCompatibilityProfile;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Support\Arr;

class GuestCompatibilityProfileService
{
    private const FIELDS = [
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

    public function getOrCreate(User $guest): GuestCompatibilityProfile
    {
        return GuestCompatibilityProfile::query()->firstOrCreate(
            ['user_id' => $guest->id],
            [
                'comfortable_with_strangers' => true,
                'comfortable_with_shared_room' => true,
                'i_accept_living_with_strangers' => true,
                'i_accept_shared_room' => true,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $guest, array $data): GuestCompatibilityProfile
    {
        $profile = $this->getOrCreate($guest);
        $payload = Arr::only($data, self::FIELDS);
        $profile->fill($payload + $this->legacyPayload($payload));
        $profile->save();

        return $profile->refresh();
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
