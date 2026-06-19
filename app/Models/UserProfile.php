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
        'avatar_path',
        'date_of_birth',
        'gender',
        'country_id',
        'city_id',
        'phone',
        'phone_verified_at',
        'email_verified_at',
        'about',
        'languages_json',
        'occupation',
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
            'phone_verified_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'languages_json' => 'array',
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
