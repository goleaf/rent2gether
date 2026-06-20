<?php

namespace App\Models;

use Database\Factories\CoLivingProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoLivingProfile extends Model
{
    /** @use HasFactory<CoLivingProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'public_alias',
        'age_range',
        'gender_for_room_policy',
        'country_id',
        'city_id',
        'languages_json',
        'stay_purpose',
        'guest_type',
        'tourist',
        'student',
        'working',
        'remote_worker',
        'long_term_guest',
        'short_term_guest',
        'sleep_schedule',
        'wake_schedule',
        'home_presence_level',
        'smokes',
        'smoking_location',
        'has_pet',
        'social_level',
        'prefers_quiet',
        'cleanliness_level',
        'participates_in_cleaning',
        'respects_personal_space',
        'roommate_rating_average',
        'roommate_reviews_count',
        'roommate_complaints_count',
        'profile_completed_at',
    ];

    /**
     * Defines how Laravel converts stored Co Living Profile attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'languages_json' => 'array',
            'tourist' => 'boolean',
            'student' => 'boolean',
            'working' => 'boolean',
            'remote_worker' => 'boolean',
            'long_term_guest' => 'boolean',
            'short_term_guest' => 'boolean',
            'smokes' => 'boolean',
            'has_pet' => 'boolean',
            'prefers_quiet' => 'boolean',
            'participates_in_cleaning' => 'boolean',
            'respects_personal_space' => 'boolean',
            'roommate_rating_average' => 'decimal:2',
            'profile_completed_at' => 'datetime',
        ];
    }

    /**
     * Links this Co Living Profile to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Co Living Profile to the Country record used by its country relation.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Links this Co Living Profile to the City record used by its city relation.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
