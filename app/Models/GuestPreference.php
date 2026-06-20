<?php

namespace App\Models;

use Database\Factories\GuestPreferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestPreference extends Model
{
    /** @use HasFactory<GuestPreferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'preferred_budget_min',
        'preferred_budget_max',
        'preferred_currency',
        'preferred_city_id',
        'preferred_room_type',
        'preferred_sleeping_place_type',
        'wants_wifi',
        'wants_kitchen',
        'wants_washing_machine',
        'wants_locker',
        'wants_lower_bunk',
        'avoids_mixed_room',
        'avoids_smoking',
        'avoids_pets',
        'needs_late_check_in',
        'needs_early_check_out',
        'needs_workspace',
        'needs_quiet_hours',
        'needs_accessibility',
        'max_people_in_room',
        'max_walking_distance_to_transport_meters',
        'sleep_schedule',
        'social_level',
        'allergies',
        'baggage_size',
        'accessibility_needs_json',
    ];

    /**
     * Defines how Laravel converts stored Guest Preference attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'preferred_budget_min' => 'decimal:2',
            'preferred_budget_max' => 'decimal:2',
            'wants_wifi' => 'boolean',
            'wants_kitchen' => 'boolean',
            'wants_washing_machine' => 'boolean',
            'wants_locker' => 'boolean',
            'wants_lower_bunk' => 'boolean',
            'avoids_mixed_room' => 'boolean',
            'avoids_smoking' => 'boolean',
            'avoids_pets' => 'boolean',
            'needs_late_check_in' => 'boolean',
            'needs_early_check_out' => 'boolean',
            'needs_workspace' => 'boolean',
            'needs_quiet_hours' => 'boolean',
            'needs_accessibility' => 'boolean',
            'accessibility_needs_json' => 'array',
        ];
    }

    /**
     * Links this Guest Preference to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Guest Preference to the City record used by its preferred city relation.
     */
    public function preferredCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'preferred_city_id');
    }
}
