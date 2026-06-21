<?php

namespace App\Models;

use Database\Factories\StayVisibilityPreferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StayVisibilityPreference extends Model
{
    /** @use HasFactory<StayVisibilityPreferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_stay_id',
        'user_id',
        'show_public_name',
        'show_age_range',
        'show_gender_if_room_policy_relevant',
        'show_city',
        'show_languages',
        'show_stay_purpose',
        'show_sleep_schedule',
        'show_smoking_status',
        'show_sociability_level',
        'show_checkout_date',
    ];

    protected function casts(): array
    {
        return [
            'show_public_name' => 'boolean',
            'show_age_range' => 'boolean',
            'show_gender_if_room_policy_relevant' => 'boolean',
            'show_city' => 'boolean',
            'show_languages' => 'boolean',
            'show_stay_purpose' => 'boolean',
            'show_sleep_schedule' => 'boolean',
            'show_smoking_status' => 'boolean',
            'show_sociability_level' => 'boolean',
            'show_checkout_date' => 'boolean',
        ];
    }

    public function stay(): BelongsTo
    {
        return $this->belongsTo(BookingStay::class, 'booking_stay_id');
    }

    public function bookingStay(): BelongsTo
    {
        return $this->stay();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
