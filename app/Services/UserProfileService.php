<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Arr;

class UserProfileService
{
    private const FIELDS = [
        'display_name',
        'first_name',
        'last_name',
        'public_name',
        'avatar_path',
        'birth_date',
        'age_range_public',
        'gender',
        'gender_public',
        'country_id',
        'city_id',
        'public_city_name',
        'about',
        'occupation',
        'education',
        'languages_text',
        'profile_completed_at',
    ];

    public function getOrCreate(User $user): UserProfile
    {
        return UserProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['display_name' => $user->name],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): UserProfile
    {
        $profile = $this->getOrCreate($user);
        $profile->fill(Arr::only($data, self::FIELDS));
        $profile->save();

        return $profile->refresh();
    }
}
