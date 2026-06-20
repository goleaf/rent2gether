<?php

namespace App\Models;

use App\Enums\UserStatus;
use Database\Factories\UserProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    /** @use HasFactory<UserProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'display_name',
        'first_name',
        'last_name',
        'public_name',
        'avatar_path',
        'birth_date',
        'age_range_public',
        'date_of_birth',
        'gender',
        'gender_public',
        'country_id',
        'city_id',
        'public_city_name',
        'phone',
        'phone_verified_at',
        'email_verified_at',
        'about',
        'languages_json',
        'occupation',
        'education',
        'languages_text',
        'profile_completed_at',
        'travel_purpose',
        'smokes',
        'has_pets',
        'allergies',
        'prefers_quiet',
        'sleep_schedule',
        'social_level',
        'identity_verified_at',
        'rating_average',
        'reviews_count',
        'complaints_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'birth_date' => 'date',
            'gender_public' => 'boolean',
            'phone_verified_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'languages_json' => 'array',
            'profile_completed_at' => 'datetime',
            'smokes' => 'boolean',
            'has_pets' => 'boolean',
            'prefers_quiet' => 'boolean',
            'identity_verified_at' => 'datetime',
            'rating_average' => 'decimal:2',
            'status' => UserStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
