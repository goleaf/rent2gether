<?php

namespace App\Models;

use Database\Factories\GuestCompatibilityProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestCompatibilityProfile extends Model
{
    /** @use HasFactory<GuestCompatibilityProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
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
        'profile_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'smokes' => 'boolean',
            'wakes_up_early' => 'boolean',
            'wakes_up_late' => 'boolean',
            'sleeps_early' => 'boolean',
            'sleeps_late' => 'boolean',
            'works_at_night' => 'boolean',
            'studies_at_night' => 'boolean',
            'returns_late' => 'boolean',
            'needs_late_entry' => 'boolean',
            'needs_quiet_at_night' => 'boolean',
            'sensitive_to_light_at_night' => 'boolean',
            'sensitive_to_noise_at_night' => 'boolean',
            'student' => 'boolean',
            'working' => 'boolean',
            'remote_worker' => 'boolean',
            'needs_workspace' => 'boolean',
            'needs_fast_wifi' => 'boolean',
            'needs_power_socket' => 'boolean',
            'needs_online_calls' => 'boolean',
            'often_home' => 'boolean',
            'rarely_home' => 'boolean',
            'mostly_only_sleeps' => 'boolean',
            'cooks_often' => 'boolean',
            'needs_kitchen' => 'boolean',
            'needs_fridge_shelf' => 'boolean',
            'needs_washing_machine' => 'boolean',
            'prefers_private_space' => 'boolean',
            'comfortable_with_strangers' => 'boolean',
            'ready_to_join_cleaning' => 'boolean',
            'wants_private_room' => 'boolean',
            'comfortable_with_shared_room' => 'boolean',
            'max_people_in_room' => 'integer',
            'wants_female_room' => 'boolean',
            'wants_male_room' => 'boolean',
            'comfortable_with_mixed_room' => 'boolean',
            'wants_lower_bunk' => 'boolean',
            'avoids_upper_bunk' => 'boolean',
            'avoids_sofa' => 'boolean',
            'avoids_floor_mattress' => 'boolean',
            'needs_locker' => 'boolean',
            'needs_locker_lock' => 'boolean',
            'needs_luggage_space' => 'boolean',
            'needs_bedding' => 'boolean',
            'needs_towel' => 'boolean',
            'needs_curtain' => 'boolean',
            'travelling_with_pet' => 'boolean',
            'avoids_pets' => 'boolean',
            'has_pet_allergy' => 'boolean',
            'needs_self_check_in' => 'boolean',
            'needs_24_7_access' => 'boolean',
            'profile_completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
