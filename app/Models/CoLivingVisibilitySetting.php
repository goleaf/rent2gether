<?php

namespace App\Models;

use Database\Factories\CoLivingVisibilitySettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoLivingVisibilitySetting extends Model
{
    /** @use HasFactory<CoLivingVisibilitySettingFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'show_public_alias',
        'show_real_first_name',
        'show_avatar',
        'show_age_range',
        'show_gender_if_room_policy',
        'show_country',
        'show_city',
        'show_languages',
        'show_stay_purpose',
        'show_guest_type',
        'show_sleep_schedule',
        'show_wake_schedule',
        'show_home_presence',
        'show_smoking_status',
        'show_pet_status',
        'show_social_level',
        'show_quiet_preference',
        'show_cleanliness_level',
        'show_roommate_rating',
        'show_checkout_date_to_future_roommates',
        'allow_profile_in_prebooking_summary',
        'allow_profile_after_confirmed_booking',
    ];

    protected function casts(): array
    {
        return [
            'show_public_alias' => 'boolean',
            'show_real_first_name' => 'boolean',
            'show_avatar' => 'boolean',
            'show_age_range' => 'boolean',
            'show_gender_if_room_policy' => 'boolean',
            'show_country' => 'boolean',
            'show_city' => 'boolean',
            'show_languages' => 'boolean',
            'show_stay_purpose' => 'boolean',
            'show_guest_type' => 'boolean',
            'show_sleep_schedule' => 'boolean',
            'show_wake_schedule' => 'boolean',
            'show_home_presence' => 'boolean',
            'show_smoking_status' => 'boolean',
            'show_pet_status' => 'boolean',
            'show_social_level' => 'boolean',
            'show_quiet_preference' => 'boolean',
            'show_cleanliness_level' => 'boolean',
            'show_roommate_rating' => 'boolean',
            'show_checkout_date_to_future_roommates' => 'boolean',
            'allow_profile_in_prebooking_summary' => 'boolean',
            'allow_profile_after_confirmed_booking' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
