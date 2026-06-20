<?php

namespace App\Models;

use Database\Factories\GuestCompatibilityVisibilitySettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestCompatibilityVisibilitySetting extends Model
{
    /** @use HasFactory<GuestCompatibilityVisibilitySettingFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'show_smoking_preference',
        'show_sleep_schedule',
        'show_work_study_status',
        'show_home_presence',
        'show_social_level',
        'show_cleanliness_preference',
        'show_room_preferences',
        'show_workspace_needs',
        'show_pet_preference',
        'allow_use_for_matching',
        'allow_show_to_hosts',
        'allow_show_to_future_roommates',
    ];

    protected $attributes = [
        'show_smoking_preference' => false,
        'show_sleep_schedule' => true,
        'show_work_study_status' => true,
        'show_home_presence' => false,
        'show_social_level' => true,
        'show_cleanliness_preference' => false,
        'show_room_preferences' => true,
        'show_workspace_needs' => true,
        'show_pet_preference' => false,
        'allow_use_for_matching' => true,
        'allow_show_to_hosts' => false,
        'allow_show_to_future_roommates' => false,
    ];

    protected function casts(): array
    {
        return [
            'show_smoking_preference' => 'boolean',
            'show_sleep_schedule' => 'boolean',
            'show_work_study_status' => 'boolean',
            'show_home_presence' => 'boolean',
            'show_social_level' => 'boolean',
            'show_cleanliness_preference' => 'boolean',
            'show_room_preferences' => 'boolean',
            'show_workspace_needs' => 'boolean',
            'show_pet_preference' => 'boolean',
            'allow_use_for_matching' => 'boolean',
            'allow_show_to_hosts' => 'boolean',
            'allow_show_to_future_roommates' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
