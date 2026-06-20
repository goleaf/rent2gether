<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPrivacySetting;
use Illuminate\Support\Arr;

class UserPrivacyService
{
    private const FIELDS = [
        'show_real_name',
        'show_avatar',
        'show_age_range',
        'show_gender',
        'show_city',
        'show_languages',
        'show_rating',
        'show_completed_stays_count',
        'show_reviews_count',
        'show_phone_after_booking',
        'show_email_after_booking',
        'show_identity_verified_badge',
        'allow_host_to_see_guest_profile',
        'allow_guest_to_see_host_contact_after_booking',
    ];

    public function getOrCreate(User $user): UserPrivacySetting
    {
        return UserPrivacySetting::query()->firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): UserPrivacySetting
    {
        $settings = $this->getOrCreate($user);
        $settings->fill(Arr::only($data, self::FIELDS));
        $settings->save();

        return $settings->refresh();
    }
}
