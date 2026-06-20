<?php

namespace App\Models;

use Database\Factories\RoomCompatibilityProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomCompatibilityProfile extends Model
{
    /** @use HasFactory<RoomCompatibilityProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id',
        'gender_policy',
        'is_private',
        'is_shared',
        'max_people_in_room',
        'current_people_count',
        'typical_people_count',
        'noise_level',
        'light_level',
        'quiet_hours_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
        'can_turn_light_at_night',
        'can_work_at_night',
        'can_eat',
        'can_store_food',
        'has_workspace',
        'has_desk',
        'has_chair',
        'has_personal_lockers',
        'has_lock',
        'has_window',
        'has_air_conditioning',
        'has_heating',
        'can_open_window',
        'smoking_allowed',
        'pets_present',
        'pets_allowed',
        'kitchen_night_use_allowed',
        'washing_machine_available',
        'long_stay_allowed',
        'short_stay_allowed',
        'late_entry_allowed',
    ];

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
            'is_shared' => 'boolean',
            'max_people_in_room' => 'integer',
            'current_people_count' => 'integer',
            'typical_people_count' => 'integer',
            'quiet_hours_enabled' => 'boolean',
            'can_turn_light_at_night' => 'boolean',
            'can_work_at_night' => 'boolean',
            'can_eat' => 'boolean',
            'can_store_food' => 'boolean',
            'has_workspace' => 'boolean',
            'has_desk' => 'boolean',
            'has_chair' => 'boolean',
            'has_personal_lockers' => 'boolean',
            'has_lock' => 'boolean',
            'has_window' => 'boolean',
            'has_air_conditioning' => 'boolean',
            'has_heating' => 'boolean',
            'can_open_window' => 'boolean',
            'smoking_allowed' => 'boolean',
            'pets_present' => 'boolean',
            'pets_allowed' => 'boolean',
            'kitchen_night_use_allowed' => 'boolean',
            'washing_machine_available' => 'boolean',
            'long_stay_allowed' => 'boolean',
            'short_stay_allowed' => 'boolean',
            'late_entry_allowed' => 'boolean',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
