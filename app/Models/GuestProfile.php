<?php

namespace App\Models;

use Database\Factories\GuestProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestProfile extends Model
{
    /** @use HasFactory<GuestProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
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

    protected function casts(): array
    {
        return [
            'has_large_luggage' => 'boolean',
            'needs_luggage_storage' => 'boolean',
            'needs_quiet_place' => 'boolean',
            'needs_desk' => 'boolean',
            'needs_fast_wifi' => 'boolean',
            'needs_registration' => 'boolean',
            'needs_work_documents' => 'boolean',
            'smokes' => 'boolean',
            'travels_with_pet' => 'boolean',
            'prefers_private_room' => 'boolean',
            'accepts_shared_room' => 'boolean',
            'accepts_living_with_strangers' => 'boolean',
            'max_people_in_room_preference' => 'integer',
            'long_stay_interested' => 'boolean',
            'short_stay_interested' => 'boolean',
            'early_wakeup' => 'boolean',
            'late_sleep' => 'boolean',
            'works_remotely' => 'boolean',
            'studies' => 'boolean',
            'often_at_home' => 'boolean',
            'rarely_at_home' => 'boolean',
            'ready_to_join_cleaning' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
