<?php

namespace App\Services\Users;

use App\Models\GuestProfile;
use App\Models\User;
use Illuminate\Support\Arr;

class GuestProfileService
{
    private const FIELDS = [
        'travel_purpose_default',
        'preferred_check_in_time',
        'preferred_check_out_time',
        'has_large_luggage',
        'needs_luggage_storage',
        'needs_quiet_place',
        'needs_desk',
        'needs_fast_wifi',
        'needs_registration',
        'needs_work_documents',
        'smokes',
        'travels_with_pet',
        'pet_description',
        'prefers_private_room',
        'accepts_shared_room',
        'accepts_living_with_strangers',
        'max_people_in_room_preference',
        'long_stay_interested',
        'short_stay_interested',
        'night_schedule',
        'early_wakeup',
        'late_sleep',
        'works_remotely',
        'studies',
        'often_at_home',
        'rarely_at_home',
        'sociability_level',
        'cleanliness_expectation',
        'ready_to_join_cleaning',
    ];

    public function getOrCreate(User $guest): GuestProfile
    {
        return GuestProfile::query()->firstOrCreate(['user_id' => $guest->id]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $guest, array $data): GuestProfile
    {
        $profile = $this->getOrCreate($guest);
        $profile->fill(Arr::only($data, self::FIELDS));
        $profile->save();

        return $profile->refresh();
    }
}
